<?php

namespace App\Models;

use App\Models\Scopes\InstituteScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Teacher extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'institute_id',
        'user_id',
        'employee_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'qualification',
        'matriculation_cert',
        'intermediate_cert',
        'bachelors_cert',
        'masters_cert',
        'phd_cert',
        'years_of_experience',
        'specialization_subjects',
        'emergency_contact_phone',
        'basic_salary_pkr',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        // Apply Global Scope for multi-tenancy isolation
        static::addGlobalScope(new InstituteScope);

        // Auto-assign institute_id when creating a new teacher record
        static::creating(function ($teacher) {
            if (empty($teacher->institute_id)) {
                if (Auth::check() && isset(Auth::user()->institute_id)) {
                    $teacher->institute_id = Auth::user()->institute_id;
                } elseif (app()->bound('current_institute_id')) {
                    $teacher->institute_id = app('current_institute_id');
                }
            }
        });
    }

    /**
     * Get the Institute that this teacher belongs to.
     */
    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the teacher's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
