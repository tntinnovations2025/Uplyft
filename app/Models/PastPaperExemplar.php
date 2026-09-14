<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PastPaperExemplar extends Model
{
    use HasFactory;

    protected $fillable = [
        'past_paper_upload_id',
        'subject_id',
        'topic_tag',
        'question_stem',
        'options',
        'correct_answer',
        'explanation',
        'stem_hash',
        'embedding',
    ];

    protected $casts = [
        'options' => 'array',
        'embedding' => 'array',
    ];

    public function upload(): BelongsTo
    {
        return $this->belongsTo(PastPaperUpload::class, 'past_paper_upload_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
