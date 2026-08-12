<?php

namespace App\Models;

use App\Models\Scopes\TenantPrivacyScope;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    // ── Role Constants ──────────────────────────────────────────────────────
    public const ROLE_GLOBAL_ADMIN = 'global_admin';
    public const ROLE_PRINCIPAL    = 'principal';
    public const ROLE_TEACHER      = 'teacher';
    public const ROLE_STUDENT      = 'student';

    public const ROLES = [
        self::ROLE_GLOBAL_ADMIN,
        self::ROLE_PRINCIPAL,
        self::ROLE_TEACHER,
        self::ROLE_STUDENT,
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'identifier',
        'role',
        'staff_role',
        'employment_type',
        'institute_id',
        'is_delegated_admin',
        'permissions',
        'created_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'  => 'datetime',
            'password'           => 'hashed',
            'is_delegated_admin' => 'boolean',
            'permissions'        => 'array',
        ];
    }

    // ── Relationships ────────────────────────────────────────────────────────

    /**
     * The institute this user belongs to.
     * Null for global_admin users.
     */
    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    /**
     * The admin user who created this account.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Users created by this admin.
     */
    public function createdUsers(): HasMany
    {
        return $this->hasMany(User::class, 'created_by');
    }

    /**
     * Password reset requests filed by this user.
     */
    public function passwordResetNotifications(): HasMany
    {
        return $this->hasMany(PasswordResetNotification::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(TeacherAvailability::class, 'teacher_id');
    }

    // ── Role Helpers ─────────────────────────────────────────────────────────

    public function isGlobalAdmin(): bool
    {
        return $this->role === self::ROLE_GLOBAL_ADMIN;
    }

    public function isPrincipal(): bool
    {
        return $this->role === self::ROLE_PRINCIPAL;
    }

    public function isTeacher(): bool
    {
        return $this->role === self::ROLE_TEACHER;
    }

    public function isStudent(): bool
    {
        return $this->role === self::ROLE_STUDENT;
    }

    /**
     * Check if this teacher has been delegated admin rights.
     */
    public function hasDelegatedAdminRights(): bool
    {
        return $this->isTeacher() && $this->is_delegated_admin;
    }

    /**
     * Check if this user can create accounts (principals can,
     * and teachers with delegation flag can).
     */
    public function canCreateAccounts(): bool
    {
        return $this->isPrincipal() || $this->hasDelegatedAdminRights();
    }

    /**
     * Determine the login credential field based on input.
     * Allows login by either email or custom identifier.
     */
    public static function findForLogin(string $credential): ?self
    {
        return static::where('email', $credential)
            ->orWhere('identifier', $credential)
            ->first();
    }
}
