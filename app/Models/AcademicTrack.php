<?php

namespace App\Models;

use App\Models\Concerns\TenantIsolated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicTrack extends Model
{
    use HasFactory, TenantIsolated;

    protected $table = 'academic_tracks';

    protected $fillable = [
        'institute_id',
        'class_id',
        'track_name',
        'track_code',
        'description',
        'allow_custom_electives',
        'is_active',
    ];

    protected $casts = [
        'allow_custom_electives' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function instituteClass(): BelongsTo
    {
        return $this->belongsTo(InstituteClass::class, 'class_id');
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'academic_track_subjects', 'academic_track_id', 'subject_id')
            ->withTimestamps();
    }

    public function studentEnrollments(): HasMany
    {
        return $this->hasMany(StudentSubjectEnrollment::class, 'academic_track_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'academic_track_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
