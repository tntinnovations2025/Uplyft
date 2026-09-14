<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentSubmission extends Model
{
    use HasFactory;

    protected $table = 'assessment_submissions';

    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_AUTO_SUBMITTED = 'auto_submitted';

    protected $fillable = [
        'assessment_id',
        'student_id',
        'started_at',
        'submitted_at',
        'answers',
        'total_score',
        'status',
    ];

    protected $casts = [
        'answers' => 'array',
        'total_score' => 'float',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function isCompleted(): bool
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_AUTO_SUBMITTED], true);
    }

    public function isAutoSubmitted(): bool
    {
        return $this->status === self::STATUS_AUTO_SUBMITTED;
    }
}
