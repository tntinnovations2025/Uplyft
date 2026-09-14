<?php

namespace App\Models;

use App\Models\Concerns\TenantIsolated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassBreak extends Model
{
    use HasFactory, TenantIsolated;

    protected static function applyTenantIsolation(Builder $builder, array $campusIds): void
    {
        $builder->whereIn('institute_id', $campusIds);
    }

    protected $fillable = [
        'institute_id',
        'academic_term_id',
        'class_section_id',
        'day_of_week',
        'break_start_time',
        'break_end_time',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function academicTerm(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class, 'class_section_id');
    }

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class, 'class_section_id');
    }

    public function getFormattedTimeRangeAttribute(): string
    {
        $start = date('g:i A', strtotime($this->break_start_time));
        $end = date('g:i A', strtotime($this->break_end_time));
        return "{$start} – {$end}";
    }
}
