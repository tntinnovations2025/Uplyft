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
        'file_path',
        'file_name',
        'file_size',
        'file_type',
        'assigned_date',
        'due_date',
        'reminder_morning',
        'reminder_sent_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'due_date' => 'date',
        'reminder_morning' => 'boolean',
        'reminder_sent_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function hasAttachment(): bool
    {
        return !empty($this->file_path);
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        if (!$this->hasAttachment()) {
            return null;
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->file_path);
    }

    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) {
            return '';
        }

        if ($this->file_size >= 1048576) {
            return round($this->file_size / 1048576, 1) . ' MB';
        }

        return round($this->file_size / 1024, 0) . ' KB';
    }

    public function isTest(): bool
    {
        return in_array(strtolower($this->entry_type), ['test', 'test_alert', 'quiz']);
    }

    public function isHomework(): bool
    {
        return in_array(strtolower($this->entry_type), ['homework', 'assignment']);
    }

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
