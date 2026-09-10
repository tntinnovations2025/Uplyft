<?php

namespace App\Models;

use App\Models\Scopes\InstituteScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class ScholarshipCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'institute_id',
        'title',
        'description',
        'discount_percentage',
        'questions',
        'is_active',
    ];

    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'questions' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new InstituteScope);

        static::creating(function ($model) {
            if (empty($model->institute_id)) {
                if (Auth::check() && isset(Auth::user()->institute_id)) {
                    $model->institute_id = Auth::user()->institute_id;
                } elseif (app()->bound('current_institute_id')) {
                    $model->institute_id = app('current_institute_id');
                }
            }
        });
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
