<?php

namespace App\Models;

use App\Models\Concerns\TenantIsolated;
use Carbon\Carbon;
use Database\Factories\DailyDiaryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyDiary extends Model
{
    use HasFactory, TenantIsolated;

    protected $table = 'daily_diaries';

    protected $fillable = [
        'institute_id',
        'class_section_id',
        'subject_id',
        'teacher_id',
        'entry_type',
        'title',
        'content',
        'assigned_date',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected static function applyTenantIsolation(Builder $builder, array $campusIds): void
    {
        $builder->whereIn('daily_diaries.institute_id', $campusIds);
    }

    protected static function booted(): void
    {
        static::creating(function (DailyDiary $diary) {
            if (empty($diary->assigned_date)) {
                $diary->assigned_date = now()->toDateString();
            }

            // Always enforce expires_at is exactly assigned_date + 14 days
            if (empty($diary->expires_at)) {
                $diary->expires_at = Carbon::parse($diary->assigned_date)->addDays(14);
            }
        });

        static::updating(function (DailyDiary $diary) {
            if ($diary->isDirty('assigned_date') && ! $diary->isDirty('expires_at')) {
                $diary->expires_at = Carbon::parse($diary->assigned_date)->addDays(14);
            }
        });
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    protected static function newFactory()
    {
        return DailyDiaryFactory::new();
    }
}
