<?php

namespace App\Http\Controllers\AccountManagement;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetNotification;
use App\Models\User;
use App\Rules\StrongPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
        } elseif ($user->isPrincipal()) {
            // Show requests for this principal's institute
            $query->where('target_role', PasswordResetNotification::TARGET_PRINCIPAL)
                  ->where('institute_id', $user->institute_id);
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

        // Update the user's password
        $targetUser->update([
            'password' => Hash::make($request->input('new_password')),
        ]);

        // Mark the notification as completed
        $notification->markCompleted($request->user()->id);

        return redirect()
            ->back()
            ->with('success', "Password has been reset for {$targetUser->name}.");
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
     * Verify the admin has authority over this reset notification.
     */
    private function authorizeResetAccess(User $admin, PasswordResetNotification $notification): void
    {
        if ($admin->isGlobalAdmin()) {
            return; // Global admin can access everything
        }

        if ($admin->isPrincipal()
            && $notification->target_role === PasswordResetNotification::TARGET_PRINCIPAL
            && $notification->institute_id === $admin->institute_id) {
            return;
        }

        abort(403, 'You do not have permission to manage this password reset request.');
    }
}
