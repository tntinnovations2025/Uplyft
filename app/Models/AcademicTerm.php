<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class AcademicTerm extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'institute_id',
        'name',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_active'  => 'boolean',
    ];

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function timetables(): HasMany
    {
        return $this->hasMany(Timetable::class);
    }

    public function teacherSubjectSections(): HasMany
    {
        return $this->hasMany(TeacherSubjectSection::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForInstitute($query, int $instituteId)
    {
        return $query->where('institute_id', $instituteId);
    }

    // ── Business Logic: Single Active Term Guarantee ──────────────────────
    /**
     * Mark this academic term as active, deactivating all other terms for this institute.
     */
    public function markAsActive(): bool
    {
        return DB::transaction(function () {
            // Deactivate all terms for this institute
            static::where('institute_id', $this->institute_id)
                ->where('id', '!=', $this->id)
                ->update(['is_active' => false]);

            // Activate current term
            return $this->update(['is_active' => true]);
        });
    }
}
