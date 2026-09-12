<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\ChatHistory;
use App\Models\InstituteClass;
use App\Models\Student;
use App\Models\Subject;
use App\Services\RagIntegrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Chatbot Controller
 *
 * Full-page RAG chatbot experience with persistent conversation history.
 * Students and teachers can only view and select their enrolled subjects.
 * Principals and administrative staff can view all subjects filtered by class.
 */
class ChatbotController extends Controller
{
    protected RagIntegrationService $ragService;

    public function __construct(RagIntegrationService $ragService)
    {
        $this->ragService = $ragService;
    }

    /**
     * Build base query for subjects authorized for the given user.
     */
    protected function getAuthorizedSubjectsQuery($user)
    {
        $query = Subject::whereHas('instituteClass', fn ($q) => $q->where('institute_id', $user->institute_id))
            ->with(['instituteClass.systemClass']);

        // Principal / Administration (or any staff with delegated admin rights) can view all subjects
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
                    // Strict downstream isolation: student only sees enrolled subjects
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
     * Show the full chatbot page.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $isAdministration = $user->isAdministration();

        $query = $this->getAuthorizedSubjectsQuery($user);

        // Fetch available classes for filtering
        if ($user->isStudent()) {
            $student = $user->getStudentModel();
            $classes = collect();
            if ($student && $student->classSection && $student->classSection->instituteClass) {
                $classes = collect([$student->classSection->instituteClass]);
                $selectedClassId = $student->classSection->institute_class_id;
            } else {
                $selectedClassId = null;
            }
        } elseif ($isAdministration) {
            $classes = InstituteClass::where('institute_id', $user->institute_id)
                ->with(['systemClass', 'subjects'])
                ->withCount('subjects')
                ->orderBy('custom_name')
                ->get();
            $selectedClassId = $request->query('class_id');
        } else {
            // For teachers who may teach across multiple classes, get their enrolled classes
            $enrolledClassIds = (clone $query)->pluck('institute_class_id')->unique()->filter();
            $classes = InstituteClass::whereIn('id', $enrolledClassIds)
                ->with(['systemClass', 'subjects'])
                ->withCount('subjects')
                ->orderBy('custom_name')
                ->get();
            $selectedClassId = $request->query('class_id');
        }

        $selectedSubjectId = $request->query('subject_id');

        // If a subject is selected, ensure class_id matches the subject's class
        if ($selectedSubjectId) {
            $selectedSubject = (clone $query)->find($selectedSubjectId);
            if ($selectedSubject) {
                $selectedClassId = $selectedSubject->institute_class_id;
            }
        }

        $subjects = $query->withCount(['materials' => fn ($q) => $q->where('is_rag_indexed', true)])
            ->orderBy('subject_name')
            ->get();

        $subjectsJson = $subjects->map(fn ($s) => [
            'id' => (int) $s->id,
            'name' => $s->subject_name,
            'class_id' => (int) $s->institute_class_id,
            'class_name' => $s->instituteClass->name ?? '',
            'materials_count' => (int) ($s->materials_count ?? 0),
        ])->values();

        $selectedSessionId = $request->query('session_id');

        $sessions = [];
        $messages = [];
        if ($selectedSubjectId) {
            $sessions = $this->ragService->getUserSessions(auth()->id(), $selectedSubjectId);

            if ($selectedSessionId) {
                $messages = $this->ragService->getSessionMessages(auth()->id(), $selectedSubjectId, $selectedSessionId);
            }
        }

        return view('lms.chatbot.index', compact(
            'subjects',
            'subjectsJson',
            'classes',
            'isAdministration',
            'selectedClassId',
            'selectedSubjectId',
            'selectedSessionId',
            'sessions',
            'messages'
        ));
    }

    /**
     * Handle an AJAX chat message (Standard REST fallback).
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'question' => 'required|string|max:2000',
            'session_id' => 'nullable|string|max:64',
            'mode' => 'nullable|string|in:short,long,summary',
        ]);

        $subject = $this->getAuthorizedSubjectsQuery(auth()->user())->find($validated['subject_id']);
        if (!$subject) {
            return response()->json(['error' => 'You are not enrolled in or authorized to access this subject.'], 403);
        }

        $result = $this->ragService->answerStudentQuery(
            subjectId: $validated['subject_id'],
            query: $validated['question'],
            sessionId: $validated['session_id'] ?? null,
            userId: auth()->id(),
            mode: $validated['mode'] ?? 'short',
        );

        return response()->json($result);
    }

    /**
     * Handle real-time Live Chatbot Token Streaming via Server-Sent Events (SSE).
     */
    public function streamMessage(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'question' => 'required|string|max:2000',
            'session_id' => 'nullable|string|max:64',
            'mode' => 'nullable|string|in:short,long,summary',
        ]);

        $subject = $this->getAuthorizedSubjectsQuery(auth()->user())->find($validated['subject_id']);
        if (!$subject) {
            return response()->json(['error' => 'You are not enrolled in or authorized to access this subject.'], 403);
        }

        $userId = auth()->id();

        return response()->stream(function () use ($validated, $userId) {
            while (ob_get_level() > 0) {
                ob_end_flush();
            }

            $this->ragService->streamStudentQuery(
                subjectId: (int) $validated['subject_id'],
                query: $validated['question'],
                sessionId: $validated['session_id'] ?? null,
                userId: $userId,
                mode: $validated['mode'] ?? 'short',
                onChunk: function ($token, $isFinal = false, $meta = []) {
                    if ($isFinal) {
                        echo "event: done\n";
                        echo "data: " . json_encode($meta) . "\n\n";
                    } else {
                        echo "event: token\n";
                        echo "data: " . json_encode(['token' => $token]) . "\n\n";
                    }
                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                }
            );
        }, 200, [
            'Content-Type' => 'text/event-stream; charset=utf-8',
            'Cache-Control' => 'no-cache, no-transform',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Get conversation history for a session.
     */
    public function getHistory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'session_id' => 'required|string|max:64',
        ]);

        $subject = $this->getAuthorizedSubjectsQuery(auth()->user())->find($validated['subject_id']);
        if (!$subject) {
            return response()->json(['error' => 'You are not authorized to view history for this subject.'], 403);
        }

        $messages = $this->ragService->getSessionMessages(
            auth()->id(),
            $validated['subject_id'],
            $validated['session_id'],
        );

        return response()->json(['messages' => $messages]);
    }

    /**
     * Get list of sessions for a subject.
     */
    public function getSessions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $subject = $this->getAuthorizedSubjectsQuery(auth()->user())->find($validated['subject_id']);
        if (!$subject) {
            return response()->json(['error' => 'You are not authorized to view sessions for this subject.'], 403);
        }

        $sessions = $this->ragService->getUserSessions(auth()->id(), $validated['subject_id']);

        return response()->json(['sessions' => $sessions]);
    }

    /**
     * Delete a chat session.
     */
    public function deleteSession(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'session_id' => 'required|string|max:64',
        ]);

        ChatHistory::where('user_id', auth()->id())
            ->where('subject_id', $validated['subject_id'])
            ->where('session_id', $validated['session_id'])
            ->delete();

        return response()->json(['message' => 'Session deleted.']);
    }
}

