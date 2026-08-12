<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'institute_class_id',
        'section_name',
        'room_id',
        'room_number',
        'capacity',
        'enrolled_students',
    ];

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
}
