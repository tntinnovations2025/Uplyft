<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\TenantIsolated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatHistory extends Model
{
    use HasFactory, TenantIsolated;

    protected static function applyTenantIsolation(Builder $builder, array $campusIds): void
    {
        $builder->whereHas('subject.instituteClass', fn ($q) => $q->whereIn('institute_id', $campusIds));
    }

    protected $fillable = [
        'user_id',
        'subject_id',
        'session_id',
        'role',
        'message',
        'sources',
        'confidence',
    ];

    protected $casts = [
        'sources' => 'array',
        'confidence' => 'float',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeForSession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeForUserSubject($query, int $userId, int $subjectId)
    {
        return $query->where('user_id', $userId)->where('subject_id', $subjectId);
    }
}
