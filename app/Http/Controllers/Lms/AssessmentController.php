<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use App\Models\StudentAssessmentAnswer;
use App\Services\AssessmentGradingService;
use App\Services\RagIntegrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Assessment Controller
 *
 * Manages the full lifecycle of assessments:
 * create → add questions → publish → collect answers → grade → report.
 */
class AssessmentController extends Controller
{
    protected AssessmentGradingService $gradingService;

    public function __construct(AssessmentGradingService $gradingService)
    {
        $this->gradingService = $gradingService;
    }

    // ── List Assessments ─────────────────────────────────────────────────────

    /**
     * List assessments filtered by subject, term, section, or type.
     */
    public function index(Request $request)
    {
        $query = Assessment::with(['subject', 'classSection.instituteClass', 'creator:id,name'])
            ->orderByDesc('created_at');

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->filled('academic_term_id')) {
            $query->where('academic_term_id', $request->academic_term_id);
        }
        if ($request->filled('class_section_id')) {
            $query->where('class_section_id', $request->class_section_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Teachers only see their own assessments
        if (auth()->user()->isTeacher()) {
            $query->where('creator_id', auth()->id());
        }

        // Students only see assessments for sections and subjects they are enrolled in
        if (auth()->user()->isStudent()) {
            $student = auth()->user()->getStudentModel();
            if ($student && $student->class_section_id) {
                $query->where('class_section_id', $student->class_section_id)
                    ->where('is_portal_visible', true)
                    ->where('is_published', true);

                $enrolledSubjectIds = \App\Models\StudentSubjectEnrollment::where('student_id', auth()->id())
                    ->where('enrollment_status', 'active')
                    ->pluck('subject_id')
                    ->all();

                if (! empty($enrolledSubjectIds)) {
                    $query->whereIn('subject_id', $enrolledSubjectIds);
                }
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $assessments = $query->paginate(20);

        if ($request->expectsJson()) {
            return response()->json(['assessments' => $assessments]);
        }

        return view('lms.assessments.index', compact('assessments'));
    }

    // ── Create Assessment ────────────────────────────────────────────────────

    /**
     * Show the assessment creation form (guarded by active academic term: BUG-LMS-001).
     */
    public function create(Request $request)
    {
        Gate::authorize('create', Assessment::class);

        $instituteId = auth()->user()->institute_id;
        $hasActiveTerm = \App\Models\AcademicTerm::where('institute_id', $instituteId)
            ->where('is_active', true)
            ->exists();

        if (! $hasActiveTerm) {
            $errorMessage = 'Academic Term Prerequisite Required: An active academic term must be configured before creating assessments.';
            if ($request->expectsJson()) {
                return response()->json(['message' => $errorMessage], 422);
            }
            return redirect()->route('principal.academic-terms.index')->with('error', $errorMessage);
        }

        $subjects = \App\Models\Subject::whereHas('instituteClass', fn ($q) => $q->where('institute_id', $instituteId))->orderBy('subject_name')->get();
        $classSections = \App\Models\ClassSection::whereHas('instituteClass', fn ($q) => $q->where('institute_id', $instituteId))->with('instituteClass')->get();
        $academicTerms = \App\Models\AcademicTerm::where('institute_id', $instituteId)->where('is_active', true)->get();

        return view('lms.assessments.create', compact('subjects', 'classSections', 'academicTerms'));
    }

    /**
     * Store a new assessment (draft by default).
     * Enforces active academic term prerequisite guard (BUG-LMS-001).
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        Gate::authorize('create', Assessment::class);

        $instituteId = auth()->user()->institute_id;
        $hasActiveTerm = \App\Models\AcademicTerm::where('institute_id', $instituteId)
            ->where('is_active', true)
            ->exists();

        if (! $hasActiveTerm) {
            $errorMessage = 'Academic Term Prerequisite Required: An active academic term must be configured before creating assessments.';
            if ($request->expectsJson()) {
                return response()->json(['message' => $errorMessage], 422);
            }
            return redirect()->back()->with('error', $errorMessage);
        }

        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'academic_term_id' => 'required|exists:academic_terms,id',
            'class_section_id' => 'required|exists:class_sections,id',
            'title' => 'required|string|max:255',
            'type' => 'required|in:'.implode(',', Assessment::TYPES),
            'total_marks' => 'required|integer|min:1|max:1000',
            'evaluation_mode' => 'nullable|in:manual,ai',
            'instructions' => 'nullable|string|max:5000',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after:start_time',
            'result_deadline' => 'nullable|date',
            'target_type' => 'nullable|in:section,multi_sections,specific_students',
            'target_section_ids' => 'nullable|array',
            'target_student_ids' => 'nullable|array',
        ]);

        // Only Principal or Administration (delegated admin rights) can create Midterm and Final Term exams
        if (in_array($validated['type'], [Assessment::TYPE_MIDTERM, Assessment::TYPE_FINAL, 'exam'])) {
            if (! auth()->user()->isAdministration()) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Unauthorized: Only Principal or Administration (delegated admin rights) can create Midterm or Final Term examinations.'], 403);
                }
                return redirect()->back()->with('error', 'Unauthorized: Only Principal or Administration (delegated admin rights) can create Midterm or Final Term examinations.');
            }
        }

        $assessment = Assessment::create([
            ...$validated,
            'creator_id' => auth()->id(),
            'start_time' => $validated['start_time'] ?? now(),
            'evaluation_mode' => $validated['evaluation_mode'] ?? 'manual',
            'target_type' => $validated['target_type'] ?? 'section',
            'target_section_ids' => isset($validated['target_section_ids']) ? json_encode($validated['target_section_ids']) : null,
            'target_student_ids' => isset($validated['target_student_ids']) ? json_encode($validated['target_student_ids']) : null,
            'is_marksheet_saved' => true,
            'saved_at' => now(),
            'status' => Assessment::STATUS_PUBLISHED,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Assessment created.', 'assessment' => $assessment], 201);
        }

        return redirect()->back()->with('success', "Assessment '{$assessment->title}' created successfully.");
    }

    // ── Add Questions ────────────────────────────────────────────────────────

    /**
     * Add questions to an assessment (batch or single).
     */
    public function addQuestions(Request $request, Assessment $assessment): RedirectResponse|JsonResponse
    {
        Gate::authorize('update', $assessment);

        $validated = $request->validate([
            'questions' => 'required|array|min:1',
            'questions.*.question_type' => 'required|in:mcq,short,long',
            'questions.*.statement' => 'required|string|max:5000',
            'questions.*.options' => 'nullable|array',      // For MCQs
            'questions.*.options.*' => 'nullable|string',
            'questions.*.correct_answer' => 'nullable|string|max:5000',
            'questions.*.marks' => 'required|integer|min:1',
            'questions.*.chapter_reference' => 'nullable|string|max:255',
        ]);

        $sortOrder = $assessment->questions()->max('sort_order') ?? 0;

        foreach ($validated['questions'] as $q) {
            $sortOrder++;
            $options = $q['options'] ?? null;
            if (is_string($options)) {
                $options = array_values(array_filter(array_map('trim', explode(',', $options))));
            }

            AssessmentQuestion::create([
                'assessment_id' => $assessment->id,
                'question_type' => $q['question_type'],
                'statement' => $q['statement'],
                'options' => $options,
                'correct_answer' => $q['correct_answer'] ?? null,
                'marks' => $q['marks'],
                'sort_order' => $sortOrder,
                'chapter_reference' => $q['chapter_reference'] ?? null,
            ]);
        }

        // Recalculate total marks
        $assessment->update([
            'total_marks' => $assessment->questions()->sum('marks'),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => count($validated['questions']).' question(s) added.']);
        }

        return redirect()->back()->with('success', count($validated['questions']).' question(s) added.');
    }

    // ── Publish Assessment ───────────────────────────────────────────────────

    /**
     * Publish a draft assessment (makes it visible to students).
     */
    public function publish(Assessment $assessment): RedirectResponse|JsonResponse
    {
        Gate::authorize('update', $assessment);

        if ($assessment->questions()->count() === 0) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Cannot publish an assessment with no questions.'], 422);
            }

            return redirect()->back()->with('error', 'Cannot publish an assessment with no questions.');
        }

        $assessment->update(['status' => Assessment::STATUS_PUBLISHED]);

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Assessment published.']);
        }

