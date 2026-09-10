<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\PracticeTestSession;
use App\Models\Student;
use App\Models\StudentAssessmentAnswer;
use App\Models\Subject;
use Illuminate\Http\Request;

class TestResultController extends Controller
{
    /**
     * Display the Test Results Directory ("Test Results Folder").
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $instituteId = $user->institute_id;

        // 1. Role-Based Subject Scope
        $subjectQuery = Subject::whereHas('instituteClass', fn ($q) => $q->where('institute_id', $instituteId));

        if ($user->isStudent()) {
            $student = $user->getStudentModel();
            if ($student && $student->class_section_id) {
                $subjectQuery->where(function ($sq) use ($student) {
                    $sq->where('institute_class_id', $student->classSection?->institute_class_id)
                      ->orWhereHas('teacherAssignments', fn ($ta) => $ta->where('class_section_id', $student->class_section_id));
                });
            }
        } elseif ($user->isTeacher()) {
            $teacher = $user->teacherProfile;
            if ($teacher) {
                $subjectQuery->whereHas('teacherAssignments', fn ($ta) => $ta->where('teacher_id', $teacher->id));
            }
        }

        $subjects = $subjectQuery->orderBy('subject_name')->get();

        // 2. Fetch Class Sections for Filtering
        $classSections = \App\Models\ClassSection::whereHas('instituteClass', fn ($q) => $q->where('institute_id', $instituteId))
            ->with('instituteClass')
            ->get()
            ->sortBy(fn ($cs) => ($cs->instituteClass?->custom_name ?? '').' '.$cs->section_name);

        // 3. Fetch Assessments
        $assessmentQuery = Assessment::whereHas('subject.instituteClass', fn ($q) => $q->where('institute_id', $instituteId))
            ->with(['subject', 'classSection.instituteClass', 'questions', 'creator:id,name'])
            ->orderByDesc('created_at');

        if ($user->isTeacher()) {
            $assessmentQuery->where('creator_id', $user->id);
        }

        if ($request->filled('class_section_id')) {
            $assessmentQuery->where('class_section_id', $request->class_section_id);
        }

        if ($request->filled('subject_id')) {
            $assessmentQuery->where('subject_id', $request->subject_id);
        }

        if ($request->filled('search')) {
            $assessmentQuery->where('title', 'like', '%'.$request->search.'%');
        }

        $assessments = $assessmentQuery->get();

        // 4. Student Search & Subject Score Breakdown Report
        $searchedStudentResults = null;
        if ($request->filled('student_search')) {
            $searchTerm = trim($request->student_search);

            $studentUsers = \App\Models\User::where('institute_id', $instituteId)
                ->where('role', 'student')
                ->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                        ->orWhere('email', 'like', "%{$searchTerm}%");
                })
                ->get();

            if ($studentUsers->isNotEmpty()) {
                $searchedStudentResults = [];

                foreach ($studentUsers as $stUser) {
                    $stProfile = \App\Models\Student::where('user_id', $stUser->id)->first();
                    $classSection = $stProfile?->classSection;
                    $className = $classSection ? (($classSection->instituteClass?->custom_name ?? 'Class').' - '.$classSection->section_name) : 'General Class';

                    // Fetch subjects assigned to student's class
                    $enrolledSubjects = $classSection
                        ? Subject::where(function ($sq) use ($classSection) {
                            $sq->where('institute_class_id', $classSection->institute_class_id)
                              ->orWhereHas('teacherAssignments', fn ($ta) => $ta->where('class_section_id', $classSection->id));
                          })->get()
                        : Subject::whereHas('instituteClass', fn ($q) => $q->where('institute_id', $instituteId))->get();

                    $subjectScores = [];

                    foreach ($enrolledSubjects as $subj) {
                        $subjAssessments = Assessment::where('subject_id', $subj->id)
                            ->when($classSection, fn ($q) => $q->where('class_section_id', $classSection->id))
                            ->with('questions')
                            ->get();

                        $totalSubjectMax = 0;
                        $totalSubjectObtained = 0.0;
                        $testBreakdown = [];

                        foreach ($subjAssessments as $ass) {
                            $qIds = $ass->questions->pluck('id');
                            $answers = \App\Models\StudentAssessmentAnswer::whereIn('assessment_question_id', $qIds)
                                ->where('student_id', $stUser->id)
                                ->get();

                            $assObtained = (float) $answers->sum('marks_awarded');
                            $hasAttempted = $answers->isNotEmpty();

                            $totalSubjectMax += $ass->total_marks;
                            if ($hasAttempted) {
                                $totalSubjectObtained += $assObtained;
                            }

                            $testBreakdown[] = [
                                'assessment_id' => $ass->id,
                                'test_title' => $ass->title,
                                'total_marks' => $ass->total_marks,
                                'obtained_marks' => round($assObtained, 2),
                                'has_attempted' => $hasAttempted,
                            ];
                        }

                        $subjectPct = $totalSubjectMax > 0 ? round(($totalSubjectObtained / $totalSubjectMax) * 100, 1) : 0;
                        $grade = match (true) {
                            $subjectPct >= 90 => 'A+',
                            $subjectPct >= 80 => 'A',
                            $subjectPct >= 70 => 'B',
                            $subjectPct >= 60 => 'C',
                            $subjectPct >= 50 => 'D',
                            default => 'F',
                        };

                        $subjectScores[] = [
                            'subject_id' => $subj->id,
                            'subject_name' => $subj->subject_name,
                            'total_obtained' => round($totalSubjectObtained, 2),
                            'total_max' => $totalSubjectMax,
                            'percentage' => $subjectPct,
                            'grade' => $grade,
                            'tests_count' => count($testBreakdown),
                            'tests' => $testBreakdown,
                        ];
                    }

                    $searchedStudentResults[] = [
                        'user_id' => $stUser->id,
                        'name' => $stUser->name,
                        'email' => $stUser->email,
                        'roll_number' => $stProfile->roll_number ?? ('ST-'.$stUser->id),
                        'class_name' => $className,
                        'subject_scores' => $subjectScores,
                    ];
                }
            }
        }

        // 5. Group by Class Section -> Subject
        $groupedByClass = $assessments->groupBy(function ($item) {
            $className = $item->classSection?->instituteClass?->custom_name ?? 'General Class';
            $sectionName = $item->classSection?->section_name ?? '';
            return trim("{$className} {$sectionName}");
        });

        return view('lms.test_results.index', compact('assessments', 'subjects', 'classSections', 'groupedByClass', 'searchedStudentResults'));
    }

    /**
     * Dedicated Marksheet view showing marks of all students for a specific test.
     */
    public function showMarksheet(Request $request, int $assessmentId)
    {
        $user = auth()->user();

        $assessment = Assessment::with([
            'subject',
            'classSection.instituteClass',
            'questions',
            'creator:id,name',
        ])->findOrFail($assessmentId);

        // Resolve the campus that owns this assessment's class section
        // (not the manager's active context) to keep marksheets campus-bound.
        $assessmentInstituteId = $assessment->classSection?->instituteClass?->institute_id ?? $user->institute_id;

        // Fetch all students enrolled in this test's class section
        $students = Student::where('class_section_id', $assessment->class_section_id)
            ->where('institute_id', $assessmentInstituteId)
            ->with('user')
            ->orderBy('roll_number')
            ->get();

        // If no students enrolled in class_section_id, fallback to user accounts with role student
        if ($students->isEmpty()) {
            $studentUsers = \App\Models\User::where('institute_id', $assessmentInstituteId)
                ->where('role', 'student')
                ->get();
        } else {
            $studentUserIds = $students->pluck('user_id')->filter();
            $studentUsers = \App\Models\User::whereIn('id', $studentUserIds)->get();
        }

        $questionIds = $assessment->questions->pluck('id');

        // Fetch all submitted answers for this assessment
        $allAnswers = StudentAssessmentAnswer::whereIn('assessment_question_id', $questionIds)
            ->with('question')
            ->get()
            ->groupBy('student_id');

        $marksheet = [];
        $totalClassScore = 0;
        $attemptedStudentsCount = 0;

        foreach ($studentUsers as $stUser) {
            $userAnswers = $allAnswers->get($stUser->id, collect());
            $hasAttempted = $userAnswers->isNotEmpty();

            $totalObtained = 0.0;
            $gradedCount = 0;
            $questionDetails = [];

            foreach ($assessment->questions as $q) {
                $ans = $userAnswers->firstWhere('assessment_question_id', $q->id);
                $obtained = $ans ? (float) ($ans->marks_awarded ?? 0) : 0.0;
                $totalObtained += $obtained;

                if ($ans && $ans->isGraded()) {
                    $gradedCount++;
                }

                $questionDetails[] = [
                    'question_id' => $q->id,
                    'statement' => $q->statement,
                    'question_type' => $q->question_type,
                    'max_marks' => $q->marks,
                    'correct_answer' => $q->correct_answer,
                    'provided_answer' => $ans ? $ans->provided_answer : null,
                    'marks_awarded' => $ans ? $ans->marks_awarded : null,
                    'feedback' => $ans ? $ans->ai_feedback : null,
                    'status' => $ans ? $ans->grading_status : 'not_answered',
                ];
            }

            if ($hasAttempted) {
                $attemptedStudentsCount++;
                $totalClassScore += $totalObtained;
            }

            $percentage = $assessment->total_marks > 0
                ? round(($totalObtained / $assessment->total_marks) * 100, 1)
                : 0;

            $grade = match (true) {
                $percentage >= 90 => 'A+',
                $percentage >= 80 => 'A',
                $percentage >= 70 => 'B',
                $percentage >= 60 => 'C',
                $percentage >= 50 => 'D',
                default => 'F',
            };

            $studentProfile = Student::where('user_id', $stUser->id)->first();

            $marksheet[] = [
                'student_id' => $stUser->id,
                'student_name' => $stUser->name,
                'roll_number' => $studentProfile->roll_number ?? ('ST-'.$stUser->id),
                'email' => $stUser->email,
                'has_attempted' => $hasAttempted,
                'total_obtained' => round($totalObtained, 2),
                'max_marks' => $assessment->total_marks,
                'percentage' => $percentage,
                'grade' => $grade,
                'questions_graded' => $gradedCount,
                'total_questions' => $assessment->questions->count(),
                'answers_detail' => $questionDetails,
            ];
        }

        $classAverage = $attemptedStudentsCount > 0
            ? round($totalClassScore / $attemptedStudentsCount, 1)
            : 0;

        return view('lms.test_results.marksheet', compact('assessment', 'marksheet', 'classAverage', 'attemptedStudentsCount'));
    }

