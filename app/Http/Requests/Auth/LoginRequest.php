<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
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
        return [
            'credential' => ['required', 'string', 'max:255'],
            'password'   => ['required', 'string'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'credential.required' => 'Please enter your email address or institutional ID.',
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

        $credential = $this->input('credential');
        $password   = $this->input('password');
        $remember   = $this->boolean('remember');

        // Determine if input looks like an email
        $isEmail = filter_var($credential, FILTER_VALIDATE_EMAIL) !== false;

        // Attempt authentication by email first, then by identifier
        $authenticated = false;

        if ($isEmail) {
            $authenticated = Auth::attempt(
                ['email' => $credential, 'password' => $password],
                $remember
            );
        }

        if (! $authenticated) {
            $authenticated = Auth::attempt(
                ['identifier' => $credential, 'password' => $password],
                $remember
            );
        }

        // Fallback: if it looked like email but failed, also try identifier
        if (! $authenticated && $isEmail) {
            $authenticated = Auth::attempt(
                ['identifier' => $credential, 'password' => $password],
                $remember
            );
        }

        if (! $authenticated) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'credential' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
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
        return Str::transliterate(
            Str::lower($this->string('credential')) . '|' . $this->ip()
        );
    }
}
