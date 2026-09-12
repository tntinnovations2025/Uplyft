<?php

namespace App\Models;

use App\Models\Concerns\TenantIsolated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StudentSubjectEnrollment extends Model
{
    use HasFactory, TenantIsolated;

    protected $table = 'student_subject_enrollments';

    protected $fillable = [
        'institute_id',
        'student_id',
        'class_section_id',
        'subject_id',
        'academic_track_id',
        'enrollment_status',
    ];

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    /**
     * The User account corresponding to student_id.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Resolve the Student profile associated with the student user_id.
     */
    public function studentProfile(): HasOne
    {
        return $this->hasOne(Student::class, 'user_id', 'student_id');
    }

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class, 'class_section_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function academicTrack(): BelongsTo
    {
        return $this->belongsTo(AcademicTrack::class, 'academic_track_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('enrollment_status', 'active');
    }

    public function scopeDropped(Builder $query): Builder
    {
        return $query->where('enrollment_status', 'dropped');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('enrollment_status', 'completed');
    }
}
