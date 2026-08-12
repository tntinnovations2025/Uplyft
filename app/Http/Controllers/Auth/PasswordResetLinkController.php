<?php

namespace App\Http\Controllers\Auth;

use App\Events\PasswordResetRequested;
use App\Http\Controllers\Controller;
use App\Models\PasswordResetNotification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Custom Forgot Password Controller for UPLYFT.
 *
 * Does NOT send email tokens. Instead:
 *  - Students/Teachers: creates a reset request record → alerts Principal
 *  - Principals: creates a reset request record → alerts Global Admin
 *  - Displays "Contact your admin for password reset" message
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
     *
     * Instead of sending an email token, this creates a notification record
     * and dispatches a real-time alert to the appropriate admin.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'credential' => ['required', 'string', 'max:255'],
        ]);

        $credential = $request->input('credential');

        // Find user by email or identifier
        $user = User::findForLogin($credential);

        if (! $user) {
            // Don't reveal whether user exists - still show the "contact admin" message
            return redirect()
                ->route('password.request')
                ->with('status', 'If an account with that credential exists, your administrator has been notified.');
        }

        // Don't allow global admins to use this flow
        if ($user->isGlobalAdmin()) {
            return redirect()
                ->route('password.request')
                ->with('status', 'Global Admin accounts cannot be reset through this process. Please use the emergency recovery procedure.');
        }

        // Determine who should handle the reset
        $targetRole = $user->isPrincipal()
            ? PasswordResetNotification::TARGET_GLOBAL_ADMIN
            : PasswordResetNotification::TARGET_PRINCIPAL;

        // Check for existing pending request to prevent spam
        $existingRequest = PasswordResetNotification::where('user_id', $user->id)
            ->where('status', PasswordResetNotification::STATUS_PENDING)
            ->first();

        if ($existingRequest) {
            return redirect()
                ->route('password.request')
                ->with('status', 'A password reset request is already pending. Please contact your administrator.');
        }

        // Create the reset notification record
        $notification = PasswordResetNotification::create([
            'user_id'      => $user->id,
            'institute_id' => $user->institute_id,
            'status'       => PasswordResetNotification::STATUS_PENDING,
            'target_role'  => $targetRole,
        ]);

        // Fire the event → triggers real-time alert to admin dashboard
        event(new PasswordResetRequested($user, $notification));

        return redirect()
            ->route('password.request')
            ->with('status', 'Your password reset request has been submitted. Please contact your administrator for further assistance.');
    }
}
