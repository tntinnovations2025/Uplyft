<?php

namespace App\Http\Controllers\Auth;

use App\Events\PasswordResetRequested;
use App\Http\Controllers\Controller;
use App\Mail\PasswordResetRequestedMail;
use App\Mail\PasswordResetSuccessMail;
use App\Models\PasswordResetNotification;
use App\Models\User;
use App\Rules\StrongPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Password Reset Request & OTP Controller for UPLYFT.
 *
 * Workflow:
 *  1. User submits request → Generates 6-Digit OTP & One-Click Cancellation Token.
 *  2. Dispatches Email to user with OTP + "Cancel Request" security link.
 *  3. Dispatches Real-Time Alert to Global Admin (for Principal) or Principal (for Student/Teacher).
 *  4. User can verify OTP self-service OR Administrator can process reset.
 *  5. Sends final confirmation email with updated password upon completion.
 */
class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'credential' => ['required', 'string', 'max:255'],
        ]);

        $credential = $request->input('credential');
        $user = User::findForLogin($credential);

        if (! $user) {
            return redirect()
                ->route('password.request')
                ->with('status', 'If an account with that credential exists, a security notification and OTP have been sent to your email.');
        }

        if ($user->isGlobalAdmin()) {
            return redirect()
                ->route('password.request')
                ->with('status', 'Global Admin accounts cannot be reset through this process. Please use the emergency recovery procedure.');
        }

        $targetRole = $user->isPrincipal()
            ? PasswordResetNotification::TARGET_GLOBAL_ADMIN
            : PasswordResetNotification::TARGET_PRINCIPAL;

        // Check for existing pending request
        $existingRequest = PasswordResetNotification::where('user_id', $user->id)
            ->where('status', PasswordResetNotification::STATUS_PENDING)
            ->first();

        if ($existingRequest) {
            return redirect()
                ->route('password.otp.show', ['credential' => $credential])
                ->with('status', 'A password reset request is already pending for your account. Please enter your OTP code below or contact your administrator.');
        }

        // Generate 6-digit OTP code & cancellation token
        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $cancellation_token = Str::random(40);

        // Create notification record
        $notification = PasswordResetNotification::create([
            'user_id' => $user->id,
            'institute_id' => $user->institute_id,
            'status' => PasswordResetNotification::STATUS_PENDING,
            'target_role' => $targetRole,
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(30),
            'cancellation_token' => $cancellation_token,
        ]);

        // Dispatch email notification to user
        if ($user->email) {
            try {
                Mail::to($user->email)->send(new PasswordResetRequestedMail($user, $notification));
            } catch (\Throwable $e) {
                Log::error("Failed to send password reset request email to {$user->email}: ".$e->getMessage());
            }
        }

        // Fire real-time alert event for admin dashboard
        event(new PasswordResetRequested($user, $notification));

        return redirect()
            ->route('password.otp.show', ['credential' => $credential])
            ->with('status', 'A confirmation email with your 6-digit OTP has been sent. Check your inbox to enter the code or cancel the request.');
    }

    /**
     * Cancel a password reset request via security link in email.
     */
    public function cancel(string $token): RedirectResponse
    {
        $notification = PasswordResetNotification::where('cancellation_token', $token)
            ->where('status', PasswordResetNotification::STATUS_PENDING)
            ->first();

        if (! $notification) {
            return redirect()
                ->route('login')
                ->with('error', 'Invalid or expired password reset cancellation token.');
        }

        $notification->cancel('Cancelled by user via security link in email.');

        return redirect()
            ->route('login')
            ->with('status', '🔒 Your password reset request has been CANCELLED successfully. Your account is secure.');
    }

    /**
     * Show the OTP verification form.
     */
    public function showOtpForm(Request $request): View
    {
        $credential = $request->query('credential', '');

        return view('auth.verify-otp', compact('credential'));
    }

    /**
     * Verify OTP and reset password.
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'credential' => ['required', 'string', 'max:255'],
            'otp' => ['required', 'string', 'size:6'],
            'password' => ['required', 'confirmed', new StrongPassword],
        ]);

        $user = User::findForLogin($validated['credential']);

        if (! $user) {
            return back()->withInput()->withErrors(['credential' => 'No account found matching this credential.']);
        }

        $notification = PasswordResetNotification::where('user_id', $user->id)
            ->where('status', PasswordResetNotification::STATUS_PENDING)
            ->first();

        if (! $notification || ! $notification->isOtpValid($validated['otp'])) {
            return back()->withInput()->withErrors(['otp' => 'Invalid or expired 6-digit OTP code. Please check your email.']);
        }

        // Update password
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Mark request completed
        $notification->markCompleted();

        // Send confirmation email with new password notification
        if ($user->email) {
            try {
                Mail::to($user->email)->send(new PasswordResetSuccessMail($user, $validated['password']));
            } catch (\Throwable $e) {
                Log::error("Failed to send password reset success email to {$user->email}: ".$e->getMessage());
            }
        }

        return redirect()
            ->route('login')
            ->with('status', '✅ Your password has been reset successfully! You can now log in.');
    }
}
