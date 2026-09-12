<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lms\StoreSubjectMaterialRequest;
use App\Jobs\ProcessDocumentIngestionJob;
use App\Models\AcademicTerm;
use App\Models\InstituteClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectMaterial;
use App\Services\GroqRagService;
use App\Services\RagIntegrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Subject Material Controller
 *
 * Handles Multi-Document uploads per subject for the LMS & RAG pipeline
 * with strict role-based access control (Student, Teacher, Administration).
 */
class SubjectMaterialController extends Controller
{
    protected RagIntegrationService $ragService;
    protected GroqRagService $groqRagService;

    public function __construct(
        RagIntegrationService $ragService,
        GroqRagService $groqRagService
    ) {
        $this->ragService = $ragService;
        $this->groqRagService = $groqRagService;
    }

    /**
     * Get authorized subjects query based on authenticated user role.
     */
    protected function getAuthorizedSubjectsQuery($user)
    {
        $query = Subject::whereHas('instituteClass', fn ($q) => $q->where('institute_id', $user->institute_id))
            ->with(['instituteClass.systemClass']);

        if ($user->isAdministration()) {
            return $query;
        }

        if ($user->isStudent()) {
            $student = $user->getStudentModel();

            if ($student && $student->class_section_id) {
                $section = $student->classSection;
                $classId = $section ? $section->institute_class_id : null;

                // Dynamic Subject Architecture: check granular student_subject_enrollments
                $enrolledSubjectIds = \App\Models\StudentSubjectEnrollment::where('student_id', $user->id)
                    ->where('enrollment_status', 'active')
                    ->pluck('subject_id')
                    ->all();

                $query = Subject::query()->with(['instituteClass.systemClass']);

                if (! empty($enrolledSubjectIds)) {
                    // Downstream Isolation: student only accesses enrolled subjects
                    $query->whereIn('id', $enrolledSubjectIds);
                } else {
                    // Fallback for classes/students without granular enrollments (e.g. junior classes)
                    $query->where(function ($sq) use ($classId, $student) {
                        if ($classId) {
                            $sq->where('institute_class_id', $classId);
                        }
                        $sq->orWhereHas('teacherAssignments', fn ($ta) => $ta->where('class_section_id', $student->class_section_id));
                    });
                }
                return $query;
            } else {
                return Subject::whereRaw('1 = 0');
            }
        } elseif ($user->isTeacher()) {
            $teacherId = $user->id;
            $profileId = $user->teacherProfile?->id;
            $query->where(function ($sq) use ($teacherId, $profileId) {
                $sq->whereHas('teacherAssignments', function ($ta) use ($teacherId, $profileId) {
                    $ta->where('teacher_id', $teacherId);
                    if ($profileId) {
                        $ta->orWhere('teacher_id', $profileId);
                    }
                })->orWhereHas('timetables', function ($tt) use ($teacherId) {
                    $tt->where('teacher_id', $teacherId);
                });
            });
        }

        return $query;
    }

    /**
     * List all classes with registered subjects.
     */
    public function subjectList(Request $request)
    {
        $user = auth()->user();

        $activeTerm = AcademicTerm::where('institute_id', $user->institute_id)
            ->where('is_active', true)
            ->first();

        $authorizedQuery = $this->getAuthorizedSubjectsQuery($user);
        $authorizedClassIds = (clone $authorizedQuery)->pluck('institute_class_id')->unique()->filter();

        $classesQuery = InstituteClass::where('institute_id', $user->institute_id)
            ->when($activeTerm, fn ($q) => $q->where('academic_term_id', $activeTerm->id))
            ->whereHas('subjects')
            ->withCount('subjects')
            ->with(['systemClass', 'sections'])
            ->orderBy('custom_name');

        if (!$user->isAdministration()) {
            $classesQuery->whereIn('id', $authorizedClassIds);
        }

        $classes = $classesQuery->get();

        // Calculate material counts per class
        foreach ($classes as $class) {
            $class->materials_count = SubjectMaterial::whereIn(
                'subject_id',
                $class->subjects()->pluck('id')
            )->count();
        }

        return view('lms.materials.subject_list', compact('classes', 'activeTerm'));
    }

