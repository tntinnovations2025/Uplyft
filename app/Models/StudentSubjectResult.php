<?php

namespace App\Models;

use App\Models\Scopes\InstituteScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class StudentSubjectResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'institute_id',
        'student_id',
        'subject_id',
        'academic_term_id',
        'marks_obtained',
        'total_marks',
        'result_status',
        'remarks',
    ];

    protected $casts = [
        'marks_obtained' => 'integer',
        'total_marks' => 'integer',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new InstituteScope);

        static::creating(function ($result) {
            if (empty($result->institute_id)) {
                if (Auth::check() && isset(Auth::user()->institute_id)) {
                    $result->institute_id = Auth::user()->institute_id;
                }
            }
        });
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function academicTerm(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class);
    }
}
