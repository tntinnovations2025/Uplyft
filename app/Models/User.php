<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    // ── Role Constants ──────────────────────────────────────────────────────
    public const ROLE_GLOBAL_ADMIN = 'global_admin';

    public const ROLE_PRINCIPAL = 'principal';

    public const ROLE_TEACHER = 'teacher';

    public const ROLE_STUDENT = 'student';

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
        'is_primary_principal',
        'organization_id',
        'current_institute_id',
        'permissions',
        'created_by',
        'basic_salary_pkr',
        'allowed_absent_days_per_month',
        'salary_disbursement_day',
        'salary_deduction_type',
        'fixed_absent_deduction_amount',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // ── Campus-scope cache (per request instance) ────────────────────────────
    private ?array $_campusIdsCache = null;

    private bool $_campusIdsLoaded = false;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_delegated_admin' => 'boolean',
            'is_primary_principal' => 'boolean',
            'permissions' => 'array',
            'basic_salary_pkr' => 'float',
            'allowed_absent_days_per_month' => 'integer',
            'salary_disbursement_day' => 'integer',
            'fixed_absent_deduction_amount' => 'float',
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
     * The organization this user / owner belongs to.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function subjectEnrollments(): HasMany
    {
        return $this->hasMany(StudentSubjectEnrollment::class, 'student_id');
    }

    public function enrolledSubjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'student_subject_enrollments', 'student_id', 'subject_id')
            ->withPivot(['class_section_id', 'academic_track_id', 'enrollment_status'])
            ->withTimestamps();
    }

    /**
     * Active switched institute for multi-institute organization context.
     */
    public function currentInstitute(): BelongsTo
    {
        return $this->belongsTo(Institute::class, 'current_institute_id');
    }

    /**
     * Get the active institute ID for tenant scoping (respects active session switch).
     */
    public function getActiveInstituteId(): ?int
    {
        if (session()->has('active_institute_id')) {
            return (int) session('active_institute_id');
        }

        return !empty($this->attributes['current_institute_id']) 
            ? (int) $this->attributes['current_institute_id'] 
            : (!empty($this->attributes['institute_id']) ? (int) $this->attributes['institute_id'] : null);
    }

    /**
     * Dynamic Accessor for institute_id:
     * Evaluates active switched campus ID when in multi-tenant session context.
     */
    public function getInstituteIdAttribute($value): ?int
    {
        if (session()->has('active_institute_id')) {
            return (int) session('active_institute_id');
        }

        if (!empty($this->attributes['current_institute_id'])) {
            return (int) $this->attributes['current_institute_id'];
        }

        return !empty($value) ? (int) $value : null;
    }

    /**
     * Raw home institute ID (campus where account was registered).
     */
    public function getHomeInstituteId(): ?int
    {
        return !empty($this->attributes['institute_id']) ? (int) $this->attributes['institute_id'] : null;
    }

    /**
     * Authorized campus (institute) IDs this user's tenant scope may span.
     *
     * - Platform Global Admins: null (no constraint / cross-platform).
     * - Principals whose home campus belongs to an Organization: every campus
     *   (institute) registered under that organization.
     * - Principals of a standalone campus (organization_id = null): their own campus only.
     * - Teachers, Staff, Students (any non-principal): strictly their own home
     *   campus regardless of organization membership.
     *
     * @return int[]|null  null = no scope constraint.
     */
    public function authorizedCampusIds(): ?array
    {
        if ($this->_campusIdsLoaded) {
            return $this->_campusIdsCache;
        }

        $this->_campusIdsLoaded = true;
        $this->_campusIdsCache = null;

        if ($this->isGlobalAdmin()) {
            return $this->_campusIdsCache;
        }

        $homeInstituteId = $this->getHomeInstituteId();

        if ($this->isPrincipal() && $homeInstituteId) {
            $orgId = $this->organization_id
                ?: Institute::withoutGlobalScopes()
                    ->whereKey($homeInstituteId)
                    ->value('organization_id');

            if ($orgId) {
                $campusIds = Institute::withoutGlobalScopes()
                    ->where('organization_id', $orgId)
                    ->whereNull('deleted_at')
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->all();

                if (count($campusIds) > 0) {
                    $this->_campusIdsCache = $campusIds;
                    return $this->_campusIdsCache;
                }
            }
        }

        $this->_campusIdsCache = $homeInstituteId !== null ? [(int) $homeInstituteId] : null;

        return $this->_campusIdsCache;
    }

    /**
     * Whether this user may access a given institute/campus — used as the
     * cross-campus route-model-binding boundary for User targets.
     */
    public function canAccessInstitute(?int $instituteId): bool
    {
        if ($instituteId === null) {
            return false;
        }

        $campusIds = $this->authorizedCampusIds();

        return $campusIds === null || in_array($instituteId, $campusIds, true);
    }

    /**
     * Get user's first name for personal greetings across all portals.
     */
    public function getFirstNameAttribute(): string
    {
        $name = trim($this->attributes['name'] ?? $this->name ?? '');
        if (!empty($name)) {
            // Strip any titles like Mr., Mrs., Dr., Prof., Accountant, etc. if present
            $cleaned = preg_replace('/^(Mr\.?|Mrs\.?|Ms\.?|Dr\.?|Prof\.?|Accountant|Teacher|Principal)\s+/i', '', $name);
            $parts = preg_split('/\s+/', trim($cleaned));
            return $parts[0] ?? $name;
        }
        return 'User';
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

    /**
     * Override Laravel's default password-reset email with our branded notification.
     *
     * Called automatically by the Password Broker when `Password::sendResetLink()`
     * generates a token. The broker hashes and stores the token in the
     * `password_reset_tokens` table, then invokes this method on the User.
     *
     * The token is valid for the minutes defined in config/auth.php → passwords.users.expire
     * (default: 60 min). After use or expiry the row is automatically purged
     * by Laravel's built-in broker on the next `Password::reset()` call.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(TeacherAvailability::class, 'teacher_id');
    }

    public function studentProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Student::class, 'user_id');
    }

    public function teacherProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Teacher::class, 'user_id');
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

    public function isPrimaryPrincipal(): bool
    {
        return (bool) $this->is_primary_principal;
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
     * Check if user is Principal or Administration (delegated admin rights).
     */
    public function isAdministration(): bool
    {
        if ($this->isStudent()) {
            return false;
        }

        return $this->isPrincipal()
            || $this->isGlobalAdmin()
            || $this->hasDelegatedAdminRights();
    }

    /**
     * Get effective permissions by combining role default authorities and user-specific overrides.
     * Guarantees that users (Accountants, Teachers, Coordinators, Admins) immediately reflect their role
     * and assigned rights across the entire portal without stale defaults or missing rights.
     */
    public function getEffectivePermissions(): array
    {
        if ($this->isPrincipal() || $this->isGlobalAdmin()) {
            return [
                'directory' => true, 'directory_view' => true, 'directory_edit' => true,
                'student_registration' => true,
                'students' => true, 'students_view' => true, 'students_edit' => true,
                'invoices' => true, 'invoices_view' => true, 'invoices_edit' => true,
                'staff_onboard' => true, 'staff' => true, 'staff_view' => true, 'staff_edit' => true,
                'academics' => true, 'academics_view' => true, 'academics_edit' => true,
                'rooms' => true, 'rooms_view' => true, 'rooms_edit' => true,
                'classes' => true, 'classes_view' => true, 'classes_edit' => true,
                'subjects' => true, 'subjects_view' => true, 'subjects_edit' => true,
                'faculty_hours' => true, 'faculty_hours_view' => true, 'faculty_hours_edit' => true,
                'allocations' => true, 'allocations_view' => true, 'allocations_edit' => true,
                'timetables' => true, 'timetables_view' => true, 'timetables_edit' => true,
                'security' => true, 'security_view' => true, 'security_edit' => true,
                'accounts' => true, 'accounts_view' => true, 'accounts_edit' => true,
                'fees' => true, 'fees_view' => true, 'fees_edit' => true,
                'salaries' => true, 'salaries_view' => true, 'salaries_edit' => true,
                'staff_salaries' => true,
                'expenses' => true, 'expenses_view' => true, 'expenses_edit' => true,
                'attendance' => true, 'attendance_view' => true, 'attendance_edit' => true,
                'scholarships' => true, 'scholarships_view' => true, 'scholarships_edit' => true,
                'assessment_engine' => true, 'lms_content' => true, 'ai_bot' => true, 'grading_normalizer' => true,
            ];
        }

        if ($this->isStudent()) {
            return [
                'ai_bot' => true,
                'practice_tests' => true,
                'lms_content' => true,
                'assessment_engine' => true,
                'academics' => true,
                'timetable' => true,
                'attendance' => true,
                'fees' => true,
                'student_portal' => true,
            ];
        }

        $basePermissions = [];
        if (!empty($this->institute_id)) {
            $staffRole = $this->staff_role;
            $defaultAuth = null;

            if (!empty($staffRole)) {
                $staffRoleSlug = \Illuminate\Support\Str::slug($staffRole, '_');
                $defaultAuth = RoleDefaultAuthority::where('institute_id', $this->institute_id)
                    ->where(function ($q) use ($staffRole, $staffRoleSlug) {
                        $q->where('role_name', $staffRole)
                          ->orWhere('role_slug', $staffRoleSlug)
                          ->orWhere('role_slug', strtolower($staffRole));
                    })->first();
            }

            if (!$defaultAuth && (empty($staffRole) || strtolower($staffRole) === 'teacher')) {
                $defaultAuth = RoleDefaultAuthority::where('institute_id', $this->institute_id)
                    ->where('role_slug', 'teacher')
                    ->first();
            }

            // Seed role defaults on the fly if this institute lacks records
            if (!$defaultAuth) {
                if (RoleDefaultAuthority::where('institute_id', $this->institute_id)->count() === 0) {
                    RoleDefaultAuthority::seedDefaultsForInstitute($this->institute_id);
                    $defaultAuth = RoleDefaultAuthority::where('institute_id', $this->institute_id)
                        ->where('role_slug', !empty($staffRole) ? \Illuminate\Support\Str::slug($staffRole, '_') : 'teacher')
                        ->first();
                }
            }

            if ($defaultAuth && !empty($defaultAuth->permissions)) {
                $basePermissions = $defaultAuth->permissions;
            }
        }

        $userOverrides = $this->permissions ?? [];

        return array_merge($basePermissions, $userOverrides);
    }

    /**
     * Check if teacher/staff user has any active delegated permission enabled.
     */
    public function hasAnyDelegatedPermission(): bool
    {
        if ($this->isStudent()) {
            return false;
        }

        if ($this->isPrincipal() || $this->is_delegated_admin) {
            return true;
        }

        if ($this->staff_role && in_array(strtolower($this->staff_role), ['administration', 'coordinator', 'accountant'])) {
            return true;
        }

        $effective = $this->getEffectivePermissions();
        foreach ($effective as $key => $val) {
            if (!empty($val)) {
                // If this permission is non-standard for basic teacher, flag as having delegated rights
                if (!in_array($key, ['ai_bot', 'lms_content', 'lms_notes_upload'])) {
                    return true;
                }
            }
        }

        return false;
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
     * Check if user has specific granular permission right (View Only or Edit/Manage).
     */
    public function hasPermission(string $permissionKey, string $mode = 'view'): bool
    {
        if ($this->isPrincipal() || $this->isGlobalAdmin()) {
            return true;
        }

        if ($this->isStudent()) {
            if ($mode === 'view') {
                return in_array($permissionKey, [
                    'ai_bot',
                    'practice_tests',
                    'lms_content',
                    'assessment_engine',
                    'academics',
                    'timetable',
                    'attendance',
                    'fees',
                    'student_portal',
                ]);
            }
            return false;
        }

        // Normalize permission key & mode if suffix supplied in key
        if (str_ends_with($permissionKey, '_view')) {
            $permissionKey = substr($permissionKey, 0, -5);
            $mode = 'view';
        } elseif (str_ends_with($permissionKey, '_edit')) {
            $permissionKey = substr($permissionKey, 0, -5);
            $mode = 'edit';
        }

        $userOverrides = $this->permissions ?? [];

        // 1. Explicit Revocation Check in user overrides (Overrides Master Delegated Admin)
        if ($this->is_delegated_admin) {
            if ($mode === 'edit') {
                if (array_key_exists($permissionKey . '_edit', $userOverrides) && empty($userOverrides[$permissionKey . '_edit'])) {
                    return false;
                }
                if (array_key_exists($permissionKey, $userOverrides) && ($userOverrides[$permissionKey] === 'view' || empty($userOverrides[$permissionKey]))) {
                    return false;
                }
            }
            if ($mode === 'view') {
                if (array_key_exists($permissionKey . '_view', $userOverrides) && empty($userOverrides[$permissionKey . '_view']) && array_key_exists($permissionKey . '_edit', $userOverrides) && empty($userOverrides[$permissionKey . '_edit'])) {
                    return false;
                }
                if (array_key_exists($permissionKey, $userOverrides) && empty($userOverrides[$permissionKey])) {
                    return false;
                }
            }
            return true;
        }

        // 2. Granular Evaluation via merged Effective Permissions
        $permissions = $this->getEffectivePermissions();

        // Helper alias resolution
        $checkKeys = [$permissionKey];
        if ($permissionKey === 'accounts') {
            $checkKeys[] = 'invoices';
            $checkKeys[] = 'staff_salaries';
            $checkKeys[] = 'fees';
        } elseif ($permissionKey === 'students') {
            $checkKeys[] = 'students_view';
            $checkKeys[] = 'student_registration';
        } elseif ($permissionKey === 'staff') {
            $checkKeys[] = 'staff_view';
            $checkKeys[] = 'staff_onboard';
        } elseif ($permissionKey === 'directory') {
            $checkKeys[] = 'master_directory';
            $checkKeys[] = 'staff_directory';
        } elseif ($permissionKey === 'timetables') {
            $checkKeys[] = 'timetable';
        } elseif ($permissionKey === 'academics') {
            $checkKeys[] = 'classes';
            $checkKeys[] = 'subjects';
        }

        if ($mode === 'edit') {
            foreach ($checkKeys as $k) {
                // Keys ending in _view only grant view rights, not edit
                if (str_ends_with($k, '_view')) {
                    continue;
                }
                if (!empty($permissions[$k . '_edit'])) {
                    return true;
                }
                $val = $permissions[$k] ?? null;
                if (!empty($val) && $val !== 'view' && $val !== '0' && $val !== false) {
                    // If user override explicitly set this key to view-only or disabled, deny edit
                    if (array_key_exists($k, $userOverrides) && ($userOverrides[$k] === 'view' || $userOverrides[$k] === false || $userOverrides[$k] === 0)) {
                        continue;
                    }
                    return true;
                }
            }
            return false;
        }

        // $mode === 'view'
        foreach ($checkKeys as $k) {
            $val = $permissions[$k] ?? null;
            if (!empty($permissions[$k . '_view']) || !empty($permissions[$k . '_edit']) || (!empty($val) && $val !== false && $val !== 0)) {
                // If user override explicitly disabled this key, check if all related view keys are off
                if (array_key_exists($k, $userOverrides) && ($userOverrides[$k] === false || $userOverrides[$k] === 0)) {
                    if (empty($permissions[$k . '_view']) && empty($permissions[$k . '_edit'])) {
                        continue;
                    }
                }
                return true;
            }
        }

        return false;
    }

    /**
     * Check if feature flag is enabled for user's institute.
     */
    public function hasFeature(string $featureKey): bool
    {
        if ($this->isGlobalAdmin()) {
            return true;
        }

        return $this->institute ? $this->institute->hasFeature($featureKey) : false;
    }

    /**
     * Determine the login credential field based on input.
     * Allows login by either email or custom identifier.
     * Student accounts are resolved by email only — institutional
     * identifiers (roll numbers) are not accepted as login credentials.
     */
    public static function findForLogin(string $credential): ?self
    {
        return static::where(function ($query) use ($credential) {
            $query->where('email', $credential);
            $query->orWhere(function ($sub) use ($credential) {
                $sub->whereNotNull('identifier')
                    ->where('identifier', '!=', '')
                    ->where('identifier', $credential)
                    ->where('role', '!=', 'student');
            });
        })->first();
    }

    /**
     * Resolve the route prefix for this staff member (e.g. 'accountant', 'staff', 'teacher').
     */
    public function getStaffUrlPrefix(): string
    {
        $role = strtolower($this->staff_role ?? '');
        if ($role === 'accountant') {
            return 'accountant';
        }
        if ($role === 'teacher' || empty($role)) {
            return 'teacher';
        }
        return 'staff';
    }

    /**
     * Generate an URL for this staff member with their role prefix.
     */
    public function staffUrl(string $path = '', array $params = []): string
    {
        $prefix = $this->getStaffUrlPrefix();
        $cleanPath = ltrim($path, '/');
        $url = url($prefix . ($cleanPath ? '/' . $cleanPath : ''));
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        return $url;
    }

    /**
     * Get relative dashboard URL based on user role.
     */
    public function dashboardRoute(): string
    {
        if ($this->isTeacher()) {
            return '/' . $this->getStaffUrlPrefix() . '/dashboard';
        }

        return match ($this->role) {
            self::ROLE_GLOBAL_ADMIN => route('global-admin.dashboard', [], false),
            self::ROLE_PRINCIPAL => route('principal.dashboard', [], false),
            self::ROLE_STUDENT => route('student.dashboard', [], false),
            default => route('dashboard', [], false),
        };
    }

    /**
     * Get the associated Student model for this user.
     */
    public function getStudentModel(): ?Student
    {
        if (!$this->isStudent()) {
            return null;
        }

        $student = $this->studentProfile ?: Student::withoutGlobalScopes()->with(['classSection.instituteClass'])->where('user_id', $this->id)->first();
        if (!$student && !empty($this->email)) {
            $student = Student::withoutGlobalScopes()->with(['classSection.instituteClass'])->whereRaw('LOWER(email) = ?', [strtolower($this->email)])->first();
        }
        if (!$student) {
            // Never fall back to an arbitrary student from another tenant.
            return null;
        }

        return $student;
    }

    /**
     * Get the dynamic display name for top navbar and profile headers.
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->isStudent()) {
            $student = $this->getStudentModel();
            if ($student) {
                return trim($student->first_name . ' ' . $student->last_name) ?: $this->name;
            }
        }
        return $this->name ?? 'User';
    }

    /**
     * Get the dynamic display email for top navbar and profile headers.
     */
    public function getDisplayEmailAttribute(): string
    {
        if ($this->isStudent()) {
            $student = $this->getStudentModel();
            if ($student && !empty($student->email)) {
                return $student->email;
            }
        }
        return $this->email ?? '';
    }

    /**
     * Get 2-letter uppercase initials for profile avatars.
     */
    public function getDisplayInitialsAttribute(): string
    {
        $name = $this->display_name;
        $parts = array_values(array_filter(explode(' ', trim($name))));
        if (count($parts) >= 2) {
            return strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
        }
        return strtoupper(substr($name, 0, 2));
    }

    /**
     * Subjects assigned to the teacher via teacher_subject_sections.
     */
    public function assignedSubjects(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'teacher_subject_sections', 'teacher_id', 'subject_id')
            ->distinct();
    }

    /**
     * Teacher's assignments across sections and subjects.
     */
    public function teacherSubjectSections(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TeacherSubjectSection::class, 'teacher_id');
    }

    /**
     * Assessment submissions made by the student.
     */
    public function assessmentSubmissions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AssessmentSubmission::class, 'student_id');
    }
}
