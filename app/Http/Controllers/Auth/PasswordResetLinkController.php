<?php

namespace App\Http\Controllers\Auth;

use App\Events\PasswordResetRequested;
use App\Events\PasswordResetRequestedAlert;
use App\Http\Controllers\Controller;
use App\Models\PasswordResetNotification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Password Reset Request Escalation Controller for UPLYFT (BUG-AUTH-003).
 *
 * Institutional Governance Workflow:
 *  1. Self-service password resets and direct OTP deliveries are disabled.
 *  2. Student / Teacher requests fire an alert routed exclusively to the Principal's dashboard.
 *  3. Principal requests fire an alert routed exclusively to the Global Admin dashboard.
 *  4. In-app notice instructs the user to contact administration for their institutional reset.
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
     * Enforces institutional escalation policy (BUG-AUTH-003).
     */
    public function store(Request $request): RedirectResponse
    {
        $raw = $request->input('credential') ?? $request->input('email') ?? $request->input('identifier');
        $request->merge(['credential' => trim((string) $raw)]);

        $request->validate([
            'credential' => ['required', 'string', 'max:255'],
        ]);

        $credential = trim((string) $request->input('credential'));
        $user = User::findForLogin($credential);

        if (! $user) {
            return redirect()
                ->route('password.request')
                ->with('status', 'Contact your administration for password reset.');
        }

        if ($user->isGlobalAdmin()) {
            return redirect()
                ->route('password.request')
                ->with('status', 'Global Admin accounts cannot be reset through this form. Please use the server-level emergency recovery procedure.');
        }

        // Determine destination dashboard based on institutional role
        $targetRole = $user->isPrincipal()
            ? PasswordResetNotification::TARGET_GLOBAL_ADMIN
            : PasswordResetNotification::TARGET_PRINCIPAL;

        // Check for existing pending request
        $existingRequest = PasswordResetNotification::where('user_id', $user->id)
            ->where('status', PasswordResetNotification::STATUS_PENDING)
            ->first();

        if ($existingRequest) {
            $existingRequest->touch();
            event(new PasswordResetRequested($user, $existingRequest));
            event(new PasswordResetRequestedAlert($user, $existingRequest));

            $notice = $user->isPrincipal()
                ? 'Contact your administration for password reset. A reset escalation alert is currently active on the Global Admin dashboard.'
                : 'Contact your administration for password reset. A reset request has already been escalated to your Principal.';

            return redirect()->route('password.request')->with('status', $notice);
        }

        // Generate 6-digit administrative verification code & security token
        $adminOtp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $cancellation_token = Str::random(40);

        // Record notification in administrative queue
        $notification = PasswordResetNotification::create([
            'user_id'            => $user->id,
            'institute_id'       => $user->institute_id,
            'status'             => PasswordResetNotification::STATUS_PENDING,
            'target_role'        => $targetRole,
            'otp'                => $adminOtp,
            'otp_expires_at'     => now()->addHours(24),
            'cancellation_token' => $cancellation_token,
        ]);

        // Dispatch real-time alert event to appropriate administrative dashboard
        event(new PasswordResetRequested($user, $notification));
        event(new PasswordResetRequestedAlert($user, $notification));

        // In-app message: NO direct reset token is emailed to the user
        $message = $user->isPrincipal()
            ? 'Contact your administration for password reset. Your request has been routed directly to the Global Admin dashboard.'
            : 'Contact your administration for password reset. An alert has been routed directly to your Principal.';

        return redirect()->route('password.request')->with('status', $message);
    }

    /**
     * Cancel a password reset request via cancellation link.
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

        $notification->cancel('Cancelled by user.');

        return redirect()
            ->route('login')
            ->with('status', '🔒 Your password reset request has been cancelled. Your account remains secure.');
    }

    /**
     * Show the OTP verification form (Redirects if self-service is disabled).
     */
    public function showOtpForm(Request $request): RedirectResponse|View
    {
        return redirect()
            ->route('password.request')
            ->with('status', 'Contact your administration for password reset. Direct user self-reset is disabled by institutional policy.');
    }

    /**
     * Self-service verify OTP endpoint is disabled under institutional governance.
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        return redirect()
            ->route('password.request')
            ->with('status', 'Contact your administration for password reset. Direct user self-reset is disabled by institutional policy.');
    }
}
