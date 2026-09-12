<?php

namespace App\Models;

use App\Models\Concerns\TenantIsolated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassSubject extends Model
{
    use HasFactory, TenantIsolated;

    protected $table = 'class_subjects';

    protected $fillable = [
        'institute_id',
        'class_id',
        'subject_id',
        'subject_type',
        'credit_hours',
        'weightage',
    ];

    protected $casts = [
        'credit_hours' => 'integer',
        'weightage' => 'decimal:2',
    ];

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function instituteClass(): BelongsTo
    {
        return $this->belongsTo(InstituteClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function scopeCompulsory(Builder $query): Builder
    {
        return $query->where('subject_type', 'compulsory');
    }

    public function scopeElective(Builder $query): Builder
    {
        return $query->where('subject_type', 'elective');
    }
}
