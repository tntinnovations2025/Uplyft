<?php

namespace App\Models;

use App\Models\Scopes\InstituteScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Student extends Model
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
        'roll_number',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'previous_marks',
        'guardian_tax_status',
        'blood_group',
        'passport_picture_path',
        'student_bform_cnic',
        'father_guardian_cnic',
        'father_guardian_name',
        'address',
        'enrolled_program',
        'base_fee',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'previous_marks' => 'decimal:2',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        // Apply Global Scope for multi-tenancy
        static::addGlobalScope(new InstituteScope);

        // Auto-assign institute_id when creating a new student record
        static::creating(function ($student) {
            if (empty($student->institute_id)) {
                if (Auth::check() && isset(Auth::user()->institute_id)) {
                    $student->institute_id = Auth::user()->institute_id;
                } elseif (app()->bound('current_institute_id')) {
                    $student->institute_id = app('current_institute_id');
                }
            }
        });
    }

    /**
     * Get the Institute that this student belongs to.
     */
    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function invoices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the student's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
