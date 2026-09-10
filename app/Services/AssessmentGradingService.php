<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use App\Models\StudentAssessmentAnswer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Assessment Grading Service
 *
 * Routes grading through the appropriate pipeline:
 * - MCQs → instant auto-evaluation against correct_answer
 * - Short/Long (AI mode) → RagIntegrationService for LLM evaluation
 * - Short/Long (Manual mode) → left as pending for teacher review
 */
class AssessmentGradingService
{
    protected RagIntegrationService $ragService;

    public function __construct(RagIntegrationService $ragService)
    {
        $this->ragService = $ragService;
    }

    // ── Grade Entire Assessment for a Student ─────────────────────────────────

    /**
     * Grade all submitted answers for a student on an assessment.
     *
     * @return array{total_marks: float, max_marks: int, questions_graded: int, questions_pending: int}
     */
    public function gradeStudentAssessment(int $assessmentId, int $studentId): array
    {
        $assessment = Assessment::with('questions')->findOrFail($assessmentId);
        $answers = StudentAssessmentAnswer::where('student_id', $studentId)
            ->whereIn('assessment_question_id', $assessment->questions->pluck('id'))
            ->get()
            ->keyBy('assessment_question_id');

        $totalMarksAwarded = 0;
        $questionsGraded = 0;
        $questionsPending = 0;

        DB::transaction(function () use ($assessment, $answers, &$totalMarksAwarded, &$questionsGraded, &$questionsPending) {
            foreach ($assessment->questions as $question) {
                $answer = $answers->get($question->id);

                if (! $answer || $answer->isGraded()) {
                    if ($answer && $answer->isGraded()) {
                        $totalMarksAwarded += (float) $answer->marks_awarded;
                        $questionsGraded++;
                    }

                    continue;
                }

                $result = $this->gradeQuestion($question, $answer, $assessment);

                if ($result !== null) {
                    $totalMarksAwarded += $result['marks'];
                    $questionsGraded++;
                } else {
                    $questionsPending++;
                }
            }
        });

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

        return [
            'total_marks' => round($totalMarksAwarded, 2),
            'max_marks' => $assessment->total_marks,
            'questions_graded' => $questionsGraded,
            'questions_pending' => $questionsPending,
        ];
    }

    // ── Grade Individual Question ─────────────────────────────────────────────

    /**
     * Grade a single question's answer based on question type and evaluation mode.
     *
     * @return array{marks: float, feedback: string}|null Null if left for manual grading
     */
    protected function gradeQuestion(
        AssessmentQuestion $question,
        StudentAssessmentAnswer $answer,
        Assessment $assessment
    ): ?array {
        // MCQs are always auto-graded regardless of evaluation mode
        if ($question->isMcq()) {
            return $this->gradeMcq($question, $answer);
        }

        // Short/Long questions: check evaluation mode
        if ($assessment->isAiEvaluated()) {
            return $this->gradeWithAi($question, $answer, $assessment);
        }

        // Manual mode: leave as pending
        return null;
    }

    // ── MCQ Auto-Grading ─────────────────────────────────────────────────────

    /**
     * Instantly grade an MCQ by comparing provided answer to correct answer.
     *
     * @return array{marks: float, feedback: string}
     */
    protected function gradeMcq(AssessmentQuestion $question, StudentAssessmentAnswer $answer): array
    {
        $isCorrect = $question->isCorrectAnswer($answer->provided_answer ?? '');

        $marks = $isCorrect ? $question->marks : 0;
        $feedback = $isCorrect
            ? 'Correct answer.'
            : "Incorrect. The correct answer is: {$question->correct_answer}";

        $answer->update([
            'marks_awarded' => $marks,
            'ai_feedback' => $feedback,
            'grading_status' => StudentAssessmentAnswer::STATUS_AUTO_GRADED,
        ]);

        return ['marks' => $marks, 'feedback' => $feedback];
    }

    // ── AI/LLM-Powered Grading ───────────────────────────────────────────────

