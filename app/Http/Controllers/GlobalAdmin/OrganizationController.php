<?php

namespace App\Http\Controllers\GlobalAdmin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    /**
     * Display a listing of registered Organizations.
     */
    public function index(): View
    {
        $organizations = Organization::with(['owner', 'institutes.principals'])
            ->withCount('institutes')
            ->orderBy('name')
            ->get();

        return view('global-admin.organizations.index', compact('organizations'));
    }

    /**
     * Show form to create a new Organization Network.
     */
    public function create(): View
    {
        // Principals eligible to be assigned as Organization Owner
        $principals = User::where('role', 'principal')
            ->orderBy('name')
            ->get();

        return view('global-admin.organizations.create', compact('principals'));
    }

    /**
     * Store a newly created Organization Network.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:255|unique:organizations,name',
            'max_campuses'          => 'required|integer|min:1|max:50',
            'owner_user_id'         => 'nullable|exists:users,id',
            'owner_mode'            => 'required|in:existing,new,none',
            'new_owner_name'        => 'nullable|required_if:owner_mode,new|string|max:255',
            'new_owner_email'       => 'nullable|required_if:owner_mode,new|email|unique:users,email',
            'new_owner_password'    => 'nullable|required_if:owner_mode,new|string|min:8|confirmed',
        ]);

        DB::beginTransaction();
        try {
            $ownerId = null;

            if ($request->input('owner_mode') === 'existing' && $request->filled('owner_user_id')) {
                $ownerId = $request->input('owner_user_id');
            } elseif ($request->input('owner_mode') === 'new') {
                $newOwner = User::create([
                    'name'                 => trim($request->input('new_owner_name')),
                    'email'                => strtolower(trim($request->input('new_owner_email'))),
                    'password'             => $request->input('new_owner_password'),
                    'role'                 => 'principal',
                    'is_primary_principal' => true,
                ]);
                $ownerId = $newOwner->id;
            }

            $org = Organization::create([
                'name'          => trim($validated['name']),
                'max_campuses'  => (int)$validated['max_campuses'],
                'owner_user_id' => $ownerId,
            ]);

            if ($ownerId) {
                User::where('id', $ownerId)->update([
                    'organization_id'      => $org->id,
                    'is_primary_principal' => true,
                ]);
            }

            DB::commit();
            Log::info('Global Admin: Registered new Organization Network', ['org_id' => $org->id, 'actor' => auth()->id()]);

            return redirect()
                ->route('global-admin.organizations.index')
                ->with('success', "Organization Network '{$org->name}' created successfully with limit of {$org->max_campuses} campuses.");

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to register Organization: ' . $e->getMessage());
        }
    }

    /**
     * Show form to edit Organization settings & quota.
     */
    public function edit(Organization $organization): View
    {
        $organization->load(['owner', 'institutes']);
        $principals = User::where('role', 'principal')->orderBy('name')->get();

        return view('global-admin.organizations.edit', compact('organization', 'principals'));
    }

    /**
     * Update Organization settings.
     */
    public function update(Request $request, Organization $organization): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255|unique:organizations,name,' . $organization->id,
            'max_campuses'  => 'required|integer|min:' . max(1, $organization->campus_count) . '|max:50',
            'owner_user_id' => 'nullable|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $organization->update([
                'name'          => trim($validated['name']),
                'max_campuses'  => (int)$validated['max_campuses'],
                'owner_user_id' => $request->input('owner_user_id') ?: null,
            ]);

            if ($request->filled('owner_user_id')) {
                User::where('id', $request->input('owner_user_id'))->update([
                    'organization_id'      => $organization->id,
                    'is_primary_principal' => true,
                ]);
            }

            DB::commit();
            Log::info('Global Admin: Updated Organization Network', ['org_id' => $organization->id, 'actor' => auth()->id()]);

            return redirect()
                ->route('global-admin.organizations.index')
                ->with('success', "Organization '{$organization->name}' updated successfully.");

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update Organization: ' . $e->getMessage());
        }
    }

    /**
     * Toggle Organization Active / Inactive Status.
     */
    public function toggleStatus(Organization $organization, \App\Services\InstitutePurgeService $purgeService): RedirectResponse
    {
        if ($organization->is_active) {
            $purgeService->deactivateOrganization($organization);
            $msg = "Services for organization network '{$organization->name}' have been temporarily paused (e.g. pending payment/conflict resolution). All member campus data remains 100% preserved.";
        } else {
            $purgeService->activateOrganization($organization);
            $msg = "Services for organization network '{$organization->name}' have been resumed! All member campuses and portals are now active.";
        }

        return back()->with('success', $msg);
    }

    /**
     * Delete Organization Network (Purges all linked campuses, data, and portals).
     */
    public function destroy(Organization $organization, \App\Services\InstitutePurgeService $purgeService): RedirectResponse
    {
        try {
            $name = $organization->name;
            $purgeService->purgeOrganization($organization);

            Log::warning('Global Admin: Deleted Organization Network and all linked campuses', ['org_name' => $name, 'actor' => auth()->id()]);

            return redirect()
                ->route('global-admin.organizations.index')
                ->with('success', "Organization Network '{$name}' and all member campuses, data, and portals have been permanently deleted.");

        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Failed to delete Organization: ' . $e->getMessage());
        }
    }
}