    /**
     * List subjects for a specific class with RAG upload controls.
     */
    public function classSubjects(Request $request, int $classId)
    {
        $user = auth()->user();

        $class = InstituteClass::where('institute_id', $user->institute_id)
            ->with(['systemClass', 'sections'])
            ->findOrFail($classId);

        $query = $this->getAuthorizedSubjectsQuery($user)
            ->where('institute_class_id', $classId)
            ->with(['materials', 'instituteClass'])
            ->orderBy('subject_name');

        $subjects = $query->get();

        return view('lms.materials.class_subjects', compact('class', 'subjects'));
    }

    /**
     * List all materials for a subject (Multi-Document Repository).
     */
    public function index(Request $request, int $subjectId)
    {
        $user = auth()->user();
        $subject = $this->getAuthorizedSubjectsQuery($user)->findOrFail($subjectId);

        $materials = SubjectMaterial::where('subject_id', $subjectId)
            ->with('uploader:id,name')
            ->orderByDesc('created_at')
            ->get();

        if ($request->expectsJson()) {
            return response()->json([
                'subject'   => $subject,
                'materials' => $materials,
            ]);
        }

        return view('lms.materials.index', compact('materials', 'subjectId', 'subject'));
    }

    /**
     * Upload a new document for a subject (PDF, DOCX, DOC, TXT).
     *
     * Uploads are byte-level verified (magic-byte/MIME sniffing) and the
     * ingestion job is dispatched to the document processing queue so
     * large textbooks never block the web request.
     */
    public function store(StoreSubjectMaterialRequest $request, int $subjectId): RedirectResponse|JsonResponse
    {
        $user = auth()->user();
        $subject = $this->getAuthorizedSubjectsQuery($user)->findOrFail($subjectId);

        $validated = $request->validated();
        $documentType = $validated['document_type'] ?? 'textbook';

        $material = $this->groqRagService->uploadSubjectDocument(
            file: $request->file('document'),
            subjectId: $subjectId,
            uploadedBy: $user->id,
            title: $validated['title'],
            documentType: $documentType
        );

        ProcessDocumentIngestionJob::dispatch($material->id)
            ->onQueue((string) config('lms.processing.queue', 'default'));

        if ($request->expectsJson()) {
            return response()->json([
                'message'  => "Material '{$material->title}' uploaded successfully. Ingestion and indexing started.",
                'material' => $material,
            ], 201);
        }

        return redirect()->back()->with('success', "Material '{$material->title}' uploaded and indexed successfully.");
    }

    /**
     * Delete a material.
     */
    public function destroy(Request $request, int $subjectId, SubjectMaterial $material): RedirectResponse|JsonResponse
    {
        if ($material->subject_id !== $subjectId) {
            abort(403, 'Material does not belong to this subject.');
        }

        $user = auth()->user();
        if ($user->id !== $material->uploaded_by && !$user->isAdministration()) {
            abort(403, 'Unauthorized to delete this material.');
        }

        $title = $material->title;
        $material->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => "Material '{$title}' deleted."]);
        }

        return redirect()->back()->with('success', "Material '{$title}' deleted.");
    }

    /**
     * RAG Chatbot: Answer student's question using book context and vector citations.
     */
    public function askChatbot(Request $request, int $subjectId): JsonResponse
    {
        $validated = $request->validate([
            'question'       => 'required|string|max:2000',
            'session_id'     => 'nullable|string',
            'chapter_number' => 'nullable|integer',
            'topic'          => 'nullable|string',
        ]);

        $subject = $this->getAuthorizedSubjectsQuery(auth()->user())->find($subjectId);
        if (! $subject) {
            return response()->json(['error' => 'You are not authorized to access this subject.'], 403);
        }

        $filters = array_filter([
            'chapter_number' => $validated['chapter_number'] ?? null,
            'topic'          => $validated['topic'] ?? null,
        ]);

        $result = $this->groqRagService->answerStudentQuery(
            subjectId: $subjectId,
            query: $validated['question'],
            scopeFilters: $filters,
            sessionId: $validated['session_id'] ?? null,
            userId: auth()->id()
        );

        return response()->json([
            'session_id' => $result['session_id'],
            'answer'     => $result['answer'],
            'sources'    => $result['sources'],
            'confidence' => $result['confidence'],
        ]);
    }
}
