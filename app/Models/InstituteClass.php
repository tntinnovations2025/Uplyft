<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstituteClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'institute_id',
        'system_class_id',
        'custom_name',
    ];

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
}
