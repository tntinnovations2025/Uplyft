<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAssessmentAnswer extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_AUTO_GRADED = 'auto_graded';

    public const STATUS_AI_GRADED = 'ai_graded';

    public const STATUS_MANUALLY_GRADED = 'manually_graded';

    protected $fillable = [
        'assessment_question_id',
        'student_id',
        'provided_answer',
        'marks_awarded',
        'ai_feedback',
        'grading_status',
        'graded_by',
    ];

    protected $casts = [
        'marks_awarded' => 'decimal:2',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function question(): BelongsTo
    {
        return $this->belongsTo(AssessmentQuestion::class, 'assessment_question_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function isGraded(): bool
    {
        return in_array($this->grading_status, [
            self::STATUS_AUTO_GRADED,
            self::STATUS_AI_GRADED,
            self::STATUS_MANUALLY_GRADED,
        ]);
    }
}
