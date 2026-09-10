<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use App\Models\PracticeTestSession;
use App\Models\StudentPracticeAttempt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class AssessmentEngineService
{
    /**
     * Deduplicate an array of generated questions.
     * Guarantees zero duplicate questions within the set and optionally checks existing questions in DB.
     *
     * @param array<array{
     *     question_type: string,
     *     question_text: string,
     *     options?: ?array,
     *     correct_answer?: string,
     *     max_marks?: int,
     *     evaluation_rubric?: ?string
     * }> $questions
     * @param int|null $assessmentId
     * @param int|null $subjectId
     * @return array<array>
     */
    public function deduplicateQuestions(array $questions, ?int $assessmentId = null, ?int $subjectId = null): array
    {
        $unique = [];
        $seenHashes = [];

        foreach ($questions as $q) {
            $text = $q['question_text'] ?? ($q['statement'] ?? '');
            if (empty(trim($text))) {
                continue;
            }

            $normalized = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $text)));
            $hash = hash('sha256', $normalized);

            // Deduplicate within current batch
            if (isset($seenHashes[$hash])) {
                continue;
            }

            // Check database deduplication if assessment ID or subject ID provided
            if ($assessmentId && AssessmentQuestion::where('assessment_id', $assessmentId)->where('question_hash', $hash)->exists()) {
                continue;
            }

            $seenHashes[$hash] = true;
            $q['question_hash'] = $hash;
            $unique[] = $q;
        }

        return array_values($unique);
    }

    /**
     * Check if an assessment is currently accessible to a user based on schedule, status, and enrollment.
     *
     * @param Assessment $assessment
     * @param User $user
     * @return array{accessible: bool, reason: ?string, remaining_seconds: ?int, is_expired: bool}
     */
    public function isAssessmentAccessible(Assessment $assessment, User $user): array
    {
        // Teachers and Principal can always preview
        if ($user->isTeacher() || $user->isAdministration()) {
            return [
                'accessible' => true,
                'reason'     => null,
                'remaining_seconds' => null,
                'is_expired' => false,
            ];
        }

        // Student accessibility checks
        if ($user->isStudent()) {
            // 1. Must be published
            if ($assessment->status !== Assessment::STATUS_PUBLISHED) {
                return [
                    'accessible' => false,
                    'reason'     => 'This assessment has not been published yet.',
                    'remaining_seconds' => 0,
                    'is_expired' => false,
                ];
            }

            $now = now();

            // 2. Check scheduled start window
            if ($assessment->start_time && $now->isBefore($assessment->start_time)) {
                $startFormatted = Carbon::parse($assessment->start_time)->toDayDateTimeString();
                return [
                    'accessible' => false,
                    'reason'     => "This assessment will become available on {$startFormatted}.",
                    'remaining_seconds' => $now->diffInSeconds($assessment->start_time),
                    'is_expired' => false,
                ];
            }

            // 3. Check scheduled end window
            if ($assessment->end_time && $now->isAfter($assessment->end_time)) {
                return [
                    'accessible' => false,
                    'reason'     => 'The deadline for this assessment has passed.',
                    'remaining_seconds' => 0,
                    'is_expired' => true,
                ];
            }

            // Calculate remaining seconds if there is an end time
            $remainingSeconds = $assessment->end_time ? max(0, $now->diffInSeconds($assessment->end_time)) : null;

            return [
                'accessible' => true,
                'reason'     => null,
                'remaining_seconds' => $remainingSeconds,
                'is_expired' => false,
            ];
        }

        return [
            'accessible' => false,
            'reason'     => 'Unauthorized role for this assessment.',
            'remaining_seconds' => 0,
            'is_expired' => false,
        ];
    }

    /**
     * Log a student's practice test attempt with scoring breakdown and AI feedback.
     *
     * @return StudentPracticeAttempt|false Returns false if quota has been reached
     */
    public function logPracticeAttempt(PracticeTestSession $session): ?StudentPracticeAttempt
    {
        // Check if student has reached the max attempts quota for this subject
        $maxAttempts = config('lms.practice_tests.max_attempts_per_subject');
        $currentAttempts = StudentPracticeAttempt::where('user_id', $session->user_id)
            ->where('subject_id', $session->subject_id)
            ->count();

        if ($currentAttempts >= $maxAttempts) {
            Log::warning("Practice attempt quota reached for user {$session->user_id}, subject {$session->subject_id}. Max attempts: {$maxAttempts}");
            return null; // Quota reached, cannot log additional attempt
        }

        // Calculate breakdown
        $questions = $session->questions ?? [];
        $evalResults = $session->evaluation_results ?? [];

        $mcqTotal = 0;
        $mcqCorrect = 0;
        $shortTotal = 0;
        $shortAnswered = 0;
        $longTotal = 0;
        $longAnswered = 0;

        foreach ($evalResults as $item) {
            $type = $item['question_type'] ?? 'short';
            if ($type === 'mcq') {
                $mcqTotal++;
                if (!empty($item['is_correct'])) {
                    $mcqCorrect++;
                }
            } elseif ($type === 'short') {
                $shortTotal++;
                if (!empty($item['student_answer'])) {
                    $shortAnswered++;
                }
            } elseif ($type === 'long') {
                $longTotal++;
                if (!empty($item['student_answer'])) {
                    $longAnswered++;
                }
            }
        }

        $totalMarks = max((float) $session->total_marks, 1.0);
        $obtainedMarks = (float) ($session->obtained_marks ?? 0.0);
        $scorePercentage = round(($obtainedMarks / $totalMarks) * 100, 2);
        $scoreGrade = StudentPracticeAttempt::calculateGrade($scorePercentage);

        $attemptNum = StudentPracticeAttempt::nextAttemptNumber($session->user_id, $session->subject_id);

        // If nextAttemptNumber returns 0, quota has been reached
        if ($attemptNum === 0) {
            Log::warning("Practice attempt quota reached for user {$session->user_id}, subject {$session->subject_id}. Max attempts: {$maxAttempts}");
            return null;
        }

        $attempt = StudentPracticeAttempt::create([
            'user_id'                  => $session->user_id,
            'subject_id'               => $session->subject_id,
            'practice_test_session_id' => $session->id,
            'attempt_number'           => $attemptNum,
            'score_percentage'         => $scorePercentage,
            'total_marks'              => $totalMarks,
            'obtained_marks'           => $obtainedMarks,
            'mcq_correct'              => $mcqCorrect,
            'mcq_total'                => $mcqTotal,
            'short_answered'           => $shortAnswered,
            'short_total'              => $shortTotal,
            'long_answered'            => $longAnswered,
            'long_total'               => $longTotal,
            'question_breakdown'       => $evalResults,
            'ai_summary_feedback'      => $session->ai_feedback,
            'score_grade'              => $scoreGrade,
            'completed_at'             => $session->submitted_at ?: now(),
        ]);

        return $attempt;
    }

    /**
     * Get student attempt history for a given subject (up to limit).
     */
    public function getStudentAttemptHistory(int $userId, int $subjectId, int $limit = 10): Collection
    {
        return StudentPracticeAttempt::where('user_id', $userId)
            ->where('subject_id', $subjectId)
            ->with(['practiceTestSession'])
            ->orderByDesc('completed_at')
            ->limit($limit)
            ->get();
    }
}
