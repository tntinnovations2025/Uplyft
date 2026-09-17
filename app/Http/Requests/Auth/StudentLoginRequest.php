<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * Dedicated Student Portal Login Request.
 * Supports multi-format authentication (BUG-AUTH-001):
 *  - Standard student email address (name@student.local)
 *  - Custom Roll Numbers with slashes, dashes, or alphanumeric strings (e.g., STU-2026/0101, 10A-045)
 *
 * Scoped strictly by active tenant/institute to eliminate cross-institute roll number collisions.
 */
class StudentLoginRequest extends LoginRequest
{
    private const ALLOWED_FIELDS = ['identifier', 'credential', 'email', 'password', 'remember', '_token', 'institute_id', 'tenant_id'];

    /**
     * Get the validation rules that apply to the request.
     * Accepts flexible roll numbers (slashes, dashes, alphanumeric) or email identifiers (BUG-AUTH-001).
     */
    public function rules(): array
    {
        return [
            'identifier' => ['required', 'string', 'max:50'],
            'password'   => ['required', 'string'],
            'remember'   => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $raw = $this->input('identifier') ?? $this->input('credential') ?? $this->input('email');
        $resolved = trim((string) $raw);

        $this->merge([
            'identifier' => $resolved,
            'credential' => $resolved,
            'password'   => (string) $this->input('password'),
            'remember'   => $this->boolean('remember'),
        ]);
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'identifier.required' => 'Please enter your student Roll Number or email address.',
            'identifier.max'      => 'Identifier must not exceed 50 characters.',
            'credential.required' => 'Please enter your student Roll Number or email address.',
            'credential.max'      => 'Identifier must not exceed 50 characters.',
            'password.required'   => 'Please enter your password.',
        ];
    }

    /**
     * Authenticate student credentials with strict tenant scoping.
     * Accepts both student roll numbers (User.identifier or Student.roll_number) and email addresses.
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credential = trim((string) $this->input('credential'));
        $password   = (string) $this->input('password');
        $remember   = $this->boolean('remember');

        if (empty($credential)) {
            throw ValidationException::withMessages([
                'identifier' => 'Please enter your student Roll Number or email address.',
                'credential' => 'Please enter your student Roll Number or email address.',
            ]);
        }

        $currentInstituteId = $this->resolveActiveInstituteId();

        // 1. Build Query for Student role
        $query = User::where('role', User::ROLE_STUDENT);

        if ($currentInstituteId) {
            $query->where('institute_id', $currentInstituteId);
        }

        // 2. Multi-Format Match: Email OR Roll Number / Identifier
        if (filter_var($credential, FILTER_VALIDATE_EMAIL)) {
            $query->whereRaw('LOWER(email) = ?', [strtolower($credential)]);
            $user = $query->first();
        } else {
            $query->where(function ($sub) use ($credential) {
                $sub->where('identifier', $credential)
                    ->orWhereHas('studentProfile', function ($sq) use ($credential) {
                        $sq->where('roll_number', $credential);
                    });
            });

            if (! $currentInstituteId) {
                // When no institute context is supplied, ensure identifier is unambiguously unique across the platform
                $matches = (clone $query)->get();
                if ($matches->count() === 1) {
                    $user = $matches->first();
                } else {
                    RateLimiter::hit($this->throttleKey(), $this->accountDecaySeconds());
                    throw ValidationException::withMessages([
                        'identifier' => 'Wrong credentials!',
                        'credential' => 'Wrong credentials!',
                    ]);
                }
            } else {
                $user = $query->first();
            }
        }

        $authenticated = false;

        if ($user && Hash::check($password, $user->password)) {
            // Check if user is deactivated
            if (isset($user->is_active) && ! $user->is_active) {
                RateLimiter::hit($this->throttleKey(), $this->accountDecaySeconds());
                throw ValidationException::withMessages([
                    'identifier' => 'Access Denied: Your student account has been deactivated. Please contact administration.',
                    'credential' => 'Access Denied: Your student account has been deactivated. Please contact administration.',
                ]);
            }

            // Verify institute is active
            $institute = $user->institute;
            if ($institute && (! $institute->is_active || $institute->trashed())) {
                RateLimiter::hit($this->throttleKey(), $this->accountDecaySeconds());
                throw ValidationException::withMessages([
                    'identifier' => "Access Paused: The educational institution '{$institute->name}' is temporarily paused.",
                    'credential' => "Access Paused: The educational institution '{$institute->name}' is temporarily paused.",
                ]);
            }

            Auth::login($user, $remember);
            $authenticated = true;
        }

        if (! $authenticated) {
            RateLimiter::hit($this->throttleKey(), $this->accountDecaySeconds());

            throw ValidationException::withMessages([
                'identifier' => 'Wrong credentials!',
                'credential' => 'Wrong credentials!',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * This request is always bound to the Student portal login.
     */
    protected function isStudentLogin(): bool
    {
        return true;
    }
}