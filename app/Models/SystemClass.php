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
        'is_active',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'sort_order'  => 'integer',
    ];

    // ── Human-readable education type labels ─────────────────────────────
    public static array $educationTypeLabels = [
        'matric'       => 'Matric (Play Group – Grade 10)',
        'higher_sec'   => 'Higher Secondary (1st & 2nd Year)',
        'o_a_level'    => 'O / A Level',
        'professional' => 'Professional (ACCA, CA, CMA…)',
        'other'        => 'Other',
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
