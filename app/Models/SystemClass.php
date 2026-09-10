<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SystemClass extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'short_code',
        'education_type',
        'sort_order',
        'default_subjects',
        'is_active',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'sort_order'       => 'integer',
        'default_subjects' => 'array',
    ];

    // ── Human-readable education type labels ─────────────────────────────
    public static array $educationTypeLabels = [
        'matric'       => 'Matriculation (Play Group – Grade 10)',
        'higher_sec'   => 'Intermediate / Higher Secondary (1st & 2nd Year)',
        'o_a_level'    => 'Cambridge O / A Level (O1–O3, A1–A2)',
        'acca'         => 'ACCA / Professional Certification',
        'professional' => 'ACCA / Professional Certification',
        'other'        => 'Other / General Education',
    ];

    // Institutes this class has been assigned to
    public function institutes(): BelongsToMany
    {
        return $this->belongsToMany(
            Institute::class,
            'institute_class_assignments',
            'system_class_id',
            'institute_id'
        )->withPivot('is_active', 'assigned_at');
    }
}
