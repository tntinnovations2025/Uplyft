<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\TenantIsolated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassSection extends Model
{
    use HasFactory, TenantIsolated;

    protected static function applyTenantIsolation(Builder $builder, array $campusIds): void
    {
        $builder->whereHas('instituteClass', fn ($q) => $q->whereIn('institute_id', $campusIds));
    }

    protected $fillable = [
        'institute_class_id',
        'section_name',
        'room_id',
        'room_number',
        'capacity',
        'enrolled_students',
        'class_incharge_id',
    ];

    public function instituteClass(): BelongsTo
    {
        return $this->belongsTo(InstituteClass::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function classIncharge(): BelongsTo
    {
        return $this->belongsTo(User::class, 'class_incharge_id');
    }

    public function timetables(): HasMany
    {
        return $this->hasMany(Timetable::class);
    }

    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(TeacherSubjectSection::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'class_section_id');
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class, 'institute_class_id', 'institute_class_id');
    }

    public function studentSubjectEnrollments(): HasMany
    {
        return $this->hasMany(StudentSubjectEnrollment::class, 'class_section_id');
    }
}
