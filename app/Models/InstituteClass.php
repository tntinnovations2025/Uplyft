<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\TenantIsolated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstituteClass extends Model
{
    use HasFactory, TenantIsolated;

    protected $fillable = [
        'institute_id',
        'academic_term_id',
        'system_class_id',
        'custom_name',
    ];

    public function academicTerm(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    public function getNameAttribute(): string
    {
        return $this->custom_name ?? ($this->systemClass->name ?? "Class {$this->id}");
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function systemClass(): BelongsTo
    {
        return $this->belongsTo(SystemClass::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(ClassSection::class);
    }

    public function students()
    {
        return $this->hasManyThrough(Student::class, ClassSection::class, 'institute_class_id', 'class_section_id');
    }
}
