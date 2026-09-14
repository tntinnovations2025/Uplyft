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
use App\Services\GroqMockGenerationService;
use App\Services\PastPaperParserService;
use Illuminate\Http\JsonResponse;
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

        // Fetch subjects
        $subjectsQuery = Subject::whereHas('instituteClass', function ($q) use ($instituteId) {
            $q->where('institute_id', $instituteId);
        });

        if ($user->isTeacher()) {
            // Optional teacher assignment filtering if exists
            $assignedSubjectIds = DB::table('teacher_subjects')
                ->where('teacher_id', $user->id)
                ->pluck('subject_id')
                ->toArray();

            if (!empty($assignedSubjectIds)) {
                $subjectsQuery->whereIn('id', $assignedSubjectIds);
            }
        }

        $subjects = $subjectsQuery->orderBy('name')->get();

        // Fetch class sections
        $classSections = ClassSection::whereHas('instituteClass', function ($q) use ($instituteId) {
            $q->where('institute_id', $instituteId);
        })->with('instituteClass')->get();

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
                'code' => $subject->code ?? 'CAIE',
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
     * AJAX endpoint to generate Cambridge CAIE / Edexcel Mock Exam MCQs with Zero Duplication.
     */
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'topics' => 'nullable|array',
            'topics.*' => 'string',
            'total_mcqs' => 'required|integer|in:10,20,30,40',
            'exam_standard' => 'required|string|in:caie_o_level,caie_a_level,edexcel_igcse',
        ]);

        $user = auth()->user();
        $instituteId = $user->institute_id;
        $subjectId = (int) $request->input('subject_id');
        $topics = $request->input('topics', []);
        $totalMcqs = (int) $request->input('total_mcqs', 30);
        $examStandard = (string) $request->input('exam_standard', 'caie_o_level');

        try {
            $mockResult = $this->generationService->generateMockExam(
                $subjectId,
                $instituteId,
                $topics,
                $totalMcqs,
                $examStandard
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
            'class_section_id' => 'required|exists:class_sections,id',
            'title' => 'required|string|max:255',
            'exam_standard' => 'required|string',
            'time_limit_minutes' => 'required|integer|min:15|max:180',
            'instructions' => 'nullable|string|max:5000',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date',
            'questions' => 'required|array|min:1',
            'questions.*.question_stem' => 'required|string',
            'questions.*.options' => 'required|array',
            'questions.*.correct_answer' => 'required|string',
        ]);

        $user = auth()->user();
        $instituteId = $user->institute_id;
        $questions = $request->input('questions');
        $totalCount = count($questions);

        return DB::transaction(function () use ($request, $user, $questions, $totalCount) {
            $startTime = $request->input('start_time') ? now()->parse($request->input('start_time')) : now();
            $durationMinutes = (int) $request->input('time_limit_minutes', 45);
            $endTime = $request->input('end_time') ? now()->parse($request->input('end_time')) : (clone $startTime)->addMinutes($durationMinutes);

            $defaultInstructions = "1. Answer all {$totalCount} questions.\n2. For each question, choose the ONE answer you consider correct.\n3. Each correct answer will score one mark. A mark will not be deducted for a wrong answer.\n4. Any rough working should be done on paper.";

            $assessment = Assessment::create([
                'subject_id' => $request->input('subject_id'),
                'academic_term_id' => $request->input('academic_term_id'),
                'class_section_id' => $request->input('class_section_id'),
                'creator_id' => $user->id,
                'title' => $request->input('title'),
                'type' => Assessment::TYPE_FINAL, // Official Exam standard
                'total_marks' => $totalCount,
                'evaluation_mode' => 'ai',
                'instructions' => $request->input('instructions') ?: $defaultInstructions,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => Assessment::STATUS_PUBLISHED,
                'is_portal_visible' => true,
                'is_marksheet_saved' => true,
                'saved_at' => now(),
                'is_mock' => true,
                'exam_standard' => $request->input('exam_standard'),
                'total_mcqs' => $totalCount,
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

            return response()->json([
                'success' => true,
                'message' => "Official Mock Examination successfully published with {$totalCount} MCQs.",
                'assessment_id' => $assessment->id,
                'redirect_url' => route('lms.assessments.show', $assessment->id),
            ]);
        });
    }
}
