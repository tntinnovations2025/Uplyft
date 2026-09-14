<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\TenantIsolated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory, TenantIsolated;

    protected static function applyTenantIsolation(Builder $builder, array $campusIds): void
    {
        $builder->whereHas('instituteClass', fn ($q) => $q->whereIn('institute_id', $campusIds));
    }

    protected $fillable = [
        'institute_class_id',
        'subject_name',
        'subject_code',
        'credit_hours',
        'lecture_duration_minutes',
        'room_id',
        'total_marks',
        'passing_marks',
        'mcq_weightage',
        'short_answer_weightage',
        'long_answer_weightage',
        'assignment_quiz_weightage',
        'show_marks_to_student',
        'show_grade_to_student',
        'grade_scale_json',
    ];

    protected $casts = [
        'credit_hours' => 'integer',
        'lecture_duration_minutes' => 'integer',
        'total_marks' => 'integer',
        'passing_marks' => 'integer',
        'mcq_weightage' => 'decimal:2',
        'short_answer_weightage' => 'decimal:2',
        'long_answer_weightage' => 'decimal:2',
        'assignment_quiz_weightage' => 'decimal:2',
        'show_marks_to_student' => 'boolean',
        'show_grade_to_student' => 'boolean',
        'grade_scale_json' => 'array',
    ];

    public function getFormattedLectureDurationAttribute(): string
    {
        $mins = (int) ($this->lecture_duration_minutes ?: 60);
        $hours = floor($mins / 60);
        $remMins = $mins % 60;

        if ($hours > 0 && $remMins > 0) {
            return "{$hours}h {$remMins}m ({$mins} mins)";
        } elseif ($hours > 0) {
            return "{$hours} " . ($hours == 1 ? 'Hour' : 'Hours') . " ({$mins} mins)";
        } else {
            return "{$mins} Mins";
        }
    }

    public function instituteClass(): BelongsTo
    {
        return $this->belongsTo(InstituteClass::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function timetables(): HasMany
    {
        return $this->hasMany(Timetable::class);
    }

    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(TeacherSubjectSection::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(SubjectMaterial::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function classSubjects(): HasMany
    {
        return $this->hasMany(ClassSubject::class, 'subject_id');
    }

    public function academicTracks(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(AcademicTrack::class, 'academic_track_subjects', 'subject_id', 'academic_track_id')
            ->withTimestamps();
    }

    public function studentEnrollments(): HasMany
    {
        return $this->hasMany(StudentSubjectEnrollment::class, 'subject_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(ClassSection::class, 'institute_class_id', 'institute_class_id');
    }
}
