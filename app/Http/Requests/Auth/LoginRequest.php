<?php

namespace App\Http\Requests\Auth;

use App\Models\Institute;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Custom Login Request supporting dual-credential authentication with strict multi-tenant scoping.
 *
 * Users can log in with either:
 *  - A standard email address
 *  - A custom institutional identifier (Roll Number / Employee ID)
 *
 * Multi-tenancy Isolation:
 *  - Non-email identifier logins MUST be scoped to the active tenant/institute context
 *    to prevent cross-tenant credential collisions (BUG-AUTH-002).
 *  - Tenant context is resolved via explicit input, headers, route bindings,
 *    subdomains, session, or container bindings.
 *
 * Rate limiting:
 *  - Tier A (network, 40 req/min/IP): enforced by the Throttle middleware.
 *  - Tier B (account): keyed by normalized email/identifier + institute,
 *    max 5 failed attempts per 15 minutes.
 */
class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'credential' => ['required', 'string', 'max:255'],
            'password'   => ['required', 'string'],
        ];

        // Strict login for the Student portal: email address only — no
        // roll numbers / institutional identifiers are accepted here.
        if ($this->isStudentLogin()) {
            $rules['credential'] = ['required', 'string', 'max:255', 'email'];
        }

        return $rules;
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'credential.required' => 'Please enter your email address or institutional ID.',
            'credential.email'    => 'Student login requires a valid email address.',
        ];
    }

    /**
     * Resolve the active tenant/institute ID context for the incoming request.
     *
     * Resolution hierarchy:
     * 1. Direct request input ('institute_id', 'tenant_id')
     * 2. Request headers ('X-Institute-Id', 'X-Tenant-Id')
     * 3. Route parameter ('institute', 'institute_id')
     * 4. Application container binding ('current_institute_id')
     * 5. Session state ('current_institute_id', 'active_institute_id', 'tenant_id')
     * 6. Subdomain from host (e.g., apex.uplyft.example.com -> 'apex')
     */
    public function resolveActiveInstituteId(): ?int
    {
        // 1. Direct request input
        if ($id = $this->input('institute_id') ?? $this->input('tenant_id')) {
            if (is_numeric($id)) {
                return (int) $id;
            }
        }

        // 2. Request headers
        if ($id = $this->header('X-Institute-Id') ?? $this->header('X-Tenant-Id')) {
            if (is_numeric($id)) {
                return (int) $id;
            }
        }

        // 3. Route parameters
        if ($param = $this->route('institute') ?? $this->route('institute_id')) {
            if ($param instanceof Institute) {
                return (int) $param->id;
            }
            if (is_numeric($param)) {
                return (int) $param;
            }
        }

        // 4. Container binding
        if (app()->bound('current_institute_id') && ($boundId = app('current_institute_id'))) {
            return (int) $boundId;
        }

        // 5. Session state
        if ($sessionId = session('current_institute_id') ?? session('active_institute_id') ?? session('tenant_id')) {
            return (int) $sessionId;
        }

        // 6. Subdomain resolution
        $host = $this->getHost();
        $parts = explode('.', $host);
        if (count($parts) >= 3) {
            $subdomain = strtolower($parts[0]);
            $reserved = ['www', 'app', 'admin', 'portal', 'api', 'mail', 'localhost', 'staging', 'dev'];
            if (!in_array($subdomain, $reserved, true)) {
                $instituteId = Institute::withoutGlobalScopes()
                    ->where('slug', $subdomain)
                    ->value('id');
                if ($instituteId) {
                    return (int) $instituteId;
                }
            }
        }

        return null;
    }

    /**
     * Resolve the intended target role for strict role isolation.
     */
    public function getTargetRole(): ?string
    {
        if ($this->routeIs('principal.login*') || $this->is('principal/login*')) {
            return User::ROLE_PRINCIPAL;
        }

        if ($this->routeIs('faculty.login*') || $this->routeIs('teacher.login*') || $this->is('faculty/login*') || $this->is('teacher/login*')) {
            return User::ROLE_TEACHER;
        }

        if ($this->routeIs('student.login*') || $this->is('student/login*') || $this->isStudentLogin()) {
            return User::ROLE_STUDENT;
        }

        if ($this->routeIs('globaladmin.login*') || $this->routeIs('global-admin.login*') || $this->is('globaladmin*') || $this->is('global-admin*')) {
            return User::ROLE_GLOBAL_ADMIN;
        }

        $inputRole = $this->input('role');
        if ($inputRole) {
            return match (strtolower(trim((string) $inputRole))) {
                'principal'                            => User::ROLE_PRINCIPAL,
                'faculty', 'teacher'                   => User::ROLE_TEACHER,
                'student'                              => User::ROLE_STUDENT,
                'globaladmin', 'global_admin', 'admin' => User::ROLE_GLOBAL_ADMIN,
                default                                => null,
            };
        }

        return null;
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credential = trim((string) $this->input('credential'));
        $password   = (string) $this->input('password');
        $remember   = $this->boolean('remember');

        if (empty($credential)) {
            throw ValidationException::withMessages([
                'credential' => 'Please enter your email address or institutional ID.',
            ]);
        }

        $currentInstituteId = $this->resolveActiveInstituteId();
        $user = null;

        if ($this->isStudentLogin()) {
            // Student portal: strict email lookup. Scoped by institute if tenant context is present.
            $query = User::whereRaw('LOWER(email) = ?', [strtolower($credential)]);
            if ($currentInstituteId) {
                $query->where('institute_id', $currentInstituteId);
            }
            $user = $query->first();
        } else {
            // Non-student portals: Email OR Institutional Identifier
            if (filter_var($credential, FILTER_VALIDATE_EMAIL)) {
                $query = User::whereRaw('LOWER(email) = ?', [strtolower($credential)]);
                
                if ($currentInstituteId) {
                    // In a scoped tenant environment, ensure user belongs to this institute or is a Global Admin
                    $user = (clone $query)->where(function ($q) use ($currentInstituteId) {
                        $q->where('institute_id', $currentInstituteId)
                          ->orWhere('role', User::ROLE_GLOBAL_ADMIN);
                    })->first();

                    if (!$user) {
                        // Check if email exists in another institute to prevent cross-tenant bleed
                        $crossUser = $query->first();
                        if ($crossUser && !$crossUser->isGlobalAdmin()) {
                            RateLimiter::hit($this->throttleKey(), $this->accountDecaySeconds());
                            throw ValidationException::withMessages([
                                'credential' => 'Wrong credentials!',
                            ]);
                        }
                    }
                } else {
                    $user = $query->first();
                }
            } else {
                // Non-email credential: must be an institutional identifier (e.g. EMP-100)
                // STRICT MULTI-TENANT ISOLATION (BUG-AUTH-002)
                if ($currentInstituteId) {
                    $user = User::where('identifier', $credential)
                        ->where('institute_id', $currentInstituteId)
                        ->where('role', '!=', 'student')
                        ->first();
                } else {
                    // When no institute context is supplied, ensure identifier is unambiguously unique
                    $matchingUsers = User::where('identifier', $credential)
                        ->where('role', '!=', 'student')
                        ->get();

                    if ($matchingUsers->count() === 1) {
                        $user = $matchingUsers->first();
                    } else {
                        // Ambiguous collision across tenants or not found: reject with clean unrevealing error
                        RateLimiter::hit($this->throttleKey(), $this->accountDecaySeconds());
                        throw ValidationException::withMessages([
                            'credential' => 'Wrong credentials!',
                        ]);
                    }
                }
            }
        }

        $authenticated = false;

        if ($user) {
            if (Hash::check($password, $user->password)) {

                // ── Strict Role Isolation Check ──
                $targetRole = $this->getTargetRole();

                if ($targetRole !== null) {
                    $matches = match ($targetRole) {
                        User::ROLE_PRINCIPAL    => $user->isPrincipal(),
                        User::ROLE_TEACHER      => $user->isTeacher(),
                        User::ROLE_STUDENT      => $user->isStudent(),
                        User::ROLE_GLOBAL_ADMIN => $user->isGlobalAdmin(),
                        default                 => false,
                    };

                    if (! $matches) {
                        RateLimiter::hit($this->throttleKey(), $this->accountDecaySeconds());
                        throw ValidationException::withMessages([
                            'credential' => 'Wrong credentials!',
                        ]);
                    }
                } else {
                    // Global admin cannot use default gateway form
                    if ($user->isGlobalAdmin()) {
                        RateLimiter::hit($this->throttleKey(), $this->accountDecaySeconds());
                        throw ValidationException::withMessages([
                            'credential' => 'Wrong credentials!',
                        ]);
                    }
                }

                // Student portal: credential must belong to a student
                if ($this->isStudentLogin() && ! $user->isStudent()) {
                    RateLimiter::hit($this->throttleKey(), $this->accountDecaySeconds());
                    throw ValidationException::withMessages([
                        'credential' => 'Wrong credentials!',
                    ]);
                }

                // Check if user is deactivated
                if (isset($user->is_active) && ! $user->is_active) {
                    RateLimiter::hit($this->throttleKey(), $this->accountDecaySeconds());
                    throw ValidationException::withMessages([
                        'credential' => 'Access Denied: Your user account has been deactivated. Please contact administration.',
                    ]);
                }

                // Check institute and organization status for non-global-admin users
                if (! $user->isGlobalAdmin()) {
                    $institute = $user->institute ?? ($user->current_institute_id ? Institute::withoutGlobalScopes()->withTrashed()->find($user->current_institute_id) : null);

                    if ($institute) {
                        if (! $institute->is_active || $institute->trashed()) {
                            RateLimiter::hit($this->throttleKey(), $this->accountDecaySeconds());
                            throw ValidationException::withMessages([
                                'credential' => "Services Temporarily Paused: Access for '{$institute->name}' is temporarily paused. All records are safely preserved.",
                            ]);
                        }

                        if ($institute->organization_id && $institute->organization) {
                            $org = $institute->organization;
                            if (! $org->is_active || $org->trashed()) {
                                RateLimiter::hit($this->throttleKey(), $this->accountDecaySeconds());
                                throw ValidationException::withMessages([
                                    'credential' => "Services Temporarily Paused: Services for '{$org->name}' are temporarily paused.",
                                ]);
                            }
                        }
                    }

                    if ($user->organization_id && $user->organization) {
                        $org = $user->organization;
                        if (! $org->is_active || $org->trashed()) {
                            RateLimiter::hit($this->throttleKey(), $this->accountDecaySeconds());
                            throw ValidationException::withMessages([
                                'credential' => "Access Denied: The organization network '{$org->name}' has been deactivated.",
                            ]);
                        }
                    }
                }

                Auth::login($user, $remember);
                $authenticated = true;
            }
        }

        if (! $authenticated) {
            RateLimiter::hit($this->throttleKey(), $this->accountDecaySeconds());

            throw ValidationException::withMessages([
                'credential' => 'Wrong credentials!',
            ]);
        }

        // Successful login: reset the account-level failed-attempt counter.
        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), $this->accountMaxAttempts())) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'credential' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        if ($this->attributes->has('uplyft_login_throttle_key')) {
            return (string) $this->attributes->get('uplyft_login_throttle_key');
        }

        return static::resolveLoginThrottleKey($this);
    }

    /**
     * Resolve the account-level throttle key for a given login request.
     *
     * Key = md5( normalizedCredential | instituteId )
     */
    public static function resolveLoginThrottleKey(Request $request): string
    {
        $credential  = trim((string) ($request->input('credential') ?? $request->input('email')));
        $normalized  = $credential === '' ? 'anonymous' : Str::transliterate(Str::lower($credential));
        $instituteId = (int) ($request->input('institute_id') ?? $request->header('X-Institute-Id') ?? 0);

        if ($instituteId === 0 && app()->bound('current_institute_id')) {
            $instituteId = (int) app('current_institute_id');
        }

        if ($instituteId === 0 && ($sessionId = session('current_institute_id') ?? session('active_institute_id'))) {
            $instituteId = (int) $sessionId;
        }

        if ($instituteId === 0 && $credential !== '') {
            $user = User::withoutGlobalScopes()
                ->where(function ($query) use ($credential) {
                    $query->whereRaw('LOWER(email) = ?', [strtolower($credential)]);
                    $query->orWhere(function ($sub) use ($credential) {
                        $sub->whereNotNull('identifier')
                            ->where('identifier', '!=', '')
                            ->where('identifier', $credential)
                            ->where('role', '!=', 'student');
                    });
                })
                ->first(['institute_id', 'current_institute_id']);

            if ($user) {
                $instituteId = (int) ($user->current_institute_id ?: $user->institute_id ?: 0);
            }
        }

        return 'login-account:' . md5($normalized . '|' . $instituteId);
    }

    /**
     * Determine whether this request targets the Student portal login.
     */
    protected function isStudentLogin(): bool
    {
        return $this->routeIs('student.login') || $this->routeIs('student.login.store');
    }


    /**
     * Maximum allowed failed attempts within the account lockout window.
     */
    protected function accountMaxAttempts(): int
    {
        return (int) config('rate-limiter.login.account_max_attempts', 5);
    }

    /**
     * Account lockout window length in seconds.
     */
    protected function accountDecaySeconds(): int
    {
        return (int) config('rate-limiter.login.account_decay_minutes', 15) * 60;
    }
}