<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasswordResetNotification extends Model
{
    use HasFactory;

    // ── Status Constants ─────────────────────────────────────────────────────
    public const STATUS_PENDING   = 'pending';
    public const STATUS_APPROVED  = 'approved';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_DENIED    = 'denied';

    // ── Target Role Constants ────────────────────────────────────────────────
    public const TARGET_PRINCIPAL    = 'principal';
    public const TARGET_GLOBAL_ADMIN = 'global_admin';

    protected $fillable = [
        'user_id',
        'institute_id',
        'status',
        'target_role',
        'processed_by',
        'processed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'processed_at' => 'datetime',
        ];
    }

    // ── Relationships ────────────────────────────────────────────────────────

    /**
     * The user who requested the password reset.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The institute context of this request.
     */
    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    /**
     * The admin who processed this request.
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // ── Status Helpers ───────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isDenied(): bool
    {
        return $this->status === self::STATUS_DENIED;
    }

    /**
     * Mark the request as approved and set a temporary password.
     */
    public function markApproved(int $adminId): self
    {
        $this->update([
            'status'       => self::STATUS_APPROVED,
            'processed_by' => $adminId,
            'processed_at' => now(),
        ]);

        return $this;
    }

    /**
     * Mark the request as completed (password has been reset).
     */
    public function markCompleted(int $adminId): self
    {
        $this->update([
            'status'       => self::STATUS_COMPLETED,
            'processed_by' => $adminId,
            'processed_at' => now(),
        ]);

        return $this;
    }

    /**
     * Mark the request as denied.
     */
    public function markDenied(int $adminId, ?string $notes = null): self
    {
        $this->update([
            'status'       => self::STATUS_DENIED,
            'processed_by' => $adminId,
            'processed_at' => now(),
            'notes'        => $notes,
        ]);

        return $this;
    }
}
