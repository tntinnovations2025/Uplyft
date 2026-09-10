<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\InstituteClass;
use App\Models\PracticeTestSession;
use App\Models\Student;
use App\Models\Subject;
use App\Services\AssessmentEngineService;
use App\Services\GroqRagService;
use App\Services\RagIntegrationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PracticeTestController extends Controller
{
    protected RagIntegrationService $ragService;
    protected GroqRagService $groqRagService;
    protected AssessmentEngineService $assessmentEngine;

    public function __construct(
        RagIntegrationService $ragService,
        GroqRagService $groqRagService,
        AssessmentEngineService $assessmentEngine
    ) {
        $this->ragService = $ragService;
        $this->groqRagService = $groqRagService;
        $this->assessmentEngine = $assessmentEngine;
    }

    /**
     * Build base query for subjects authorized for the given user.
     */
    protected function getAuthorizedSubjectsQuery($user)
    {
        $query = Subject::whereHas('instituteClass', fn ($q) => $q->where('institute_id', $user->institute_id))
            ->with(['instituteClass.systemClass']);

        // Principal / Administration can view all subjects
        if ($user->isAdministration()) {
            return $query;
        }

        if ($user->isStudent()) {
            $student = $user->getStudentModel();

            if ($student && $student->class_section_id) {
                $section = $student->classSection;
                $classId = $section ? $section->institute_class_id : null;
                $query = Subject::query()->with(['instituteClass.systemClass']);
                $query->where(function ($sq) use ($classId, $student) {
                    if ($classId) {
                        $sq->where('institute_class_id', $classId);
                    }
                    $sq->orWhereHas('teacherAssignments', fn ($ta) => $ta->where('class_section_id', $student->class_section_id));
                });
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
     * Show Practice Test Generator dashboard & history.
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
            $enrolledClassIds = (clone $query)->pluck('institute_class_id')->unique()->filter();
            $classes = InstituteClass::whereIn('id', $enrolledClassIds)
                ->with(['systemClass', 'subjects'])
                ->withCount('subjects')
                ->orderBy('custom_name')
                ->get();
            $selectedClassId = $request->query('class_id');
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

        $recentTests = PracticeTestSession::where('user_id', $user->id)
            ->with(['subject.instituteClass', 'practiceAttempt'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('lms.practice_test.index', compact('subjects', 'subjectsJson', 'classes', 'isAdministration', 'selectedClassId', 'recentTests'));
    }

    /**
     * Generate an instant AI practice test with deduplication from textbook context.
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'subject_id'         => 'required|exists:subjects,id',
            'mcq_count'          => 'required|integer|min:0|max:20',
            'short_count'        => 'required|integer|min:0|max:10',
            'long_count'         => 'required|integer|min:0|max:5',
            'topic'              => 'nullable|string|max:255',
            'portion'            => 'nullable|in:first_half,second_half,complete',
            'chapter_number'     => 'nullable|integer|min:1',
            'page_start'         => 'nullable|integer|min:1',
            'page_end'           => 'nullable|integer|gte:page_start',
            'enable_timer'       => 'nullable|boolean',
            'timer_mode'         => 'nullable|in:duration,scheduled',
            'time_limit_minutes' => 'nullable|integer|min:1|max:180',
            'scheduled_start'    => 'nullable|date_format:H:i',
            'scheduled_end'      => 'nullable|date_format:H:i',
        ]);

        $subject = $this->getAuthorizedSubjectsQuery(auth()->user())->find($validated['subject_id']);
        if (!$subject) {
            return redirect()->back()->withErrors(['subject_id' => 'You are not enrolled in or authorized to generate tests for this subject.']);
        }

        if ($validated['mcq_count'] == 0 && $validated['short_count'] == 0 && $validated['long_count'] == 0) {
            return redirect()->back()->with('error', 'Please select at least 1 question (MCQ, Short, or Long answer).');
        }

        $topic = $validated['topic'] ?: 'Comprehensive Practice Test';

        $scopeFilters = array_filter([
            'portion'        => $validated['portion'] ?? null,
            'chapter_number' => $validated['chapter_number'] ?? null,
            'page_start'     => $validated['page_start'] ?? null,
            'page_end'       => $validated['page_end'] ?? null,
            'topic'          => $topic,
        ]);

        // Generate questions from RAG context with Groq & deduplication
        $generated = $this->groqRagService->generateGroqTest(
            subjectId: $validated['subject_id'],
            topic: $topic,
            mcqCount: (int) $validated['mcq_count'],
            shortCount: (int) $validated['short_count'],
            longCount: (int) $validated['long_count'],
            scopeFilters: $scopeFilters
        );

        $questions = $generated['questions'] ?? [];

        // Fallback to RagIntegrationService if Groq returns empty
        if (empty($questions)) {
            $fallback = $this->ragService->generateAssessmentFromBook(
                subjectId: $validated['subject_id'],
                topic: $topic,
                mcqCount: (int) $validated['mcq_count'],
                shortCount: (int) $validated['short_count'],
                longCount: (int) $validated['long_count'],
                scopeFilters: $scopeFilters
            );
            $questions = $fallback['questions'] ?? [];
        }

        // Deduplicate generated questions using AssessmentEngineService
        $questions = $this->assessmentEngine->deduplicateQuestions($questions, null, $validated['subject_id']);

        if (empty($questions)) {
            return redirect()->back()->with('error', 'No text content available in uploaded materials for this subject scope to generate questions.');
        }

        // Calculate total marks
        $totalMarks = 0;
        foreach ($questions as $q) {
            $totalMarks += ($q['max_marks'] ?? 5);
        }

        // Timer calculation
        $scheduledStartAt = null;
        $scheduledEndAt = null;
        $timeLimitMinutes = null;

        if (!empty($validated['enable_timer'])) {
            if (($validated['timer_mode'] ?? 'duration') === 'scheduled' && !empty($validated['scheduled_start']) && !empty($validated['scheduled_end'])) {
                $today = Carbon::today()->toDateString();
                $scheduledStartAt = Carbon::parse("{$today} {$validated['scheduled_start']}");
                $scheduledEndAt = Carbon::parse("{$today} {$validated['scheduled_end']}");

                if ($scheduledEndAt->isBefore($scheduledStartAt)) {
                    $scheduledEndAt->addDay();
                }

                $timeLimitMinutes = max(1, $scheduledStartAt->diffInMinutes($scheduledEndAt));
            } else {
                $timeLimitMinutes = (int) ($validated['time_limit_minutes'] ?? 15);
                $scheduledStartAt = now();
                $scheduledEndAt = now()->addMinutes($timeLimitMinutes);
            }
        }

        $testSession = PracticeTestSession::create([
            'id'                 => (string) Str::uuid(),
            'user_id'            => auth()->id(),
            'subject_id'         => $validated['subject_id'],
            'title'              => $generated['title'] ?? ("Practice Test: {$topic}"),
            'mcq_count'          => (int) $validated['mcq_count'],
            'short_count'        => (int) $validated['short_count'],
            'long_count'         => (int) $validated['long_count'],
            'total_marks'        => $totalMarks,
            'questions'          => $questions,
            'time_limit_minutes' => $timeLimitMinutes,
            'scheduled_start_at' => $scheduledStartAt,
            'scheduled_end_at'   => $scheduledEndAt,
            'status'             => 'in_progress',
        ]);

        return redirect()->route('lms.practice-test.take', $testSession->id);
    }

    /**
     * Take an interactive practice test.
     */
    public function take(string $id)
    {
        $test = PracticeTestSession::where('user_id', auth()->id())
            ->with('subject')
            ->findOrFail($id);

        if (in_array($test->status, ['submitted', 'evaluated'])) {
            return redirect()->route('lms.practice-test.result', $test->id);
        }

        // Auto submit if timer has expired
        if ($test->scheduled_end_at && now()->isAfter($test->scheduled_end_at)) {
            return $this->evaluateAndSave($test, []);
        }

        return view('lms.practice_test.take', compact('test'));
    }

    /**
     * Submit student test answers for AI evaluation.
     */
    public function submit(Request $request, string $id)
    {
        $test = PracticeTestSession::where('user_id', auth()->id())
            ->findOrFail($id);

        if (in_array($test->status, ['submitted', 'evaluated'])) {
            return redirect()->route('lms.practice-test.result', $test->id);
        }

        $studentAnswers = $request->input('answers', []);

        return $this->evaluateAndSave($test, $studentAnswers);
    }

    /**
     * Display detailed test scorecard and evaluation.
     */
    public function result(string $id)
    {
        $test = PracticeTestSession::where('user_id', auth()->id())
            ->with(['subject', 'practiceAttempt'])
            ->findOrFail($id);

        return view('lms.practice_test.result', compact('test'));
    }

    /**
     * Get student attempt history for a given subject (JSON or partial).
     */
    public function history(Request $request, int $subjectId)
    {
        $user = auth()->user();
        $history = $this->assessmentEngine->getStudentAttemptHistory($user->id, $subjectId, 10);

        if ($request->expectsJson()) {
            return response()->json([
                'subject_id' => $subjectId,
                'attempts'   => $history,
            ]);
        }

        return response()->json(['attempts' => $history]);
    }

    /**
     * Internal helper to evaluate test answers against textbook context and log attempt.
     */
    protected function evaluateAndSave(PracticeTestSession $test, array $studentAnswers)
    {
        $questions = $test->questions ?? [];
        $evaluationResults = [];
        $totalObtained = 0.0;

        foreach ($questions as $idx => $q) {
            $type = $q['question_type'] ?? 'short';
            $maxMarks = (float) ($q['max_marks'] ?? 5);
            $correctAnswer = $q['correct_answer'] ?? '';
            $studentAns = trim($studentAnswers[$idx] ?? '');

            if ($type === 'mcq') {
                $isCorrect = (mb_strtolower(trim($studentAns)) === mb_strtolower(trim($correctAnswer)));
                $obtained = $isCorrect ? $maxMarks : 0.0;
                $feedback = $isCorrect
                    ? "Correct! Option '{$studentAns}' matches the textbook solution."
                    : "Incorrect. Your selection: '{$studentAns}'. Correct option: '{$correctAnswer}'.";

                $evaluationResults[] = [
                    'question_index' => $idx + 1,
                    'question_type'  => 'mcq',
                    'question_text'  => $q['question_text'],
                    'student_answer' => $studentAns,
                    'correct_answer' => $correctAnswer,
                    'max_marks'      => $maxMarks,
                    'obtained_marks' => $obtained,
                    'is_correct'     => $isCorrect,
                    'feedback'       => $feedback,
                ];
                $totalObtained += $obtained;
            } else {
                // Short or Long Answer evaluated using RAG context
                if (empty($studentAns)) {
                    $obtained = 0.0;
                    $feedback = 'No answer submitted for this question.';
                } else {
                    $eval = $this->ragService->evaluateAnswer(
                        question: $q['question_text'],
                        studentAnswer: $studentAns,
                        correctAnswer: $correctAnswer,
                        totalMarks: (int) $maxMarks,
                        subjectId: $test->subject_id,
                        questionType: $type
                    );
                    $obtained = (float) ($eval['score'] ?? 0.0);
                    $feedback = $eval['feedback'] ?? 'Evaluated against subject textbook context.';
                }

                $evaluationResults[] = [
                    'question_index' => $idx + 1,
                    'question_type'  => $type,
                    'question_text'  => $q['question_text'],
                    'student_answer' => $studentAns,
                    'correct_answer' => $correctAnswer,
                    'max_marks'      => $maxMarks,
                    'obtained_marks' => $obtained,
                    'feedback'       => $feedback,
                ];
                $totalObtained += $obtained;
            }
        }

        $percentage = $test->total_marks > 0 ? round(($totalObtained / $test->total_marks) * 100, 1) : 0;
        $summaryFeedback = "You scored {$totalObtained} out of {$test->total_marks} ({$percentage}%). Review the questions above to strengthen your understanding.";

        $test->update([
            'student_answers'    => $studentAnswers,
            'evaluation_results' => $evaluationResults,
            'obtained_marks'     => round($totalObtained, 2),
            'submitted_at'       => now(),
            'status'             => 'evaluated',
            'ai_feedback'        => $summaryFeedback,
        ]);

        // Log into dedicated StudentPracticeAttempt history table
        $attempt = $this->assessmentEngine->logPracticeAttempt($test);

        if ($attempt === null) {
            // Quota reached - redirect with warning
            return redirect()->route('lms.practice-test.result', $test->id)
                ->with('warning', 'You have reached the maximum number of practice attempts per subject. ' . config('lms.practice_tests.max_attempts_per_subject') . ' attempts are allowed.');
        }

        return redirect()->route('lms.practice-test.result', $test->id);
    }
}
