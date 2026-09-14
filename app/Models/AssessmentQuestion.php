<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\TenantIsolated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentQuestion extends Model
{
    use HasFactory, TenantIsolated;

    protected static function applyTenantIsolation(Builder $builder, array $campusIds): void
    {
        $builder->whereHas('assessment.subject.instituteClass', fn ($q) => $q->whereIn('institute_id', $campusIds));
    }

    public const TYPE_MCQ = 'mcq';

    public const TYPE_SHORT = 'short';

    public const TYPE_LONG = 'long';

    protected $fillable = [
        'assessment_id',
        'question_type',
        'statement',
        'options',
        'correct_answer',
        'marks',
        'sort_order',
        'chapter_reference',
        'question_hash',
        'embedding',
    ];

    protected $casts = [
        'options' => 'array',
        'embedding' => 'array',
        'marks' => 'integer',
        'sort_order' => 'integer',
    ];

    // ── Boot: Auto-compute question hash ─────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (self $question) {
            $question->question_hash = $question->computeHash();
        });

        static::updating(function (self $question) {
            if ($question->isDirty('statement')) {
                $question->question_hash = $question->computeHash();
            }
        });
    }

    /**
     * Compute SHA-256 hash of the normalized question text.
     */
    public function computeHash(): string
    {
        $normalized = mb_strtolower(trim(preg_replace('/\s+/', ' ', $this->statement ?? '')));

        return hash('sha256', $normalized);
    }

    /**
     * Check if a question with the same text already exists for an assessment.
     */
    public static function isDuplicate(int $assessmentId, string $questionText): bool
    {
        $normalized = mb_strtolower(trim(preg_replace('/\s+/', ' ', $questionText)));
        $hash = hash('sha256', $normalized);

        return static::where('assessment_id', $assessmentId)
            ->where('question_hash', $hash)
            ->exists();
    }

    /**
     * Check if a question hash exists anywhere in the same subject's assessments.
     */
    public static function isDuplicateInSubject(int $subjectId, string $questionText): bool
    {
        $normalized = mb_strtolower(trim(preg_replace('/\s+/', ' ', $questionText)));
        $hash = hash('sha256', $normalized);

        return static::whereHas('assessment', function ($q) use ($subjectId) {
            $q->where('subject_id', $subjectId);
        })->where('question_hash', $hash)->exists();
    }

    // ── Relationships ────────────────────────────────────────────────────────

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function studentAnswers(): HasMany
    {
        return $this->hasMany(StudentAssessmentAnswer::class);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function isMcq(): bool
    {
        return $this->question_type === self::TYPE_MCQ;
    }

    public function isShort(): bool
    {
        return $this->question_type === self::TYPE_SHORT;
    }

    public function isLong(): bool
    {
        return $this->question_type === self::TYPE_LONG;
    }

    /**
     * Check if a provided answer matches the correct answer (for MCQs).
     */
    public function isCorrectAnswer(string $answer): bool
    {
        if (! $this->isMcq()) {
            return false;
        }

        return strtolower(trim($answer)) === strtolower(trim($this->correct_answer ?? ''));
    }
}