        return redirect()->back()->with('success', "Assessment '{$assessment->title}' published.");
    }

    // ── Set Exam Schedule (Principal Only) ───────────────────────────────────

    /**
     * Set or update the start/end time and result deadline for midterms/finals.
     * Principal-only authorization enforced via AssessmentPolicy.
     */
    public function setSchedule(Request $request, Assessment $assessment): RedirectResponse|JsonResponse
    {
        Gate::authorize('setExamSchedule', $assessment);

        $validated = $request->validate([
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'result_deadline' => 'nullable|date|after:end_time',
        ]);

        $assessment->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Exam schedule updated.']);
        }

        return redirect()->back()->with('success', "Schedule set for '{$assessment->title}'.");
    }

    // ── Student Take Assessment ─────────────────────────────────────────────

    /**
     * Display the assessment taking interface for students.
     */
    public function take(Assessment $assessment)
    {
        $assessment->load(['questions', 'subject', 'classSection']);

        $existingAnswers = StudentAssessmentAnswer::whereIn('assessment_question_id', $assessment->questions->pluck('id'))
            ->where('student_id', auth()->id())
            ->get();

        return view('lms.assessments.take', compact('assessment', 'existingAnswers'));
    }

    // ── Submit Student Answers ────────────────────────────────────────────────

    /**
     * Student submits answers for an assessment.
     */
    public function submitAnswers(Request $request, Assessment $assessment): RedirectResponse|JsonResponse
    {
        if ($assessment->status !== Assessment::STATUS_PUBLISHED && $assessment->status !== Assessment::STATUS_IN_PROGRESS) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'This assessment is not currently open for submissions.'], 422);
            }

            return redirect()->back()->with('error', 'This assessment is not currently open for submissions.');
        }

        $validated = $request->validate([
            'answers' => 'required|array',
        ]);

        $studentId = auth()->id();

        // Support both simple array answers[q_id] = text and nested answers[i][question_id]
        foreach ($validated['answers'] as $qId => $providedAnswer) {
            $questionId = is_array($providedAnswer) ? ($providedAnswer['question_id'] ?? $qId) : $qId;
            $answerText = is_array($providedAnswer) ? ($providedAnswer['provided_answer'] ?? '') : $providedAnswer;

            if (empty($answerText)) {
                continue;
            }

            StudentAssessmentAnswer::updateOrCreate(
                [
                    'assessment_question_id' => $questionId,
                    'student_id' => $studentId,
                ],
                [
                    'provided_answer' => $answerText,
                    'grading_status' => StudentAssessmentAnswer::STATUS_PENDING,
                ]
            );
        }

        // Mark assessment as in progress if not already
        if ($assessment->status === Assessment::STATUS_PUBLISHED) {
            $assessment->update(['status' => Assessment::STATUS_IN_PROGRESS]);
        }

        // Auto-grade MCQs immediately if available
        $this->gradingService->gradeStudentAssessment($assessment->id, $studentId);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Answers submitted successfully.']);
        }

        return redirect()->back()->with('success', 'Your assessment answers have been submitted successfully!');
    }

    // ── Trigger Grading ──────────────────────────────────────────────────────

    /**
     * Trigger grading for a student's submission on an assessment.
     */
    public function gradeStudent(Request $request, Assessment $assessment): JsonResponse
    {
        Gate::authorize('grade', $assessment);

        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
        ]);

        $result = $this->gradingService->gradeStudentAssessment(
            assessmentId: $assessment->id,
            studentId: $validated['student_id'],
        );

        return response()->json([
            'message' => 'Grading complete.',
            'result' => $result,
        ]);
    }

    /**
     * Bulk auto-grade all MCQs for this assessment.
     */
    public function bulkAutoGradeMcqs(Assessment $assessment): JsonResponse
    {
        Gate::authorize('grade', $assessment);

        $result = $this->gradingService->bulkAutoGradeMcqs($assessment->id);

        return response()->json([
            'message' => "{$result['graded_count']} MCQ answer(s) auto-graded.",
            'result' => $result,
        ]);
    }

    // ── Update Assessment ────────────────────────────────────────────────────

    public function update(Request $request, Assessment $assessment): RedirectResponse|JsonResponse
    {
        Gate::authorize('update', $assessment);

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'evaluation_mode' => 'nullable|in:manual,ai',
            'instructions' => 'nullable|string|max:5000',
        ]);

        $assessment->update(array_filter($validated));

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Assessment updated.']);
        }

        return redirect()->back()->with('success', 'Assessment updated.');
    }

    // ── Delete Assessment ────────────────────────────────────────────────────

    public function destroy(Assessment $assessment): RedirectResponse|JsonResponse
    {
        Gate::authorize('delete', $assessment);

        $title = $assessment->title;
        $assessment->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => "Assessment '{$title}' deleted."]);
        }

        return redirect()->back()->with('success', "Assessment '{$title}' deleted.");
    }
    // ── AI Test Generator from Book ────────────────────────────────────────

    /**
     * Generate test questions automatically from the RAG indexed book/materials.
     * Supports filtering by topic, page range (e.g. 1-30), chapter range (e.g. Ch 1-2), or portion (half/complete book).
     */
    public function generateAiAssessment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'academic_term_id' => 'required|exists:academic_terms,id',
            'class_section_id' => 'required|exists:class_sections,id',
            'topic' => 'nullable|string|max:255',
            'page_start' => 'nullable|integer|min:1',
            'page_end' => 'nullable|integer|gte:page_start',
            'chapter_start' => 'nullable|integer|min:1',
            'chapter_end' => 'nullable|integer|gte:chapter_start',
            'chapter_number' => 'nullable|integer|min:1',
            'portion' => 'nullable|in:first_half,second_half,complete',
            'mcq_count' => 'nullable|integer|min:0|max:20',
            'short_count' => 'nullable|integer|min:0|max:10',
            'long_count' => 'nullable|integer|min:0|max:5',
            'mcq_marks' => 'nullable|integer|min:1|max:50',
            'short_marks' => 'nullable|integer|min:1|max:50',
            'long_marks' => 'nullable|integer|min:1|max:100',
            'has_time_limit' => 'nullable|boolean',
            'duration_minutes' => 'nullable|integer|min:1|max:300',
            'is_marksheet_saved' => 'nullable|boolean',
            'target_type' => 'nullable|in:section,multi_sections,specific_students',
            'target_section_ids' => 'nullable|array',
            'target_student_ids' => 'nullable|array',
        ]);

        $ragService = app(RagIntegrationService::class);
        $topic = $validated['topic'] ?? 'Subject Assessment';

        $scopeFilters = array_filter([
            'page_start' => $validated['page_start'] ?? null,
            'page_end' => $validated['page_end'] ?? null,
            'chapter_start' => $validated['chapter_start'] ?? null,
            'chapter_end' => $validated['chapter_end'] ?? null,
            'chapter_number' => $validated['chapter_number'] ?? null,
            'portion' => $validated['portion'] ?? null,
            'topic' => $topic,
        ]);

        $generated = $ragService->generateAssessmentFromBook(
            subjectId: $validated['subject_id'],
            topic: $topic,
            mcqCount: $validated['mcq_count'] ?? 5,
            shortCount: $validated['short_count'] ?? 3,
            longCount: $validated['long_count'] ?? 1,
            scopeFilters: $scopeFilters,
        );

        if (empty($generated['questions'])) {
            return response()->json(['error' => 'Failed to generate questions. Ensure study materials are uploaded and indexed.'], 422);
        }

        $mcqMarks = (int) ($validated['mcq_marks'] ?? 2);
        $shortMarks = (int) ($validated['short_marks'] ?? 5);
        $longMarks = (int) ($validated['long_marks'] ?? 10);

        // Assign explicit total marks to each question
        $totalMarks = 0;
        foreach ($generated['questions'] as &$q) {
            $qType = $q['question_type'] ?? 'mcq';
            $qMarks = match ($qType) {
                'mcq' => $mcqMarks,
                'short' => $shortMarks,
                'long' => $longMarks,
                default => 2,
            };
            $q['max_marks'] = $qMarks;
            $totalMarks += $qMarks;
        }
        unset($q);

        $assessment = Assessment::create([
            'subject_id' => $validated['subject_id'],
            'academic_term_id' => $validated['academic_term_id'],
            'class_section_id' => $validated['class_section_id'],
            'creator_id' => auth()->id(),
            'title' => $generated['title'] ?? ('Test: '.$topic),
            'type' => Assessment::TYPE_QUIZ,
            'total_marks' => $totalMarks,
            'start_time' => now(),
            'evaluation_mode' => 'ai',
            'has_time_limit' => !empty($validated['has_time_limit']),
            'duration_minutes' => $validated['duration_minutes'] ?? null,
            'is_marksheet_saved' => true,
            'saved_at' => now(),
            'target_type' => $validated['target_type'] ?? 'section',
            'target_section_ids' => isset($validated['target_section_ids']) ? json_encode($validated['target_section_ids']) : null,
            'target_student_ids' => isset($validated['target_student_ids']) ? json_encode($validated['target_student_ids']) : null,
            'status' => Assessment::STATUS_PUBLISHED,
        ]);

        // Create assessment questions
        foreach ($generated['questions'] as $idx => $q) {
            AssessmentQuestion::create([
                'assessment_id' => $assessment->id,
                'question_type' => $q['question_type'] ?? 'mcq',
                'statement' => $q['question_text'],
                'options' => isset($q['options']) && is_array($q['options']) ? json_encode($q['options']) : null,
                'correct_answer' => $q['correct_answer'] ?? '',
                'marks' => $q['max_marks'] ?? 2,
                'sort_order' => $idx + 1,
            ]);
        }

        return response()->json([
            'message' => "AI Assessment '{$assessment->title}' generated and published successfully!",
            'assessment' => $assessment->load('questions'),
        ], 201);
    }

    // ── Student Self-Practice Quiz Generator ─────────────────────────────────

    /**
     * Generate an instant practice quiz for students from the book with page/chapter/portion scope.
     */
    public function generateStudentPracticeQuiz(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'topic' => 'nullable|string|max:255',
            'page_start' => 'nullable|integer|min:1',
            'page_end' => 'nullable|integer|gte:page_start',
            'chapter_start' => 'nullable|integer|min:1',
            'chapter_end' => 'nullable|integer|gte:chapter_start',
            'chapter_number' => 'nullable|integer|min:1',
            'portion' => 'nullable|in:first_half,second_half,complete',
        ]);

        $topic = $validated['topic'] ?: 'General Practice Quiz';
        $ragService = app(RagIntegrationService::class);

        $scopeFilters = array_filter([
            'page_start' => $validated['page_start'] ?? null,
            'page_end' => $validated['page_end'] ?? null,
            'chapter_start' => $validated['chapter_start'] ?? null,
            'chapter_end' => $validated['chapter_end'] ?? null,
            'chapter_number' => $validated['chapter_number'] ?? null,
            'portion' => $validated['portion'] ?? null,
            'topic' => $topic,
        ]);

        $generated = $ragService->generateAssessmentFromBook(
            subjectId: $validated['subject_id'],
            topic: $topic,
            mcqCount: 5,
            shortCount: 1,
            longCount: 0,
            scopeFilters: $scopeFilters,
        );

        return response()->json([
            'quiz' => $generated,
        ]);
    }

    /**
     * Render form for creating a manual paper test marksheet.
     */
    public function paperMarksheetForm(Request $request)
    {
        $user = auth()->user();
        $instituteId = $user->institute_id;

        $subjects = \App\Models\Subject::whereHas('instituteClass', fn ($q) => $q->where('institute_id', $instituteId))->orderBy('subject_name')->get();
        $classSections = \App\Models\ClassSection::whereHas('instituteClass', fn ($q) => $q->where('institute_id', $instituteId))->with('instituteClass')->get();
        $academicTerms = \App\Models\AcademicTerm::where('institute_id', $instituteId)->orderByDesc('is_active')->get();

        $selectedSectionId = $request->class_section_id ?: ($classSections->first()?->id);
        $students = collect();

        if ($selectedSectionId) {
            $studentProfiles = \App\Models\Student::where('class_section_id', $selectedSectionId)
                ->where('institute_id', $instituteId)
                ->with('user')
                ->get();

            if ($studentProfiles->isEmpty()) {
                $students = \App\Models\User::where('institute_id', $instituteId)->where('role', 'student')->get();
            } else {
                $students = $studentProfiles->map(fn ($p) => $p->user)->filter();
            }
        }

        return view('lms.assessments.paper_marksheet', compact('subjects', 'classSections', 'academicTerms', 'students', 'selectedSectionId'));
    }

    /**
     * Save manual paper test marksheet into database.
     */
    public function storePaperMarksheet(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'academic_term_id' => 'required|exists:academic_terms,id',
            'class_section_id' => 'required|exists:class_sections,id',
            'title' => 'required|string|max:255',
            'total_marks' => 'required|numeric|min:1',
            'weightage_percentage' => 'required|numeric|min:0.1|max:100',
            'student_marks' => 'required|array',
            'student_marks.*' => 'nullable|numeric|min:0',
        ]);

        $user = auth()->user();

        // 1. Create Assessment
        $assessment = Assessment::create([
            'subject_id' => $validated['subject_id'],
            'academic_term_id' => $validated['academic_term_id'],
            'class_section_id' => $validated['class_section_id'],
            'creator_id' => $user->id,
            'title' => $validated['title'],
            'type' => 'quiz',
            'total_marks' => $validated['total_marks'],
            'weightage_percentage' => $validated['weightage_percentage'],
            'evaluation_mode' => 'manual',
            'is_paper_test' => true,
            'status' => 'graded',
            'is_marksheet_saved' => true,
            'saved_at' => now(),
            'instructions' => 'Manual paper test marksheet recorded by teacher.',
        ]);

        // 2. Create Placeholder Question
        $question = AssessmentQuestion::create([
            'assessment_id' => $assessment->id,
            'question_type' => 'long_answer',
            'statement' => 'Paper Exam Overall Marks',
            'marks' => $validated['total_marks'],
            'sort_order' => 1,
        ]);

        // 3. Insert Student Marks
        foreach ($validated['student_marks'] as $studentUserId => $marksObtained) {
            if ($marksObtained === null || $marksObtained === '') {
                continue;
            }

            StudentAssessmentAnswer::updateOrCreate(
                [
                    'assessment_question_id' => $question->id,
                    'student_id' => $studentUserId,
                ],
                [
                    'provided_answer' => 'Paper Test Submission',
                    'marks_awarded' => min((float) $marksObtained, (float) $validated['total_marks']),
                    'grading_status' => StudentAssessmentAnswer::STATUS_MANUALLY_GRADED,
                    'graded_by' => $user->id,
                ]
            );
        }

        return redirect()->route('lms.test-results.marksheet', $assessment->id)
            ->with('success', "Paper Test Marksheet for '{$assessment->title}' saved successfully!");
    }
}
