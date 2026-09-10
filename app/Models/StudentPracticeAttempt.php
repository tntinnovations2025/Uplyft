<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Student Practice Attempt
 *
 * Tracks individual practice test attempts per student per subject,
 * with score breakdowns, AI-generated feedback, and letter grades.
 */
class StudentPracticeAttempt extends Model
{
    protected $fillable = [
        'user_id',
        'subject_id',
        'practice_test_session_id',
        'attempt_number',
        'score_percentage',
        'total_marks',
        'obtained_marks',
        'mcq_correct',
        'mcq_total',
        'short_answered',
        'short_total',
        'long_answered',
        'long_total',
        'question_breakdown',
        'ai_summary_feedback',
        'score_grade',
        'completed_at',
    ];

    protected $casts = [
        'score_percentage' => 'decimal:2',
        'total_marks'      => 'decimal:2',
        'obtained_marks'   => 'decimal:2',
        'question_breakdown' => 'array',
        'completed_at'     => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function practiceTestSession(): BelongsTo
    {
        return $this->belongsTo(PracticeTestSession::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    /**
     * Scope to retrieve latest N attempts for a student in a subject.
     */
    public function scopeLatestForStudent($query, int $userId, int $subjectId, int $limit = 10)
    {
        return $query->where('user_id', $userId)
            ->where('subject_id', $subjectId)
            ->orderByDesc('completed_at')
            ->limit($limit);
    }

    /**
     * Scope to retrieve all attempts for a student across all subjects.
     */
    public function scopeForStudent($query, int $userId)
    {
        return $query->where('user_id', $userId)->orderByDesc('completed_at');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Calculate letter grade from percentage.
     */
    public static function calculateGrade(float $percentage): string
    {
        return match (true) {
            $percentage >= 90 => 'A+',
            $percentage >= 80 => 'A',
            $percentage >= 70 => 'B',
            $percentage >= 60 => 'C',
            $percentage >= 50 => 'D',
            default => 'F',
        };
    }

    /**
     * Get the next attempt number for a student-subject pair.
     */
    public static function nextAttemptNumber(int $userId, int $subjectId): int
    {
        $maxAttempts = config('lms.practice_tests.max_attempts_per_subject');

        $currentAttempts = static::where('user_id', $userId)
            ->where('subject_id', $subjectId)
            ->count();

        if ($currentAttempts >= $maxAttempts) {
            return 0; // Quota reached, no more attempts allowed
        }

        return $currentAttempts + 1;
    }
}
