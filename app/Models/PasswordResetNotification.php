<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\TenantIsolated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasswordResetNotification extends Model
{
    use HasFactory, TenantIsolated;

    // ── Status Constants ─────────────────────────────────────────────────────
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_DENIED = 'denied';

    // ── Target Role Constants ────────────────────────────────────────────────
    public const TARGET_PRINCIPAL = 'principal';

    public const TARGET_GLOBAL_ADMIN = 'global_admin';

    protected $fillable = [
        'user_id',
        'institute_id',
        'status',
        'target_role',
        'processed_by',
        'processed_at',
        'notes',
        'otp',
        'otp_expires_at',
        'cancellation_token',
    ];

    protected function casts(): array
    {
        return [
            'processed_at' => 'datetime',
            'otp_expires_at' => 'datetime',
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

    // ── Status & OTP Helpers ──────────────────────────────────────────────────

    /**
     * Scope query to pending requests only.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

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
     * Check if the provided OTP matches and is not expired.
     */
    public function isOtpValid(string $inputOtp): bool
    {
        if (empty($this->otp) || $this->otp !== trim($inputOtp)) {
            return false;
        }

        if ($this->otp_expires_at && now()->greaterThan($this->otp_expires_at)) {
            return false;
        }

        return true;
    }

    /**
     * Cancel this password reset request (e.g. by user via email security link).
     */
    public function cancel(?string $notes = null): self
    {
        $this->update([
            'status' => self::STATUS_DENIED,
            'notes' => $notes ?? 'Cancelled by account owner (security precaution)',
        ]);

        return $this;
    }

    /**
     * Mark the request as approved and set a temporary password.
     */
    public function markApproved(int $adminId): self
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'processed_by' => $adminId,
            'processed_at' => now(),
        ]);

        return $this;
    }

    /**
     * Mark the request as completed (password has been reset).
     */
    public function markCompleted(?int $adminId = null): self
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'processed_by' => $adminId,
            'processed_at' => now(),
        ]);

        return $this;
    }

    /**
     * Mark the request as denied.
     */
    public function markDenied(?int $adminId = null, ?string $notes = null): self
    {
        $this->update([
            'status' => self::STATUS_DENIED,
            'processed_by' => $adminId,
            'processed_at' => now(),
            'notes' => $notes,
        ]);

        return $this;
    }
}