    /**
     * Mark test marksheet as saved in system.
     */
    public function saveMarksheet(Request $request, int $assessmentId)
    {
        $assessment = Assessment::findOrFail($assessmentId);

        $assessment->update([
            'is_marksheet_saved' => true,
            'saved_at' => now(),
        ]);

        return redirect()->back()->with('success', "Marksheet for '{$assessment->title}' saved successfully into the Test Results folder.");
    }

    /**
     * Store or update marks for all students for a test by test title and date.
     */
    public function updateStudentMarks(Request $request, int $assessmentId)
    {
        $assessment = Assessment::with('questions')->findOrFail($assessmentId);

        $validated = $request->validate([
            'student_marks' => 'required|array',
            'student_marks.*' => 'nullable|numeric|min:0',
        ]);

        $user = auth()->user();

        // Ensure at least one question exists to attach student marks to
        $question = $assessment->questions->first();
        if (! $question) {
            $question = \App\Models\AssessmentQuestion::create([
                'assessment_id' => $assessment->id,
                'question_type' => 'long',
                'statement' => 'Test Evaluation Marks',
                'marks' => $assessment->total_marks,
                'sort_order' => 1,
            ]);
        }

        foreach ($validated['student_marks'] as $studentUserId => $marksObtained) {
            if ($marksObtained === null || $marksObtained === '') {
                continue;
            }

            $marksObtained = min((float) $marksObtained, (float) $assessment->total_marks);

            StudentAssessmentAnswer::updateOrCreate(
                [
                    'assessment_question_id' => $question->id,
                    'student_id' => $studentUserId,
                ],
                [
                    'provided_answer' => 'Recorded Marksheet Entry',
                    'marks_awarded' => $marksObtained,
                    'grading_status' => StudentAssessmentAnswer::STATUS_MANUALLY_GRADED,
                    'graded_by' => $user->id,
                ]
            );
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

        $testDateFormatted = $assessment->start_time ? $assessment->start_time->format('M d, Y') : $assessment->created_at->format('M d, Y');

        return redirect()->back()->with('success', "Marks for test '{$assessment->title}' (Date: {$testDateFormatted}) saved successfully into Test Results & Marksheet!");
    }
}
