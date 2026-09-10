<?php

namespace App\Http\Controllers\AccountManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePrincipalRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Handles Principal account creation by Global Admin.
 *
 * Hierarchy: Only Global Admin → creates Principals
 */
class PrincipalAccountController extends Controller
{
    /**
     * Show the form to create a new Principal account.
     */
    public function create(): View
    {
        $this->authorize('createPrincipal', User::class);

        $institutes = \App\Models\Institute::orderBy('name')->get();

        return view('global-admin.accounts.create-principal', compact('institutes'));
    }

    /**
     * Store a new Principal account.
     */
    public function store(CreatePrincipalRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $principal = User::create([
            'name'         => $validated['name'],
            'email'        => $validated['email'],
            'identifier'   => $validated['identifier'] ?? null,
            'password'     => Hash::make($validated['password']),
            'role'         => User::ROLE_PRINCIPAL,
            'institute_id' => $validated['institute_id'],
            'created_by'   => $request->user()->id,
        ]);

        return redirect()
            ->route('global-admin.institutes.show', $principal->institute_id)
            ->with('success', "Principal account '{$principal->name}' created successfully.");
    }

    /**
     * List all principals (for Global Admin overview).
     */
    public function index(Request $request): View
    {
        $this->authorize('createPrincipal', User::class);

        $principals = User::where('role', User::ROLE_PRINCIPAL)
            ->with('institute')
            ->orderBy('name')
            ->paginate(20);

        return view('global-admin.accounts.principals-index', compact('principals'));
    }

    /**
     * Change password of a Principal account directly by Global Admin.
     */
    public function changePassword(Request $request, User $user): RedirectResponse
    {
        if (!auth()->user()->isGlobalAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        if (!$user->isPrincipal()) {
            return back()->with('error', 'The selected account is not a Principal account.');
        }

        $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', "Password for Principal '{$user->name}' ({$user->email}) updated successfully.");
    }

    /**
     * Permanently delete / unlink a Principal's credentials.
     * The Principal portal for this institute will no longer be linked to active credentials.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if (!auth()->user()->isGlobalAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        if (!$user->isPrincipal()) {
            return back()->with('error', 'The selected account is not a Principal account.');
        }

        $principalName = $user->name;
        $principalEmail = $user->email;
        $instituteName = $user->institute?->name ?? 'Institute';

        // Permanently delete the credentials from the database
        $user->forceDelete();

        return back()->with('success', "Principal credentials for '{$principalName}' ({$principalEmail}) have been permanently deleted and unlinked from {$instituteName}. The Principal portal for this campus is no longer linked to active credentials.");
    }

    /**
     * Update any user's system role (Global Admin master authority).
     * Global Admin can switch any account's role to Principal, Teacher, Student, or Staff.
     */
    public function updateUserRole(Request $request, User $user): RedirectResponse
    {
        if (!auth()->user()->isGlobalAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'role' => ['required', 'string', 'in:principal,teacher,student,staff,admin,global_admin'],
        ]);

        $oldRole = $user->role;
        $newRole = $validated['role'];

        $user->role = $newRole;
        $user->is_delegated_admin = false;

        if ($user->institute_id) {
            \App\Models\RoleDefaultAuthority::seedDefaultsForInstitute($user->institute_id);
        }

        if ($newRole === User::ROLE_PRINCIPAL) {
            $user->staff_role = 'Principal';
            $user->permissions = null;
        } elseif ($newRole === User::ROLE_TEACHER) {
            $user->staff_role = 'Teacher';
            if ($user->institute_id) {
                $roleAuth = \App\Models\RoleDefaultAuthority::where('institute_id', $user->institute_id)
                    ->where('role_slug', 'teacher')->first();
                $user->permissions = $roleAuth?->permissions ?? [];

                \App\Models\Teacher::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'institute_id' => $user->institute_id,
                        'first_name' => explode(' ', trim($user->name))[0] ?? 'Faculty',
                        'last_name' => explode(' ', trim($user->name), 2)[1] ?? '',
                        'email' => $user->email,
                        'qualification' => 'Faculty Member',
                        'matriculation_cert' => '',
                        'intermediate_cert' => '',
                        'bachelors_cert' => '',
                        'masters_cert' => '',
                        'phd_cert' => '',
                    ]
                );
            }
        } elseif ($newRole === User::ROLE_STUDENT) {
            $user->staff_role = null;
            $user->permissions = [];
        } elseif ($newRole === 'staff' || $newRole === 'admin') {
            $user->staff_role = 'Administration';
            if ($user->institute_id) {
                $roleAuth = \App\Models\RoleDefaultAuthority::where('institute_id', $user->institute_id)
                    ->where('role_slug', 'administration')->first();
                $user->permissions = $roleAuth?->permissions ?? [];
            }
        }

        $user->save();

        return back()->with('success', "Role for '{$user->name}' ({$user->email}) successfully updated from " . strtoupper($oldRole) . " to " . strtoupper($newRole) . ".");
    }
}