    /**
     * Grade a short/long answer using the RAG Integration Service.
     * Validates conceptual alignment and step-by-step logic.
     *
     * @return array{marks: float, feedback: string}
     */
    protected function gradeWithAi(
        AssessmentQuestion $question,
        StudentAssessmentAnswer $answer,
        Assessment $assessment
    ): array {
        try {
            $result = $this->ragService->evaluateAnswer(
                question: $question->statement,
                studentAnswer: $answer->provided_answer ?? '',
                correctAnswer: $question->correct_answer ?? '',
                totalMarks: $question->marks,
                subjectId: $assessment->subject_id,
                questionType: $question->question_type,
            );

            $answer->update([
                'marks_awarded' => $result['marks'],
                'ai_feedback' => $result['feedback'],
                'grading_status' => StudentAssessmentAnswer::STATUS_AI_GRADED,
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error("GradingService: AI grading failed for answer ID {$answer->id}: {$e->getMessage()}");

            // Mark as pending for manual review on AI failure
            $answer->update([
                'ai_feedback' => 'AI grading encountered an error. Marked for manual review.',
                'grading_status' => StudentAssessmentAnswer::STATUS_PENDING,
            ]);

            return null;
        }
    }

    // ── Manual Grading (Teacher Override) ─────────────────────────────────────

    /**
     * Allow a teacher to manually grade or override an answer's marks.
     *
     * @return array{marks: float, feedback: string}
     */
    public function manuallyGrade(
        StudentAssessmentAnswer $answer,
        float $marks,
        string $feedback,
        int $gradedBy
    ): array {
        $maxMarks = $answer->question->marks;
        $marks = min($marks, $maxMarks);

        $answer->update([
            'marks_awarded' => $marks,
            'ai_feedback' => $feedback,
            'grading_status' => StudentAssessmentAnswer::STATUS_MANUALLY_GRADED,
            'graded_by' => $gradedBy,
        ]);

        // Trigger continuous grade normalization
        try {
            $assessment = $answer->question->assessment;
            if ($assessment) {
                app(\App\Services\GradeNormalizationService::class)->recalculateAndSyncClassResults(
                    $assessment->subject_id,
                    $assessment->academic_term_id,
                    $assessment->class_section_id
                );
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Continuous Grading trigger failed: '.$e->getMessage());
        }

        return ['marks' => $marks, 'feedback' => $feedback];
    }

    // ── Bulk Auto-Grade All MCQs ─────────────────────────────────────────────

    /**
     * Auto-grade all pending MCQ answers for an assessment.
     * Useful for batch processing after submission deadline.
     *
     * @return array{graded_count: int, total_mcq_answers: int}
     */
    public function bulkAutoGradeMcqs(int $assessmentId): array
    {
        $assessment = Assessment::with('questions')->findOrFail($assessmentId);

        $mcqQuestionIds = $assessment->questions
            ->where('question_type', AssessmentQuestion::TYPE_MCQ)
            ->pluck('id');

        $pendingAnswers = StudentAssessmentAnswer::whereIn('assessment_question_id', $mcqQuestionIds)
            ->where('grading_status', StudentAssessmentAnswer::STATUS_PENDING)
            ->with('question')
            ->get();

        $gradedCount = 0;

        DB::transaction(function () use ($pendingAnswers, &$gradedCount) {
            foreach ($pendingAnswers as $answer) {
                $this->gradeMcq($answer->question, $answer);
                $gradedCount++;
            }
        });

        // Trigger continuous grade normalization
        try {
            app(\App\Services\GradeNormalizationService::class)->recalculateAndSyncClassResults(
                $assessment->subject_id,
                $assessment->academic_term_id,
                $assessment->class_section_id
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Continuous Grading trigger failed: '.$e->getMessage());
        }

        return [
            'graded_count' => $gradedCount,
            'total_mcq_answers' => $pendingAnswers->count(),
        ];
    }
}
