<?php

namespace App\Models;

use App\Models\Scopes\InstituteScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'institute_id',
        'academic_term_id',
        'student_id',
        'title',
        'fee_month',
        'class_section_id',
        'amount_pkr',
        'admission_fee',
        'security_fee',
        'due_date',
        'status',
        'pdf_path',
        'paid_slip_path',
    ];

    protected $casts = [
        'due_date' => 'date',
        'amount_pkr' => 'decimal:2',
        'admission_fee' => 'decimal:2',
        'security_fee' => 'decimal:2',
    ];

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

    public function academicTerm(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class, 'class_section_id');
    }
}
