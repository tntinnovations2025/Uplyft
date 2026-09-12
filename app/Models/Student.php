<?php

namespace App\Models;

use App\Models\Scopes\InstituteScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'academic_term_id',
        'user_id',
        'roll_number',
        'first_name',
        'last_name',
        'email',
        'phone',
        'guardian_phone',
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
        'class_section_id',
        'academic_track_id',
        'selected_subject_ids',
        'annual_result_status',
        'admission_status',
        'base_fee',
        'admission_fee',
        'security_fee',
        'tax_percentage',
        'scholarship_category_id',
        'scholarship_name',
        'scholarship_percentage',
        'scholarship_reason',
        'scholarship_verification_answers',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'previous_marks' => 'decimal:2',
        'base_fee' => 'decimal:2',
        'admission_fee' => 'decimal:2',
        'security_fee' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'scholarship_percentage' => 'decimal:2',
        'scholarship_verification_answers' => 'array',
        'academic_track_id' => 'integer',
        'selected_subject_ids' => 'array',
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

    public function academicTerm(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class, 'class_section_id');
    }

    public function scholarshipCategory(): BelongsTo
    {
        return $this->belongsTo(ScholarshipCategory::class, 'scholarship_category_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subjectResults(): HasMany
    {
        return $this->hasMany(StudentSubjectResult::class);
    }

    public function academicTrack(): BelongsTo
    {
        return $this->belongsTo(AcademicTrack::class, 'academic_track_id');
    }

    public function subjectEnrollments(): HasMany
    {
        return $this->hasMany(StudentSubjectEnrollment::class, 'student_id', 'user_id');
    }

    /**
     * Get the student's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getStatusAttribute(): string
    {
        return $this->admission_status ?? 'pending_payment';
    }

    public function setStatusAttribute($value): void
    {
        $this->attributes['admission_status'] = $value;
    }
}
