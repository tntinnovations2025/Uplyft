<?php

namespace App\Models;

use App\Models\Scopes\InstituteScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'institute_id',
        'teacher_id',
        'type',
        'title',
        'description_message',
        'file_attachment_path',
        'deadline',
        'is_ai_graded'
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'is_ai_graded' => 'boolean',
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

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
