<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\TenantIsolated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    use HasFactory, TenantIsolated;

    protected static function applyTenantIsolation(Builder $builder, array $campusIds): void
    {
        $builder->whereHas('subject.instituteClass', fn ($q) => $q->whereIn('institute_id', $campusIds));
    }

    // ── Constants ────────────────────────────────────────────────────────────

    public const TYPE_MIDTERM = 'midterm';

    public const TYPE_FINAL = 'final';

    public const TYPE_QUIZ = 'quiz';

    public const TYPE_ASSIGNMENT = 'assignment';

    public const TYPE_PROJECT = 'project';

    public const TYPE_HOMEWORK = 'homework';

    public const TYPE_PRESENTATION = 'presentation';

    public const TYPES = [
        self::TYPE_MIDTERM,
        self::TYPE_FINAL,
        self::TYPE_QUIZ,
        self::TYPE_ASSIGNMENT,
        self::TYPE_PROJECT,
        self::TYPE_HOMEWORK,
        self::TYPE_PRESENTATION,
    ];

    public const MANDATORY_TYPES = [
        self::TYPE_MIDTERM,
        self::TYPE_FINAL,
    ];

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_GRADED = 'graded';

    public const EVAL_MANUAL = 'manual';

    public const EVAL_AI = 'ai';

    protected $fillable = [
        'subject_id',
        'academic_term_id',
        'class_section_id',
        'creator_id',
        'title',
        'type',
        'total_marks',
        'weightage_percentage',
        'start_time',
        'end_time',
        'has_time_limit',
        'duration_minutes',
        'result_deadline',
        'evaluation_mode',
        'is_paper_test',
        'status',
        'is_marksheet_saved',
        'saved_at',
        'instructions',
        'room',
        'is_published_teacher',
        'is_published_student',
    ];

    protected $casts = [
        'total_marks' => 'integer',
        'weightage_percentage' => 'float',
        'has_time_limit' => 'boolean',
        'is_paper_test' => 'boolean',
        'is_published_teacher' => 'boolean',
        'is_published_student' => 'boolean',
        'duration_minutes' => 'integer',
        'is_marksheet_saved' => 'boolean',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'result_deadline' => 'datetime',
        'saved_at' => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function academicTerm(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(AssessmentQuestion::class)->orderBy('sort_order');
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeForTerm($query, int $termId)
    {
        return $query->where('academic_term_id', $termId);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function isMandatory(): bool
    {
        return in_array($this->type, self::MANDATORY_TYPES);
    }

    public function isAiEvaluated(): bool
    {
        return $this->evaluation_mode === self::EVAL_AI;
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PUBLISHED]);
    }
}
