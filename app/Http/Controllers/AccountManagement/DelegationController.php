<?php

namespace App\Http\Controllers\AccountManagement;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Allows Principals to toggle the is_delegated_admin flag on Teachers.
 *
 * When enabled, the teacher gains account-creation capabilities
 * identical to the Principal's within the same institute.
 */
class DelegationController extends Controller
{
    /**
     * Toggle the delegation flag on a teacher.
     */
    public function toggle(Request $request, User $user): RedirectResponse
    {
        $this->authorize('toggleDelegation', $user);

        $user->update([
            'is_delegated_admin' => ! $user->is_delegated_admin,
        ]);

        $status = $user->is_delegated_admin ? 'granted' : 'revoked';

        return redirect()
            ->back()
            ->with('success', "Admin delegation {$status} for {$user->name}.");
    }

    /**
     * List all teachers with their delegation status for the current institute.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Only principals can view delegation list
        if (! $user->isPrincipal()) {
            abort(403);
        }

        $teachers = User::where('institute_id', $user->institute_id)
            ->where('role', User::ROLE_TEACHER)
            ->orderBy('name')
            ->paginate(20);

        return view('principal.delegation.index', compact('teachers'));
    }
}
