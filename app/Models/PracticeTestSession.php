<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\TenantIsolated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PracticeTestSession extends Model
{
    use HasFactory, TenantIsolated;

    protected static function applyTenantIsolation(Builder $builder, array $campusIds): void
    {
        $builder->whereHas('subject.instituteClass', fn ($q) => $q->whereIn('institute_id', $campusIds));
    }

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    protected $fillable = [
        'id',
        'user_id',
        'subject_id',
        'title',
        'mcq_count',
        'short_count',
        'long_count',
        'total_marks',
        'obtained_marks',
        'questions',
        'student_answers',
        'evaluation_results',
        'time_limit_minutes',
        'scheduled_start_at',
        'scheduled_end_at',
        'submitted_at',
        'status',
        'ai_feedback',
    ];

    protected $casts = [
        'questions' => 'array',
        'student_answers' => 'array',
        'evaluation_results' => 'array',
        'scheduled_start_at' => 'datetime',
        'scheduled_end_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function practiceAttempt()
    {
        return $this->hasOne(StudentPracticeAttempt::class, 'practice_test_session_id');
    }
}
