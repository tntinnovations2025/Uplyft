<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'institute_class_id',
        'subject_name',
        'subject_code',
        'credit_hours',
    ];

    public function instituteClass(): BelongsTo
    {
        return $this->belongsTo(InstituteClass::class);
    }

    public function timetables(): HasMany
    {
        return $this->hasMany(Timetable::class);
    }

    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(TeacherSubjectSection::class);
    }
}
