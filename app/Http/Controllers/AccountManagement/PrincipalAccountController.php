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
}
