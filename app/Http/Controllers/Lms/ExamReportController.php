<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use App\Models\ClassSection;
use App\Models\StudentAssessmentAnswer;
use App\Models\Subject;
use App\Models\TeacherSubjectSection;
use Illuminate\Http\Request;

class ExamReportController extends Controller
{
    /**
     * Display Official Exams Report Directory (Midterm / Final Term / Formal Exams)
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->isStudent()) {
            return redirect()->route('student.examReport');
        }

        $instituteId = $user->institute_id;

        $classSections = ClassSection::whereHas('instituteClass', fn ($q) => $q->where('institute_id', $instituteId))
            ->with('instituteClass')
            ->get()
            ->sortBy(fn ($cs) => ($cs->instituteClass?->custom_name ?? '').' '.$cs->section_name);

        $subjects = Subject::whereHas('instituteClass', fn ($q) => $q->where('institute_id', $instituteId))
            ->orderBy('subject_name')
            ->get();

        $query = Assessment::whereHas('subject.instituteClass', fn ($q) => $q->where('institute_id', $instituteId))
            ->whereIn('type', [Assessment::TYPE_MIDTERM, Assessment::TYPE_FINAL, 'exam'])
            ->with(['subject', 'classSection.instituteClass', 'academicTerm', 'creator:id,name'])
            ->orderBy('start_time', 'desc');

        if ($user->isTeacher()) {
            $query->where('is_published_teacher', true);
            $teacher = $user->teacherProfile;
            $assignedSectionIds = [];
            if ($teacher) {
                $assignedSectionIds = TeacherSubjectSection::where('teacher_id', $teacher->id)
                    ->pluck('class_section_id')
                    ->unique()
                    ->toArray();
            }
            $query->whereIn('class_section_id', $assignedSectionIds);
        }

        if ($request->filled('class_section_id')) {
            $query->where('class_section_id', $request->class_section_id);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        $exams = $query->get();

        $groupedExams = $exams->groupBy(function ($item) {
            $cName = $item->classSection?->instituteClass?->custom_name ?? 'Class';
            $sName = $item->classSection?->section_name ?? '';
            return trim("{$cName} - {$sName}");
        });

        return view('lms.exam_report.index', compact('classSections', 'subjects', 'exams', 'groupedExams'));
    }

    /**
     * Display Exam Marksheet Entry Page
     */
    public function showMarksheet(Request $request, int $assessmentId)
    {
        $user = auth()->user();
        $assessment = Assessment::with(['subject', 'classSection.instituteClass', 'creator', 'questions'])->findOrFail($assessmentId);

        // Get questions or create a default Q1 if none exist
        $questions = $assessment->questions()->orderBy('sort_order')->get();
        if ($questions->isEmpty()) {
            $defaultQ = AssessmentQuestion::create([
                'assessment_id' => $assessment->id,
                'question_type' => 'long',
                'statement' => 'Q1',
                'marks' => $assessment->total_marks ?: 100,
                'sort_order' => 1,
            ]);
            $questions = collect([$defaultQ]);
        }

        // Fetch students in this class section
        $students = \App\Models\Student::where('class_section_id', $assessment->class_section_id)
            ->with('user')
            ->get()
            ->sortBy('roll_number');

        // Fetch existing recorded marks for all questions
        $answers = StudentAssessmentAnswer::whereIn('assessment_question_id', $questions->pluck('id'))
            ->get()
            ->groupBy('student_id');

        $marksheet = [];
        foreach ($students as $st) {
            $stUserId = $st->user_id ?? $st->id;
            $stAnswers = $answers->get($stUserId, collect())->keyBy('assessment_question_id');

            $totalObtained = 0;
            $hasAttempted = false;
            $questionMarks = [];

            foreach ($questions as $q) {
                $ans = $stAnswers->get($q->id);
                $m = $ans ? $ans->marks_awarded : null;
                $questionMarks[$q->id] = $m;
                if ($m !== null) {
                    $hasAttempted = true;
                    $totalObtained += (float) $m;
                }
            }

            $pct = ($hasAttempted && $assessment->total_marks > 0)
                ? round(($totalObtained / $assessment->total_marks) * 100, 1)
                : 0;

            $grade = 'F';
            if ($pct >= 90) $grade = 'A+';
            elseif ($pct >= 80) $grade = 'A';
            elseif ($pct >= 70) $grade = 'B';
            elseif ($pct >= 60) $grade = 'C';
            elseif ($pct >= 50) $grade = 'D';

            $marksheet[] = [
                'student_id' => $stUserId,
                'roll_number' => $st->roll_number ?? 'N/A',
                'student_name' => $st->full_name ?? ($st->user?->name ?? 'Student'),
                'email' => $st->email ?? ($st->user?->email ?? ''),
                'question_marks' => $questionMarks,
                'total_obtained' => $hasAttempted ? $totalObtained : null,
                'has_attempted' => $hasAttempted,
                'percentage' => $pct,
                'grade' => $hasAttempted ? $grade : 'Pending',
            ];
        }

        // Determine edit permissions:
        // Teacher assigned can edit; Principal/Admin has master override authority
        $canEdit = false;
        if ($user->isAdministration()) {
            $canEdit = true;
        } elseif ($user->isTeacher()) {
            $teacher = $user->teacherProfile;
            if ($teacher) {
                $isAssigned = TeacherSubjectSection::where('teacher_id', $teacher->id)
                    ->where('class_section_id', $assessment->class_section_id)
                    ->where('subject_id', $assessment->subject_id)
                    ->exists();
                $canEdit = $isAssigned;
            }
        }

        return view('lms.exam_report.marksheet', compact('assessment', 'questions', 'marksheet', 'canEdit'));
    }

