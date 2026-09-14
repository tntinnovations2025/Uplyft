<?php

namespace App\Models;

use App\Models\Concerns\TenantIsolated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PastPaperUpload extends Model
{
    use HasFactory, TenantIsolated;

    protected static function applyTenantIsolation(Builder $builder, array $campusIds): void
    {
        $builder->whereIn('institute_id', $campusIds);
    }

    protected $fillable = [
        'institute_id',
        'subject_id',
        'exam_series',
        'file_path',
        'parsed_status',
        'total_questions_extracted',
    ];

    protected $casts = [
        'total_questions_extracted' => 'integer',
    ];

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function exemplars(): HasMany
    {
        return $this->hasMany(PastPaperExemplar::class, 'past_paper_upload_id');
    }
}
