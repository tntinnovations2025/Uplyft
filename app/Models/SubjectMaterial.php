<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\TenantIsolated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubjectMaterial extends Model
{
    use HasFactory, TenantIsolated;

    protected static function applyTenantIsolation(Builder $builder, array $campusIds): void
    {
        $builder->whereHas('subject.instituteClass', fn ($q) => $q->whereIn('institute_id', $campusIds));
    }

    protected $fillable = [
        'subject_id',
        'uploaded_by',
        'title',
        'file_path',
        'document_type',
        'mime_type',
        'file_size_bytes',
        'is_rag_indexed',
    ];

    protected $casts = [
        'is_rag_indexed' => 'boolean',
        'file_size_bytes' => 'integer',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function ragChunks(): HasMany
    {
        return $this->hasMany(RagDocumentChunk::class);
    }

    // ── Accessors ────────────────────────────────────────────────────────────

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size_bytes ?? 0;
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1).' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 1).' KB';
        }

        return $bytes.' B';
    }
}