    /**
     * Save / Update Exam Marksheet & Questions Structure (Teacher inserts, Principal can override)
     */
    public function storeMarks(Request $request, int $assessmentId)
    {
        $user = auth()->user();
        $assessment = Assessment::with('questions')->findOrFail($assessmentId);

        // Check permission
        if (! ($user->isAdministration() || $user->isTeacher())) {
            return redirect()->back()->with('error', 'Unauthorized to modify exam marks.');
        }

        // Handle question structure update if provided
        if ($request->has('questions') && is_array($request->questions)) {
            $sumMarks = 0;
            $updatedQIds = [];

            foreach ($request->questions as $index => $qData) {
                $qStatement = trim($qData['statement'] ?? '') ?: ('Q'.($index + 1));
                $qMarks = max(1, (int) ($qData['marks'] ?? 10));
                $sumMarks += $qMarks;

                if (! empty($qData['id'])) {
                    $q = AssessmentQuestion::where('assessment_id', $assessment->id)->find($qData['id']);
                    if ($q) {
                        $q->update([
                            'statement' => $qStatement,
                            'marks' => $qMarks,
                            'sort_order' => $index + 1,
                        ]);
                        $updatedQIds[] = $q->id;
                    }
                } else {
                    $newQ = AssessmentQuestion::create([
                        'assessment_id' => $assessment->id,
                        'question_type' => 'long',
                        'statement' => $qStatement,
                        'marks' => $qMarks,
                        'sort_order' => $index + 1,
                    ]);
                    $updatedQIds[] = $newQ->id;
                }
            }

            // Remove questions that were deleted by teacher if no answers exist yet
            AssessmentQuestion::where('assessment_id', $assessment->id)
                ->whereNotIn('id', $updatedQIds)
                ->delete();

            $assessment->update(['total_marks' => $sumMarks]);
        }

        $questions = $assessment->questions()->orderBy('sort_order')->get();
        if ($questions->isEmpty()) {
            $defaultQ = AssessmentQuestion::create([
                'assessment_id' => $assessment->id,
                'question_type' => 'long',
                'statement' => 'Q1',
                'marks' => $assessment->total_marks ?: 100,
                'sort_order' => 1,
            ]);
            $questions = collect([$defaultQ]);
        }

        // Store marks per student per question
        if ($request->has('student_question_marks') && is_array($request->student_question_marks)) {
            foreach ($request->student_question_marks as $studentUserId => $qMarksMap) {
                if (! is_array($qMarksMap)) continue;

                foreach ($qMarksMap as $qId => $marksObtained) {
                    if ($marksObtained === null || $marksObtained === '') continue;

                    $questionObj = $questions->firstWhere('id', $qId);
                    $maxM = $questionObj ? (float) $questionObj->marks : (float) $assessment->total_marks;
                    $marksObtained = min(max(0, (float) $marksObtained), $maxM);

                    StudentAssessmentAnswer::updateOrCreate(
                        [
                            'assessment_question_id' => $qId,
                            'student_id' => $studentUserId,
                        ],
                        [
                            'provided_answer' => 'Official Exam Result Entry',
                            'marks_awarded' => $marksObtained,
                            'grading_status' => StudentAssessmentAnswer::STATUS_MANUALLY_GRADED,
                            'graded_by' => $user->id,
                        ]
                    );
                }
            }
        } elseif ($request->has('student_marks') && is_array($request->student_marks)) {
            // Legacy single total marks fallback
            $firstQ = $questions->first();
            foreach ($request->student_marks as $studentUserId => $marksObtained) {
                if ($marksObtained === null || $marksObtained === '') continue;

                $marksObtained = min((float) $marksObtained, (float) $assessment->total_marks);

                StudentAssessmentAnswer::updateOrCreate(
                    [
                        'assessment_question_id' => $firstQ->id,
                        'student_id' => $studentUserId,
                    ],
                    [
                        'provided_answer' => 'Official Exam Result Entry',
                        'marks_awarded' => $marksObtained,
                        'grading_status' => StudentAssessmentAnswer::STATUS_MANUALLY_GRADED,
                        'graded_by' => $user->id,
                    ]
                );
            }
        }

        $assessment->update([
            'is_marksheet_saved' => true,
            'saved_at' => now(),
            'status' => Assessment::STATUS_GRADED,
        ]);

        // Continuous Live Grade Recalculation Trigger
        try {
            app(\App\Services\GradeNormalizationService::class)->recalculateAndSyncClassResults(
                $assessment->subject_id,
                $assessment->academic_term_id,
                $assessment->class_section_id
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Continuous Grading trigger failed: '.$e->getMessage());
        }

        $examDate = $assessment->start_time ? $assessment->start_time->format('M d, Y') : $assessment->created_at->format('M d, Y');

        return redirect()->back()->with('success', "Official Exam marks for '{$assessment->title}' (Date: {$examDate}) saved successfully!");
    }
}
