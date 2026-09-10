<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstituteClassAssignment extends Model
{
    public $timestamps = false; // pivot-style, assigned_at handles timing

    protected $fillable = [
        'institute_id',
        'system_class_id',
        'is_active',
        'assigned_at',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'assigned_at' => 'datetime',
    ];

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function systemClass(): BelongsTo
    {
        return $this->belongsTo(SystemClass::class);
    }
}
