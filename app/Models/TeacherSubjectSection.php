<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\TenantIsolated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherSubjectSection extends Model
{
    use HasFactory, TenantIsolated;

    protected static function applyTenantIsolation(Builder $builder, array $campusIds): void
    {
        $builder->whereHas('subject.instituteClass', fn ($q) => $q->whereIn('institute_id', $campusIds));
    }

    protected $fillable = [
        'academic_term_id',
        'teacher_id',
        'subject_id',
        'class_section_id',
        'periods_per_week',
        'duration_minutes',
        'allowed_days',
    ];

    protected $casts = [
        'allowed_days' => 'array',
    ];

    public function getAllowedDaysListAttribute(): array
    {
        if (is_array($this->allowed_days) && !empty($this->allowed_days)) {
            return array_map('strtolower', $this->allowed_days);
        }
        return ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
    }

    public function isDayAllowed(string $day): bool
    {
        return in_array(strtolower($day), $this->allowed_days_list, true);
    }

    public function getFormattedAllowedDaysAttribute(): string
    {
        $days = $this->allowed_days_list;
        if (count($days) === 6) {
            return 'All Weekdays';
        }
        $shortMap = ['monday'=>'Mon', 'tuesday'=>'Tue', 'wednesday'=>'Wed', 'thursday'=>'Thu', 'friday'=>'Fri', 'saturday'=>'Sat', 'sunday'=>'Sun'];
        $formatted = array_map(fn($d) => $shortMap[strtolower($d)] ?? ucfirst($d), $days);
        return implode(', ', $formatted);
    }

    public function getDurationHoursAttribute(): int
    {
        return (int) floor(($this->duration_minutes ?: 60) / 60);
    }

    public function getDurationRemainingMinutesAttribute(): int
    {
        return (int) (($this->duration_minutes ?: 60) % 60);
    }

    public function getFormattedDurationAttribute(): string
    {
        $hrs = $this->duration_hours;
        $mins = $this->duration_remaining_minutes;
        if ($hrs > 0 && $mins > 0) {
            return "{$hrs}h {$mins}m";
        } elseif ($hrs > 0) {
            return "{$hrs}h";
        }
        return "{$mins}m";
    }

    public function academicTerm(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class, 'class_section_id');
    }
}
