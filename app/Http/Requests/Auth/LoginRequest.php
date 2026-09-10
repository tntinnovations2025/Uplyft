<?php

namespace App\Http\Requests\Auth;

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
 * Custom Login Request supporting dual-credential authentication.
 *
 * Users can log in with either:
 *  - A standard email address
 *  - A custom institutional identifier (Roll Number / Employee ID)
 *
 * The 'credential' field accepts both formats and resolves automatically.
 *
 * Rate limiting:
 *  - Tier A (network, 40 req/min/IP): enforced by the Throttle middleware.
 *  - Tier B (account): keyed by normalized email/identifier + institute,
 *    max 5 failed attempts per 15 minutes. Attempts are incremented on
 *    failures here and cleared on a successful login.
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
     * Attempt to authenticate the request's credentials.
     *
     * Tries matching against both email and identifier fields.
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

        if ($this->isStudentLogin()) {
            // Student portal: strict email-only lookup.
            $user = User::whereRaw('LOWER(email) = ?', [strtolower($credential)])->first();
        } else {
            // Non-student portals accept email OR institutional identifier;
            // student accounts may only authenticate via email.
            $user = User::where(function ($query) use ($credential) {
                $query->whereRaw('LOWER(email) = ?', [strtolower($credential)]);
                $query->orWhere(function ($sub) use ($credential) {
                    $sub->whereNotNull('identifier')
                        ->where('identifier', '!=', '')
                        ->where('identifier', $credential)
                        ->where('role', '!=', 'student');
                });
            })->first();
        }

        $authenticated = false;

        if ($user) {
            if (Hash::check($password, $user->password)) {

                // Student portal: the credential must map to a student account.
                if ($this->isStudentLogin() && ! $user->isStudent()) {
                    throw ValidationException::withMessages([
                        'credential' => 'This account is not registered as a Student. Please use the correct portal.',
                    ]);
                }

                // Check if user is deactivated
                if (isset($user->is_active) && !$user->is_active) {
                    RateLimiter::hit($this->throttleKey(), $this->accountDecaySeconds());
                    throw ValidationException::withMessages([
                        'credential' => 'Access Denied: Your user account has been deactivated. Please contact administration.',
                    ]);
                }

                // Check institute and organization status for non-global-admin users
                if (!$user->isGlobalAdmin()) {
                    $institute = $user->institute ?? ($user->current_institute_id ? \App\Models\Institute::withoutGlobalScopes()->withTrashed()->find($user->current_institute_id) : null);

                    if ($institute) {
                        if (!$institute->is_active || $institute->trashed()) {
                            RateLimiter::hit($this->throttleKey(), $this->accountDecaySeconds());
                            throw ValidationException::withMessages([
                                'credential' => "Services Temporarily Paused: Access for '{$institute->name}' is temporarily paused (e.g. pending payment resolution). All records are safely preserved. Please contact platform administration to resume services.",
                            ]);
                        }

                        if ($institute->organization_id && $institute->organization) {
                            $org = $institute->organization;
                            if (!$org->is_active || $org->trashed()) {
                                RateLimiter::hit($this->throttleKey(), $this->accountDecaySeconds());
                                throw ValidationException::withMessages([
                                    'credential' => "Services Temporarily Paused: Services for '{$org->name}' are temporarily paused (e.g. pending payment resolution). All records are safely preserved.",
                                ]);
                            }
                        }
                    }

                    if ($user->organization_id && $user->organization) {
                        $org = $user->organization;
                        if (!$org->is_active || $org->trashed()) {
                            RateLimiter::hit($this->throttleKey(), $this->accountDecaySeconds());
                            throw ValidationException::withMessages([
                                'credential' => "Access Denied: The organization network '{$org->name}' has been deactivated. All linked portals are disabled.",
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
                'credential' => trans('auth.failed'),
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
     *
     * Prefers the account-level key resolved by the Throttle middleware
     * (stored on the request attributes) so the pre-check, hit, and clear
     * operations all target the SAME key.
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
     * The institute is resolved from the credential's owning user so that
     * brute-force attempts are scoped per tenant and cannot lock out a
     * shared campus IP or consume another institute's quota.
     */
    public static function resolveLoginThrottleKey(Request $request): string
    {
        $credential  = trim((string) ($request->input('credential') ?? $request->input('email')));
        $normalized  = $credential === '' ? 'anonymous' : Str::transliterate(Str::lower($credential));
        $instituteId = 0;

        if ($credential !== '') {
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