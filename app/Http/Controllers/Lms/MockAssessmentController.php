<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use App\Models\ClassSection;
use App\Models\PastPaperExemplar;
use App\Models\PastPaperUpload;
use App\Models\Subject;
use App\Models\SubjectMaterial;
use App\Models\TeacherSubjectSection;
use App\Services\GroqMockGenerationService;
use App\Services\PastPaperParserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MockAssessmentController extends Controller
{
    protected GroqMockGenerationService $generationService;
    protected PastPaperParserService $parserService;

    public function __construct(
        GroqMockGenerationService $generationService,
        PastPaperParserService $parserService
    ) {
        $this->generationService = $generationService;
        $this->parserService = $parserService;
    }

    /**
     * Display the list of generated Mock Examinations for the teacher / institute.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $instituteId = $user->institute_id;

        $query = Assessment::where('is_mock', true)
            ->whereHas('subject.instituteClass', fn ($q) => $q->where('institute_id', $instituteId))
            ->with(['subject', 'classSection.instituteClass', 'academicTerm', 'creator'])
            ->withCount('questions')
            ->orderByDesc('created_at');

        if ($user->isTeacher() && !$user->is_delegated_admin) {
            $query->where('creator_id', $user->id);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $mocks = $query->paginate(15)->withQueryString();

        // Fetch subjects for filter
        $subjects = Subject::whereHas('instituteClass', fn ($q) => $q->where('institute_id', $instituteId))
            ->orderBy('subject_name')
            ->get();

        if ($request->expectsJson()) {
            return response()->json(['mocks' => $mocks]);
        }

        return view('teacher.mocks.index', compact('mocks', 'subjects'));
    }

    /**
     * Display the dedicated O/A Levels Mock Examination Generation Engine interface.
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        $instituteId = $user->institute_id;

        // Fetch active academic terms
        $academicTerms = AcademicTerm::where('institute_id', $instituteId)
            ->where('is_active', true)
            ->get();

        // Fetch subjects & sections based on teacher scoping
        if ($user->isTeacher() && !$user->is_delegated_admin) {
            $subjects = $user->assignedSubjects()
                ->with(['instituteClass', 'sections'])
                ->whereHas('instituteClass', function ($q) use ($instituteId) {
                    $q->where('institute_id', $instituteId);
                })
                ->orderBy('subject_name')
                ->get();

            $assignedSectionIds = TeacherSubjectSection::where('teacher_id', $user->id)
                ->pluck('class_section_id')
                ->filter()
                ->unique()
                ->toArray();

            $classSectionsQuery = ClassSection::whereHas('instituteClass', function ($q) use ($instituteId) {
                $q->where('institute_id', $instituteId);
            })->with('instituteClass');

            if (!empty($assignedSectionIds)) {
                $classSectionsQuery->whereIn('id', $assignedSectionIds);
            }
            $classSections = $classSectionsQuery->get();
        } else {
            $subjects = Subject::whereHas('instituteClass', function ($q) use ($instituteId) {
                $q->where('institute_id', $instituteId);
            })->with(['instituteClass', 'sections'])->orderBy('subject_name')->get();

            $classSections = ClassSection::whereHas('instituteClass', function ($q) use ($instituteId) {
                $q->where('institute_id', $instituteId);
            })->with('instituteClass')->get();
        }

        // Past paper exemplar stats
        $totalPastPapers = PastPaperUpload::where('institute_id', $instituteId)->count();
        $totalExemplars = PastPaperExemplar::whereHas('upload', function ($q) use ($instituteId) {
            $q->where('institute_id', $instituteId);
        })->count();

        return view('teacher.mocks.create', compact(
            'subjects',
            'classSections',
            'academicTerms',
            'totalPastPapers',
            'totalExemplars'
        ));
    }

    /**
     * Authorize user access to a mock assessment.
     */
    private function authorizeAssessmentAccess(Assessment $assessment): void
    {
        $user = auth()->user();
        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        if ($user->isGlobalAdmin()) {
            return;
        }

        $activeInstId = method_exists($user, 'getActiveInstituteId') ? $user->getActiveInstituteId() : $user->institute_id;
        $assessmentInstId = $assessment->subject?->instituteClass?->institute_id;

        if ($assessmentInstId && $activeInstId && (int) $assessmentInstId !== (int) $activeInstId) {
            abort(403, 'Unauthorized access to this assessment.');
        }
    }

    /**
     * Display dedicated Mock Review & In-Place Editor view.
     */
    public function show(Request $request, Assessment $assessment)
    {
        $this->authorizeAssessmentAccess($assessment);

        $assessment->load([
            'subject',
            'classSection.instituteClass',
            'academicTerm',
            'creator',
            'questions' => function ($q) {
                $q->orderBy('sort_order');
            }
        ]);

        if ($request->expectsJson()) {
            return response()->json(['assessment' => $assessment]);
        }

        return view('teacher.mocks.show', compact('assessment'));
    }

    /**
     * In-Place Update of an individual MCQ Question in a Mock Examination.
     */
    public function updateQuestion(Request $request, AssessmentQuestion $question): JsonResponse
    {
        $user = auth()->user();
        $instituteId = $user->institute_id;

        // Security check
        $this->authorizeAssessmentAccess($question->assessment);

        $validated = $request->validate([
            'statement' => 'required|string',
            'options' => 'required|array|min:3|max:5',
            'options.*' => 'required|string',
            'correct_answer' => 'required|string|max:10',
            'chapter_reference' => 'nullable|string|max:255',
            'explanation' => 'nullable|string',
        ]);

        // Normalize options keys [A => '...', B => '...', C => '...', D => '...', optional E => '...']
        $normalizedOptions = [];
        $allowedKeys = array_slice(['A', 'B', 'C', 'D', 'E'], 0, count($validated['options']));

        if (array_is_list($validated['options'])) {
            foreach ($allowedKeys as $idx => $k) {
                $normalizedOptions[$k] = $validated['options'][$idx] ?? "Option {$k}";
            }
        } else {
            foreach ($allowedKeys as $k) {
                $normalizedOptions[$k] = $validated['options'][$k] ?? ($validated['options'][strtolower($k)] ?? "Option {$k}");
            }
        }

        $correctAnswer = strtoupper(trim($validated['correct_answer']));
        if (!in_array($correctAnswer, $allowedKeys, true)) {
            // Find key by option value match
            foreach ($normalizedOptions as $k => $val) {
                if (mb_strtolower(trim($val)) === mb_strtolower(trim($validated['correct_answer']))) {
                    $correctAnswer = $k;
                    break;
                }
            }
            if (!in_array($correctAnswer, $allowedKeys, true)) {
                $correctAnswer = 'A';
            }
        }

        $question->statement = $validated['statement'];
        $question->options = $normalizedOptions;
        $question->correct_answer = $correctAnswer;
        $question->chapter_reference = $validated['chapter_reference'] ?? $question->chapter_reference;
        $question->question_hash = $question->computeHash();
        $question->save();

        return response()->json([
            'success' => true,
            'message' => "Question Q{$question->sort_order} updated successfully.",
            'question' => [
                'id' => $question->id,
                'question_number' => $question->sort_order,
                'statement' => $question->statement,
                'options' => $question->options,
                'correct_answer' => $question->correct_answer,
                'chapter_reference' => $question->chapter_reference,
                'question_hash' => $question->question_hash,
            ]
        ]);
    }

    /**
     * Return real-time status of past papers, exemplars, and syllabus topics for a subject.
     */
    public function subjectStatus(Request $request, int $subjectId): JsonResponse
    {
        $user = auth()->user();
        $instituteId = $user->institute_id;

        $subject = Subject::where('id', $subjectId)
            ->whereHas('instituteClass', fn ($q) => $q->where('institute_id', $instituteId))
            ->firstOrFail();

        $uploads = PastPaperUpload::where('subject_id', $subjectId)
            ->where('institute_id', $instituteId)
            ->orderByDesc('created_at')
            ->get(['id', 'exam_series', 'parsed_status', 'total_questions_extracted', 'created_at']);

        $exemplarCount = PastPaperExemplar::where('subject_id', $subjectId)->count();

        // Retrieve known topics from RAG chunks or past paper exemplars
        $exemplarTopics = PastPaperExemplar::where('subject_id', $subjectId)
            ->whereNotNull('topic_tag')
            ->pluck('topic_tag')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $materials = SubjectMaterial::where('subject_id', $subjectId)
            ->get(['id', 'title', 'document_type', 'is_rag_indexed']);

        return response()->json([
            'success' => true,
            'subject' => [
                'id' => $subject->id,
                'name' => $subject->name,
                'code' => $subject->subject_code ?? 'CAIE',
            ],
            'past_paper_uploads' => $uploads,
            'exemplar_count' => $exemplarCount,
            'known_topics' => $exemplarTopics,
            'materials_count' => $materials->count(),
            'rag_indexed_count' => $materials->where('is_rag_indexed', true)->count(),
        ]);
    }

    /**
     * Batch upload 1–10 Past Paper PDFs and parse them into the Exemplar Knowledge Base.
     */
    public function uploadPastPapers(Request $request): JsonResponse
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'exam_series' => 'nullable|string|max:100',
            'past_papers' => 'required|array|min:1|max:10',
            'past_papers.*' => 'required|file|mimes:pdf|max:20480', // Up to 20MB per PDF
        ]);

        $user = auth()->user();
        $instituteId = $user->institute_id;
        $subjectId = (int) $request->input('subject_id');
        $examSeries = $request->input('exam_series') ?: 'CAIE Past Paper Series';

        $uploadedFiles = $request->file('past_papers');
        $results = [];
        $totalExtracted = 0;

        foreach ($uploadedFiles as $file) {
            $path = $file->store("past-papers/{$instituteId}/{$subjectId}", 'local');
            $uploadRecord = PastPaperUpload::create([
                'institute_id' => $instituteId,
                'subject_id' => $subjectId,
                'exam_series' => $examSeries . ' (' . $file->getClientOriginalName() . ')',
                'file_path' => $path,
                'parsed_status' => 'processing',
                'total_questions_extracted' => 0,
            ]);

            try {
                $count = $this->parserService->ingestPastPaperPdf($uploadRecord);
                $totalExtracted += $count;
                $results[] = [
                    'file' => $file->getClientOriginalName(),
                    'status' => 'success',
                    'questions_extracted' => $count,
                ];
            } catch (\Throwable $e) {
                Log::error("Failed parsing past paper {$file->getClientOriginalName()}: {$e->getMessage()}");
                $results[] = [
                    'file' => $file->getClientOriginalName(),
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }

        $totalSubjectExemplars = PastPaperExemplar::where('subject_id', $subjectId)->count();

        return response()->json([
            'success' => true,
            'message' => "Successfully processed " . count($uploadedFiles) . " past paper(s). Extracted {$totalExtracted} standard MCQs.",
            'total_extracted' => $totalExtracted,
            'total_subject_exemplars' => $totalSubjectExemplars,
            'details' => $results,
        ]);
    }

    /**
     * AJAX endpoint to generate Cambridge CAIE / Edexcel Mock Exam MCQs with dynamic option counts and Zero Duplication.
     */
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'topics' => 'nullable|array',
            'topics.*' => 'string',
            'total_mcqs' => 'required|integer|min:5|max:100',
            'options_per_mcq' => 'nullable|integer|in:3,4,5',
            'exam_standard' => 'required|string|in:caie_o_level,caie_a_level,edexcel_igcse',
        ]);

        $user = auth()->user();
        $instituteId = $user->institute_id;
        $subjectId = (int) $request->input('subject_id');

        // Teacher Scoping Guard: Only allow assigned subjects
        if ($user->isTeacher() && !$user->is_delegated_admin) {
            $isAssigned = TeacherSubjectSection::where('teacher_id', $user->id)
                ->where('subject_id', $subjectId)
                ->exists();

            if (!$isAssigned) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: You can only generate mock examinations for subjects actively assigned to you.',
                ], 403);
            }
        }

        $topics = $request->input('topics', []);
        $totalMcqs = (int) $request->input('total_mcqs', 30);
        $optionsCount = (int) $request->input('options_per_mcq', 4);
        $examStandard = (string) $request->input('exam_standard', 'caie_o_level');

        try {
            $mockResult = $this->generationService->generateMockExam(
                $subjectId,
                $instituteId,
                $topics,
                $totalMcqs,
                $examStandard,
                $optionsCount
            );

            return response()->json($mockResult);
        } catch (\Throwable $e) {
            Log::error("Mock generation failed: {$e->getMessage()}\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => "Mock generation error: {$e->getMessage()}",
            ], 500);
        }
    }

    /**
     * Publish and persist the generated Mock Examination to the database.
     */
    public function publish(Request $request): JsonResponse
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'academic_term_id' => 'required|exists:academic_terms,id',
            'class_section_id' => 'nullable|exists:class_sections,id',
            'institute_class_id' => 'nullable|exists:institute_classes,id',
            'title' => 'required|string|max:255',
            'exam_standard' => 'required|string',
            'options_per_mcq' => 'nullable|integer|in:3,4,5',
            'time_limit_minutes' => 'required|integer|min:5|max:300',
            'instructions' => 'nullable|string|max:5000',
            'scheduled_date' => 'nullable|date',
            'start_time' => 'nullable|string',
            'end_time' => 'nullable|string',
            'questions' => 'required|array|min:1',
            'questions.*.question_stem' => 'required|string',
            'questions.*.options' => 'required|array',
            'questions.*.correct_answer' => 'required|string',
        ]);

        $user = auth()->user();
        $instituteId = $user->institute_id;
        $subjectId = (int) $request->input('subject_id');
        $sectionId = $request->input('class_section_id');

        // Teacher Scoping Guard: Only allow assigned subjects & classes
        if ($user->isTeacher() && !$user->is_delegated_admin) {
            $assignmentQuery = TeacherSubjectSection::where('teacher_id', $user->id)
                ->where('subject_id', $subjectId);

            if ($sectionId) {
                $assignmentQuery->where(function ($q) use ($sectionId) {
                    $q->where('class_section_id', $sectionId)->orWhereNull('class_section_id');
                });
            }

            if (!$assignmentQuery->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: You are not authorized to publish mock examinations for this subject or class section.',
                ], 403);
            }
        }

        $questions = $request->input('questions');
        $totalCount = count($questions);
        $optionsPerMcq = (int) $request->input('options_per_mcq', 4);

        return DB::transaction(function () use ($request, $user, $questions, $totalCount, $optionsPerMcq) {
            $subject = Subject::with('instituteClass')->findOrFail($request->input('subject_id'));
            $instituteClassId = $request->input('institute_class_id') ?: ($subject->institute_class_id ?? null);

            $scheduledDateStr = $request->input('scheduled_date')
                ? \Carbon\Carbon::parse($request->input('scheduled_date'))->toDateString()
                : now()->toDateString();

            $durationMinutes = (int) $request->input('time_limit_minutes', 45);

            $startTimeInput = $request->input('start_time');
            $endTimeInput = $request->input('end_time');

            if ($startTimeInput && preg_match('/^\d{1,2}:\d{2}/', $startTimeInput)) {
                $startTime = \Carbon\Carbon::parse("{$scheduledDateStr} {$startTimeInput}");
            } elseif ($startTimeInput) {
                $startTime = \Carbon\Carbon::parse($startTimeInput);
            } else {
                $startTime = now();
            }

            if ($endTimeInput && preg_match('/^\d{1,2}:\d{2}/', $endTimeInput)) {
                $endTime = \Carbon\Carbon::parse("{$scheduledDateStr} {$endTimeInput}");
            } elseif ($endTimeInput) {
                $endTime = \Carbon\Carbon::parse($endTimeInput);
            } else {
                $endTime = (clone $startTime)->addMinutes($durationMinutes);
            }

            $deliverOnPortal = $request->boolean('deliver_on_portal', false);
            $defaultInstructions = "1. Answer all {$totalCount} questions.\n2. For each question, choose the ONE answer you consider correct.\n3. Each correct answer will score one mark. A mark will not be deducted for a wrong answer.\n4. Any rough working should be done on paper.";

            $assessment = Assessment::create([
                'subject_id' => $subject->id,
                'academic_term_id' => $request->input('academic_term_id'),
                'institute_class_id' => $instituteClassId,
                'class_section_id' => $request->input('class_section_id') ?: null,
                'creator_id' => $user->id,
                'teacher_id' => $user->id,
                'title' => $request->input('title'),
                'type' => Assessment::TYPE_FINAL, // Official Exam standard
                'total_marks' => $totalCount,
                'evaluation_mode' => 'ai',
                'instructions' => $request->input('instructions') ?: $defaultInstructions,
                'scheduled_date' => $scheduledDateStr,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'duration_minutes' => $durationMinutes,
                'has_time_limit' => true,
                'status' => $deliverOnPortal ? Assessment::STATUS_PUBLISHED : Assessment::STATUS_DRAFT,
                'is_published' => $deliverOnPortal,
                'is_published_teacher' => true,
                'is_published_student' => $deliverOnPortal,
                'is_paper_test' => !$deliverOnPortal,
                'is_marksheet_saved' => true,
                'saved_at' => now(),
                'is_mock' => true,
                'exam_standard' => $request->input('exam_standard'),
                'total_mcqs' => $totalCount,
                'total_questions' => $totalCount,
                'options_per_mcq' => $optionsPerMcq,
            ]);

            foreach ($questions as $index => $q) {
                AssessmentQuestion::create([
                    'assessment_id' => $assessment->id,
                    'question_type' => AssessmentQuestion::TYPE_MCQ,
                    'statement' => $q['question_stem'],
                    'options' => $q['options'],
                    'correct_answer' => $q['correct_answer'],
                    'marks' => 1,
                    'sort_order' => $index + 1,
                    'chapter_reference' => $q['topic'] ?? null,
                    'question_hash' => $q['stem_hash'] ?? null,
                    'embedding' => $q['vector'] ?? null,
                ]);
            }

            $successMsg = $deliverOnPortal
                ? "Official Cambridge Mock Examination successfully scheduled & published on Student Portal with {$totalCount} MCQs."
                : "Mock Examination successfully saved to your Examination Bank ({$totalCount} MCQs). It is ready for printing, PDF download, or portal delivery.";

            return response()->json([
                'success' => true,
                'message' => $successMsg,
                'assessment_id' => $assessment->id,
                'is_portal_visible' => $deliverOnPortal,
                'is_published_student' => $deliverOnPortal,
                'redirect_url' => route('teacher.mocks.show', $assessment->id),
            ]);
        });
    }

    /**
     * Toggle student portal delivery for a mock exam.
     */
    public function togglePortalDelivery(Request $request, Assessment $assessment): JsonResponse|RedirectResponse
    {
        $this->authorizeAssessmentAccess($assessment);

        $newStatus = !$assessment->is_published_student;
        $assessment->is_published_student = $newStatus;
        $assessment->is_published = $newStatus;
        $assessment->is_paper_test = !$newStatus;
        $assessment->status = $newStatus ? Assessment::STATUS_PUBLISHED : Assessment::STATUS_DRAFT;
        $assessment->save();

        $msg = $newStatus
            ? "Mock Examination is now LIVE on the Student Portal."
            : "Mock Examination is now set to Offline / Print Only (hidden from Student Portal).";

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'is_portal_visible' => $newStatus,
                'is_published_student' => $newStatus,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Display the Cambridge International Examination Printable Question Paper / Marking Scheme.
     */
    public function printPaper(Request $request, Assessment $assessment)
    {
        $this->authorizeAssessmentAccess($assessment);

        $assessment->load([
            'subject.instituteClass.institute',
            'classSection.instituteClass',
            'academicTerm',
            'creator',
            'questions' => function ($q) {
                $q->orderBy('sort_order');
            }
        ]);

        $withAnswers = $request->boolean('with_answers', false);

        return view('teacher.mocks.print', compact('assessment', 'withAnswers'));
    }

    /**
     * Stream or download the Mock Examination as a clean, standardized PDF document.
     */
    public function downloadPdf(Request $request, Assessment $assessment)
    {
        $this->authorizeAssessmentAccess($assessment);

        $assessment->load([
            'subject.instituteClass.institute',
            'classSection.instituteClass',
            'academicTerm',
            'creator',
            'questions' => function ($q) {
                $q->orderBy('sort_order');
            }
        ]);

        $withAnswers = $request->boolean('with_answers', false);
        $subjectCode = preg_replace('/[^A-Za-z0-9_-]/', '', $assessment->subject->subject_code ?? 'MOCK');
        $suffix = $withAnswers ? '_Marking_Scheme' : '_Question_Paper';
        $filename = "Cambridge_Mock_{$subjectCode}_{$assessment->id}{$suffix}.pdf";

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('teacher.mocks.pdf', compact('assessment', 'withAnswers'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif'
            ]);

        return $pdf->download($filename);
    }

    /**
     * Delete a mock examination and its questions.
     */
    public function destroy(Request $request, Assessment $assessment): RedirectResponse|JsonResponse
    {
        $this->authorizeAssessmentAccess($assessment);

        $assessment->questions()->delete();
        $assessment->delete();

        $redirectRoute = \Illuminate\Support\Facades\Route::has('teacher.mocks.index') ? 'teacher.mocks.index' : 'lms.mocks.index';
        return redirect()->route($redirectRoute)->with('success', 'Mock Examination deleted successfully.');
    }
}
