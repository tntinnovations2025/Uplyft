<?php

namespace App\Http\Controllers\AccountManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStaffStudentRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Handles Teacher & Student account creation by Principals (or delegated Teachers).
 *
 * Hierarchy:
 *  Principal → creates Teachers & Students in their institute
 *  Teacher (with is_delegated_admin) → creates Teachers & Students in their institute
 */
class StaffStudentAccountController extends Controller
{
    /**
     * List all staff and students in the authenticated user's institute.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $user = $request->user();

        $users = User::where('institute_id', $user->institute_id)
            ->whereIn('role', [User::ROLE_TEACHER, User::ROLE_STUDENT])
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(25);

        return view('principal.accounts.index', compact('users'));
    }

    /**
     * Show the form to create a Teacher or Student account.
     */
    public function create(): View
    {
        $this->authorize('createStaffOrStudent', User::class);

        return view('principal.accounts.create');
    }

    /**
     * Store a new Teacher or Student account.
     */
    public function store(CreateStaffStudentRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $creator   = $request->user();

        $account = User::create([
            'name'         => $validated['name'],
            'email'        => $validated['email'] ?? null,
            'identifier'   => $validated['identifier'],
            'password'     => Hash::make($validated['password']),
            'role'         => $validated['role'],
            'institute_id' => $creator->institute_id,
            'created_by'   => $creator->id,
        ]);

        $roleLabel = ucfirst($account->role);

        return redirect()
            ->route('principal.accounts.index')
            ->with('success', "{$roleLabel} account '{$account->name}' ({$account->identifier}) created successfully.");
    }

    /**
     * Show a specific user's profile.
     */
    public function show(User $user): View
    {
        $this->authorize('view', $user);

        return view('principal.accounts.show', compact('user'));
    }

    /**
     * Show the edit form for a user.
     */
    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('principal.accounts.edit', compact('user'));
    }

    /**
     * Update a user's profile.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['nullable', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'identifier' => ['required', 'string', 'max:50', 'unique:users,identifier,' . $user->id],
        ]);

        $user->update($validated);

        return redirect()
            ->route('principal.accounts.show', $user)
            ->with('success', 'Account updated successfully.');
    }

    /**
     * Soft-delete / deactivate a user.
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        return redirect()
            ->route('principal.accounts.index')
            ->with('success', "Account '{$user->name}' has been deactivated.");
    }
}
