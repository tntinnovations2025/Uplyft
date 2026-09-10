<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\Institute;
use App\Models\InstituteFeatureToggle;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class OrganizationCampusController extends Controller
{
    /**
     * Display list of campuses registered under the Principal's Organization Network.
     */
    public function index(): View|RedirectResponse
    {
        $user = auth()->user();

        $orgId = $user->organization_id;
        if (!$orgId && $user->institute_id) {
            $userInst = Institute::withoutGlobalScopes()->find($user->institute_id);
            $orgId = $userInst?->organization_id;
        }

        if (! $user->isPrincipal()) {
            abort(403);
        }

        if (!$orgId) {
            return redirect()->route('principal.dashboard')
                ->with('error', 'Only multi-campus Organization accounts can access Organization Campus Management.');
        }

        $organization = Organization::with(['institutes' => function($q) {
            $q->withoutGlobalScopes();
        }])->find($orgId);

        if (!$organization) {
            return redirect()->route('principal.dashboard')
                ->with('error', 'Organization network not found.');
        }

        $activeCampusId = method_exists($user, 'getActiveInstituteId') ? $user->getActiveInstituteId() : $user->institute_id;

        return view('principal.organization.campuses.index', [
            'organization'   => $organization,
            'campuses'       => $organization->institutes,
            'activeCampusId' => $activeCampusId,
        ]);
    }

    /**
     * Show form for Principal to onboard a new campus under their Organization.
     */
    public function create(): View|RedirectResponse
    {
        $user = auth()->user();

        $orgId = $user->organization_id;
        if (!$orgId && $user->institute_id) {
            $userInst = Institute::withoutGlobalScopes()->find($user->institute_id);
            $orgId = $userInst?->organization_id;
        }

        if (! $user->isPrincipal()) {
            abort(403);
        }

        if (!$orgId) {
            return redirect()->route('principal.dashboard')
                ->with('error', 'Only multi-campus Organization accounts can onboard additional campuses.');
        }

        $organization = Organization::with(['institutes' => function($q) {
            $q->withoutGlobalScopes();
        }])->find($orgId);

        if (!$organization) {
            return redirect()->route('principal.dashboard')
                ->with('error', 'Organization network not found.');
        }

        // Enforce Quota Check: Do not let them make new ones if max campuses limit reached!
        if (!$organization->canAddMoreCampuses()) {
            return redirect()->route('principal.organization.campuses.index')
                ->with('error', "🔒 Campus Quota Limit Reached! Your organization network '{$organization->name}' has reached its maximum allowed limit of {$organization->max_campuses} campuses. Contact Global Admin to upgrade your campus limit quota.");
        }

        return view('principal.organization.campuses.create', [
            'organization'          => $organization,
            'educationSystemLabels' => Institute::$educationSystemLabels,
        ]);
    }

    /**
     * Store a newly created campus under Principal's Organization Network.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $orgId = $user->organization_id;
        if (!$orgId && $user->institute_id) {
            $userInst = Institute::withoutGlobalScopes()->find($user->institute_id);
            $orgId = $userInst?->organization_id;
        }

        if (! $user->isPrincipal()) {
            abort(403);
        }

        if (!$orgId) {
            return redirect()->route('principal.dashboard')
                ->with('error', 'Unauthorized: Only Organization accounts can onboard additional campuses.');
        }

        $organization = Organization::with('institutes')->find($orgId);

        if (!$organization) {
            return redirect()->route('principal.dashboard')->with('error', 'Organization not found.');
        }

        // STRICT QUOTA ENFORCEMENT: Block creation if max limit reached!
        if (!$organization->canAddMoreCampuses()) {
            return redirect()->route('principal.organization.campuses.index')
                ->with('error', "🔒 Campus Creation Blocked: Organization '{$organization->name}' has reached its maximum campus limit of {$organization->max_campuses} campuses. Contact Global Admin to upgrade your limit.");
        }

        $validated = $request->validate([
            'name'                    => 'required|string|max:255|unique:institutes,name',
            'city'                    => 'nullable|string|max:100',
            'contact_email'           => 'nullable|email|max:255',
            'contact_phone'           => 'nullable|string|max:30',
            'education_systems'       => 'nullable|array',
            'education_systems.*'     => 'in:matric,higher_sec,o_a_level,acca,other',
        ]);

        DB::beginTransaction();
        try {
            // 1. Create the new Campus under this Organization
            $campus = Institute::create([
                'name'                    => trim($validated['name']),
                'subscription_tier'       => $user->institute?->subscription_tier ?? 'standard',
                'subscription_starts_at'  => now(),
                'subscription_expires_at' => $user->institute?->subscription_expires_at,
                'contact_email'           => $validated['contact_email'] ?? $user->email,
                'contact_phone'           => $validated['contact_phone'] ?? null,
                'city'                    => $validated['city'] ?? null,
                'education_systems'       => $validated['education_systems'] ?? [],
                'organization_id'         => $organization->id,
                'is_active'               => true,
                'is_onboarded'            => true,
            ]);

            // 2. Create feature toggles for new campus
            InstituteFeatureToggle::create([
                'institute_id'         => $campus->id,
                'ai_bot'               => true,
                'attendance_system'    => true,
                'assessment_engine'    => true,
                'fee_invoicing'        => true,
                'timetable'            => true,
                'registration_portals' => true,
                'lms_content'          => true,
                'grading_normalizer'   => true,
                'teacher_portal'       => true,
                'principal_portal'     => true,
                'parent_portal'        => true,
                'sms_notifications'    => true,
                'scholarships'         => true,
                'master_directory'     => true,
                'rooms_facilities'     => true,
                'classes_sections'     => true,
                'subjects_catalog'     => true,
                'teacher_allocations'  => true,
                'last_updated_by'      => $user->id,
            ]);

            // 3. Seed default roles for new campus
            \App\Models\RoleDefaultAuthority::seedDefaultsForInstitute($campus->id);

            // 4. Automatically switch active session to newly created campus profile
            session(['active_institute_id' => $campus->id]);
            if (Schema::hasColumn('users', 'current_institute_id')) {
                $user->update(['current_institute_id' => $campus->id]);
            }

            DB::commit();
            Log::info('Principal onboarded new campus under organization', [
                'campus_id' => $campus->id,
                'org_id'    => $organization->id,
                'user_id'   => $user->id,
            ]);

            return redirect()
                ->route('principal.organization.campuses.index')
                ->with('success', "✨ Campus '{$campus->name}' successfully onboarded! Switched to your new campus profile ({$organization->campus_usage_text}).");

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to onboard campus: ' . $e->getMessage());
        }
    }

    /**
     * Remove / Delete a campus profile from Principal's Organization Network.
     */
    public function destroy(int $campusId): RedirectResponse
    {
        $user = auth()->user();

        $orgId = $user->organization_id;
        if (!$orgId && $user->institute_id) {
            $userInst = Institute::withoutGlobalScopes()->find($user->institute_id);
            $orgId = $userInst?->organization_id;
        }

        if (! $user->isPrincipal()) {
            abort(403);
        }

        if (!$orgId) {
            return redirect()->route('principal.dashboard')
                ->with('error', 'Unauthorized action.');
        }

        $organization = Organization::with(['institutes' => function($q) {
            $q->withoutGlobalScopes();
        }])->find($orgId);

        $campus = Institute::withoutGlobalScopes()->find($campusId);

        if (!$campus || $campus->organization_id !== $organization->id) {
            return redirect()->route('principal.organization.campuses.index')
                ->with('error', 'Campus profile not found in your organization network.');
        }

        // Prevent removing if it's the only remaining campus profile
        if ($organization->institutes->count() <= 1) {
            return redirect()->route('principal.organization.campuses.index')
                ->with('error', '⚠️ Cannot remove the primary campus profile. Organization network must have at least one campus profile.');
        }

        // If deleting current active campus profile, switch active profile to another campus first
        $activeCampusId = method_exists($user, 'getActiveInstituteId') ? $user->getActiveInstituteId() : $user->institute_id;
        if ((int)$activeCampusId === (int)$campus->id) {
            $remainingCampus = $organization->institutes->where('id', '!=', $campus->id)->first();
            if ($remainingCampus) {
                session(['active_institute_id' => $remainingCampus->id]);
                if (Schema::hasColumn('users', 'current_institute_id')) {
                    $user->update(['current_institute_id' => $remainingCampus->id]);
                }
            }
        }

        $campusName = $campus->name;
        $campus->delete();

        Log::info('Principal removed campus profile from organization', [
            'campus_id' => $campusId,
            'org_id'    => $organization->id,
            'user_id'   => $user->id,
        ]);

        return redirect()->route('principal.organization.campuses.index')
            ->with('success', "🗑️ Campus '{$campusName}' removed from your organization network successfully.");
    }
}
