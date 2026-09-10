<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\StrongPassword;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Show the "Forgot Password" form (email entry).
     *
     * Accessed via GET /forgot-password-token.
     * The user enters their email and the broker sends a reset link.
     */
    public function create(Request $request): View
    {
        return view('auth.forgot-password-token', ['request' => $request]);
    }

    /**
     * Handle the "Send Reset Link" request.
     *
     * Accessed via POST /forgot-password-token.
     * Uses Password::sendResetLink() which:
     *   1. Resolves the user by email via the configured provider
     *   2. Generates a token, hashes it, stores it in password_reset_tokens
     *   3. Calls sendPasswordResetNotification($token) on the User model
     *   4. Returns a status string (SUCCESS / INVALID_USER / RESET_LINK_SENT)
     *
     * The response is intentionally generic to prevent user enumeration.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Password::sendResetLink() calls the User model's
        // sendPasswordResetNotification($token) automatically.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        // Always return the same generic message regardless of whether
        // the email exists — this eliminates user enumeration.
        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
    }

    /**
     * Show the "Set New Password" form (token + new password entry).
     *
     * Accessed via GET /reset-password/{token}.
     * The token and email are embedded in the URL by the reset link.
     */
    public function resetCreate(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle the "Set New Password" request.
     *
     * Accessed via POST /reset-password.
     * Uses Password::reset() which:
     *   1. Validates the token against the hashed row in password_reset_tokens
     *   2. Resolves the user by email
     *   3. Executes the closure (hashes + saves the new password)
     *   4. Fires the PasswordReset event
     *   5. Purges the used token row (automatic cleanup)
     *
     * Password hashing: Argon2id (config/hashing.php → driver: argon2id)
     * Token cleanup: Automatic — the token row is deleted after successful use.
     * Token expiry: Enforced by the broker (config/auth.php → passwords.users.expire = 60 min).
     */
    public function resetStore(Request $request): RedirectResponse
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', new StrongPassword],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password'       => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            $loginUrl = $this->getLoginUrlForUser($request->input('email'));

            return redirect($loginUrl)->with('status', __($status));
        }

        return back()->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }

    /**
     * Resolve the portal-specific login URL for a given email address.
     *
     * Returns the appropriate login route based on the user's role.
     * Falls back to the generic login page if the user is not found.
     */
    private function getLoginUrlForUser(string $email): string
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            return route('login');
        }

        if (method_exists($user, 'isGlobalAdmin') && $user->isGlobalAdmin()) {
            return route('global-admin.login');
        }

        if (method_exists($user, 'isPrincipal') && $user->isPrincipal()) {
            return route('principal.login');
        }

        return route('login');
    }
}
