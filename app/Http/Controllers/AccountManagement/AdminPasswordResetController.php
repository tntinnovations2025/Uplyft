<?php

namespace App\Http\Controllers\AccountManagement;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetSuccessMail;
use App\Models\PasswordResetNotification;
use App\Models\User;
use App\Rules\StrongPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/**
 * Admin Password Reset Controller.
 *
 * Used by Principals (for students/teachers) and Global Admin (for principals)
 * to process pending password reset requests and execute the actual reset.
 */
class AdminPasswordResetController extends Controller
{
    /**
     * List all pending password reset requests for the current admin.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = PasswordResetNotification::with(['user', 'institute'])
            ->orderBy('created_at', 'desc');

        if ($user->isGlobalAdmin()) {
            // Show requests targeted at global_admin (principal resets)
            // and optionally all requests for oversight
            $query->where('target_role', PasswordResetNotification::TARGET_GLOBAL_ADMIN);
            $view = 'global-admin.password-resets.index';
        } elseif ($user->isPrincipal() || $user->hasPermission('security')) {
            // Show requests for this principal's / administration's campuses
            $campusIds = $user->authorizedCampusIds();

            if ($campusIds === null || count($campusIds) === 0) {
                abort(403);
            }

            $query->where('target_role', PasswordResetNotification::TARGET_PRINCIPAL)
                ->whereIn('institute_id', $campusIds);
            $view = 'principal.password-resets.index';
        } else {
            abort(403);
        }

        $resetRequests = $query->paginate(20);

        return view($view, compact('resetRequests'));
    }

    /**
     * Show a specific reset request with details.
     */
    public function show(Request $request, PasswordResetNotification $notification): View
    {
        $this->authorizeResetAccess($request->user(), $notification);

        $notification->load(['user', 'institute', 'processedBy']);

        $view = $request->user()->isGlobalAdmin()
            ? 'global-admin.password-resets.show'
            : 'principal.password-resets.show';

        return view($view, compact('notification'));
    }

    /**
     * Execute the password reset for an approved request.
     *
     * Admin sets a new temporary password for the user.
     */
    public function executeReset(Request $request, PasswordResetNotification $notification): RedirectResponse
    {
        $this->authorizeResetAccess($request->user(), $notification);

        if (! $notification->isPending() && ! $notification->isApproved()) {
            return redirect()
                ->back()
                ->with('error', 'This request has already been processed.');
        }

        $request->validate([
            'new_password' => ['required', 'string', 'confirmed', new StrongPassword],
        ]);

        $targetUser = $notification->user;
        $newPassword = $request->input('new_password');

        // Update the user's password
        $targetUser->update([
            'password' => Hash::make($newPassword),
        ]);

        // Mark the notification as completed
        $notification->markCompleted($request->user()->id);

        // Send email to user informing them of their new password
        if ($targetUser->email) {
            try {
                Mail::to($targetUser->email)->send(
                    new PasswordResetSuccessMail($targetUser, $newPassword, $request->user())
                );
            } catch (\Throwable $e) {
                Log::error("Failed to send password reset notification email to {$targetUser->email}: ".$e->getMessage());
            }
        }

        return redirect()
            ->back()
            ->with('success', "Password has been reset for {$targetUser->name} and confirmation email dispatched.");
    }

    /**
     * Deny a password reset request.
     */
    public function deny(Request $request, PasswordResetNotification $notification): RedirectResponse
    {
        $this->authorizeResetAccess($request->user(), $notification);

        $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $notification->markDenied(
            $request->user()->id,
            $request->input('notes')
        );

        return redirect()
            ->back()
            ->with('success', 'Password reset request has been denied.');
    }

    /**
     * Direct Password Reset by Principal / Administration without a prior request.
     */
    public function directResetUserPassword(Request $request, User $user): RedirectResponse
    {
        $admin = $request->user();

        // Enforce institute boundary and permission rules
        if (! $admin->isGlobalAdmin()) {
            if (! $admin->canAccessInstitute($user->getHomeInstituteId())) {
                abort(403, 'Unauthorized access to user outside your institute.');
            }
            if (! $admin->isPrincipal() && ! $admin->hasPermission('security') && ! $admin->hasPermission('staff', 'edit')) {
                abort(403, 'You do not have permission to reset user passwords.');
            }
            // Principal password can only be reset by Global Admin
            if ($user->isPrincipal() || $user->isGlobalAdmin()) {
                abort(403, 'Principal passwords can only be reset by Global Admin.');
            }
        }

        $request->validate([
            'new_password' => ['required', 'string', 'confirmed', new StrongPassword],
        ], [
            'new_password.required' => 'Please provide a new password.',
        ]);

        $newPassword = $request->input('new_password');

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        // Dispatch email notification to user
        if ($user->email) {
            try {
                Mail::to($user->email)->send(
                    new PasswordResetSuccessMail($user, $newPassword, $admin)
                );
            } catch (\Throwable $e) {
                Log::error("Direct password reset notification mail failed for {$user->email}: " . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', "🔑 Password for {$user->name} has been reset directly by Administration. Confirmation email sent.");
    }

    /**
     * Verify the admin has authority over this reset notification.
     */
    private function authorizeResetAccess(User $admin, PasswordResetNotification $notification): void
    {
        if ($admin->isGlobalAdmin()) {
            return; // Global admin can access everything
        }

        if (($admin->isPrincipal() || $admin->hasPermission('security'))
            && $notification->target_role === PasswordResetNotification::TARGET_PRINCIPAL
            && $admin->canAccessInstitute($notification->institute_id)) {
            return;
        }

        abort(403, 'You do not have permission to manage this password reset request.');
    }
}
