<?php

namespace App\Models;

use App\Models\Scopes\InstituteScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Attendance extends Model
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
        'student_id',
        'date',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date:Y-m-d',
        'academic_term_id' => 'integer',
        'student_id' => 'integer',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        // Apply Global Scope for multi-tenancy isolation
        static::addGlobalScope(new InstituteScope);

        // Auto-assign institute_id when creating a new attendance record
        static::creating(function ($attendance) {
            if (empty($attendance->institute_id)) {
                if (Auth::check() && isset(Auth::user()->institute_id)) {
                    $attendance->institute_id = Auth::user()->institute_id;
                } elseif (app()->bound('current_institute_id')) {
                    $attendance->institute_id = app('current_institute_id');
                }
            }
        });
    }

    /**
     * Get the Institute that this attendance log belongs to.
     */
    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    /**
     * Get the Student for this attendance record.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
