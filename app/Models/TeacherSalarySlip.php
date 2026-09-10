<?php

namespace App\Models;

use App\Models\Scopes\InstituteScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class TeacherSalarySlip extends Model
{
    use HasFactory;

    protected $fillable = [
        'institute_id',
        'teacher_id',
        'title',
        'month_year',
        'amount',
        'file_path',
        'notes',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new InstituteScope);

        static::creating(function ($slip) {
            if (empty($slip->institute_id)) {
                if (Auth::check() && isset(Auth::user()->institute_id)) {
                    $slip->institute_id = Auth::user()->institute_id;
                } elseif (app()->bound('current_institute_id')) {
                    $slip->institute_id = app('current_institute_id');
                }
            }
        });
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
