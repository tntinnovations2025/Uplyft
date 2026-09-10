<?php

namespace App\Http\Controllers\GlobalAdmin;

use App\Http\Controllers\Controller;
use App\Models\Institute;
use App\Models\InstituteFeatureToggle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class InstituteController extends Controller
{
    // ── List all institutes ───────────────────────────────────────────────
    public function index(): View
    {
        $institutes = Institute::withoutGlobalScope(\App\Models\Scopes\TenantPrivacyScope::class)
            ->with(['featureToggles', 'organization', 'principals'])
            ->withTrashed()               // Show soft-deleted too (with badge)
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('global-admin.institutes.index', compact('institutes'));
    }

    // ── Show create form ─────────────────────────────────────────────────
    public function create(): View
    {
        $organizations = \App\Models\Organization::with(['owner', 'institutes'])->orderBy('name')->get();

        return view('global-admin.institutes.create', [
            'educationSystemLabels' => Institute::$educationSystemLabels,
            'organizations'         => $organizations,
        ]);
    }

    // ── Store new institute + create Principal master login ────────────
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // Registration mode
            'registration_type'       => 'required|in:single,organization',
            'organization_mode'       => 'nullable|required_if:registration_type,organization|in:new,existing',
            'organization_id'         => 'nullable|required_if:organization_mode,existing|exists:organizations,id',
            'organization_name'       => 'nullable|required_if:organization_mode,new|string|max:255',
            'max_campuses'            => 'nullable|integer|min:1|max:50',
            'assign_existing_owner'   => 'nullable|boolean',

            // Institute fields
            'name'                    => 'required|string|max:255|unique:institutes,name',
            'subscription_tier'       => 'required|in:basic,standard,premium',
            'subscription_starts_at'  => 'nullable|date',
            'subscription_expires_at' => 'nullable|date|after_or_equal:subscription_starts_at',
            'contact_email'           => 'nullable|email|max:255',
            'contact_phone'           => 'nullable|string|max:30',
            'city'                    => 'nullable|string|max:100',
            'logo'                    => 'nullable|image|mimes:jpeg,jpg,png,svg,webp|max:2048',
            'icon'                    => 'nullable|image|mimes:jpeg,jpg,png,svg,webp|max:2048',
            'education_systems'       => 'nullable|array',
            'education_systems.*'     => 'in:matric,higher_sec,o_a_level,acca,other',

            // Principal master login fields
            'principal_name'          => 'required_unless:assign_existing_owner,1|nullable|string|max:255',
            'principal_email'         => 'required_unless:assign_existing_owner,1|nullable|email|max:255|unique:users,email',
            'principal_password'      => ['required_unless:assign_existing_owner,1', 'nullable', 'string', 'confirmed', new \App\Rules\StrongPassword],
            'principal_identifier'    => ['nullable', 'string', new \App\Rules\ValidIdentifier, 'unique:users,identifier'],
        ]);

        DB::beginTransaction();
        try {
            // Store logo & icon (supports both direct file upload and circular cropped base64)
            $logoPath = null;
            $iconPath = null;

            if ($request->filled('cropped_logo') && str_starts_with($request->input('cropped_logo'), 'data:image')) {
                $dataUri = $request->input('cropped_logo');
                if (str_contains($dataUri, ',')) {
                    [$meta, $encoded] = explode(',', $dataUri, 2);
                    $imageData = base64_decode($encoded);
                    if ($imageData !== false) {
                        $filename = 'institute-logos/logo_' . time() . '_' . \Illuminate\Support\Str::random(8) . '.png';
                        Storage::disk('public')->put($filename, $imageData);
                        $logoPath = $filename;
                        $iconPath = $filename;
                    }
                }
            } elseif ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('institute-logos', 'public');
                $iconPath = $logoPath;
            }

            if ($request->hasFile('icon')) {
                $iconPath = $request->file('icon')->store('institute-logos', 'public');
            }

            // Organization resolution & Quota Check
            $organization = null;
            if ($validated['registration_type'] === 'organization') {
                $maxCampuses = $request->filled('max_campuses') ? (int)$request->input('max_campuses') : 3;

                if ($validated['organization_mode'] === 'new') {
                    $organization = \App\Models\Organization::create([
                        'name'         => trim($validated['organization_name']),
                        'max_campuses' => $maxCampuses,
                    ]);
                } else {
                    $organization = \App\Models\Organization::find($validated['organization_id']);
                    if ($request->filled('max_campuses')) {
                        $organization->update(['max_campuses' => $maxCampuses]);
                    }

                    // Enforce campus quota limit
                    if (!$organization->canAddMoreCampuses()) {
                        return back()->withInput()->with('error', "Organization '{$organization->name}' has reached its maximum allowed campus limit ({$organization->campus_usage_text}). Increase the campus limit quota to add another campus.");
                    }
                }
            }

            // 1. Create the Institute
            $institute = Institute::create([
                'name'                    => $validated['name'],
                'subscription_tier'       => $validated['subscription_tier'],
                'subscription_starts_at'  => $validated['subscription_starts_at'] ?? null,
                'subscription_expires_at' => $validated['subscription_expires_at'] ?? null,
                'contact_email'           => $validated['contact_email'] ?? null,
                'contact_phone'           => $validated['contact_phone'] ?? null,
                'city'                    => $validated['city'] ?? null,
                'education_systems'       => $validated['education_systems'] ?? [],
                'logo_path'               => $logoPath,
                'icon_path'               => $iconPath,
                'organization_id'         => $organization?->id,
                'is_active'               => true,
                'is_onboarded'            => false,
            ]);

            // 2. Create default feature toggles row with all standard modules active
            InstituteFeatureToggle::create([
                'institute_id'         => $institute->id,
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
                'last_updated_by'      => auth()->id(),
            ]);

            // 3. Seed Standard Role Authority Templates (Teacher, Admin, Accountant, Coordinator)
            \App\Models\RoleDefaultAuthority::seedDefaultsForInstitute($institute->id);

            // 4. Resolve or Create Primary Principal Account
            $assignExisting = $request->boolean('assign_existing_owner') && $organization && $organization->owner_user_id;

            if ($assignExisting) {
                $principal = \App\Models\User::find($organization->owner_user_id);
                $principal->update([
                    'is_primary_principal' => true,
                    'organization_id'      => $organization->id,
                ]);
            } else {
                $plainPassword = $validated['principal_password'];

                $principal = \App\Models\User::create([
                    'name'                 => $validated['principal_name'],
                    'email'                => $validated['principal_email'],
                    'identifier'           => $validated['principal_identifier'] ?? null,
                    'password'             => \Illuminate\Support\Facades\Hash::make($plainPassword),
                    'role'                 => \App\Models\User::ROLE_PRINCIPAL,
                    'institute_id'         => $institute->id,
                    'is_primary_principal' => true,
                    'organization_id'      => $organization?->id,
                    'created_by'           => auth()->id(),
                ]);

                if ($organization && !$organization->owner_user_id) {
                    $organization->update(['owner_user_id' => $principal->id]);
                }

                // Send login credentials via email
                try {
                    \Illuminate\Support\Facades\Mail::to($principal->email)
                        ->send(new \App\Mail\PrincipalCredentialsMail($principal, $institute, $plainPassword));
                } catch (\Throwable $mailErr) {
                    Log::warning('Principal Credentials Email Warning: ' . $mailErr->getMessage());
                }
            }

            DB::commit();
            Log::info('Global Admin: Institute + Principal created', [
                'institute_id' => $institute->id,
                'principal_id' => $principal->id,
                'actor'        => auth()->id(),
            ]);

            return redirect()
                ->route('global-admin.institutes.show', $institute)
                ->with('success', 'Institute "' . $institute->name . '" registered successfully. Principal login credentials sent to ' . $principal->email . '.');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Institute creation failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to create institute. ' . $e->getMessage());
        }
    }

    // ── Show single institute ─────────────────────────────────────────────
    public function show(Institute $institute): View
    {
        $institute->load(['featureToggles', 'principals']);
        $featureLabels = InstituteFeatureToggle::$featureLabels;

        return view('global-admin.institutes.show', compact('institute', 'featureLabels'));
    }

    // ── Show edit form ───────────────────────────────────────────────────
    public function edit(Institute $institute): View
    {
        $organizations = \App\Models\Organization::with(['owner', 'institutes'])->orderBy('name')->get();

        return view('global-admin.institutes.edit', [
            'institute'             => $institute,
            'educationSystemLabels' => Institute::$educationSystemLabels,
            'organizations'         => $organizations,
        ]);
    }

    // ── Update institute ──
    public function update(Request $request, Institute $institute): RedirectResponse
    {
        $validated = $request->validate([
            'name'                         => 'required|string|max:255|unique:institutes,name,' . $institute->id,
            'subscription_tier'            => 'required|in:basic,standard,premium',
            'subscription_starts_at'       => 'nullable|date',
            'subscription_expires_at'      => 'nullable|date|after_or_equal:subscription_starts_at',
            'contact_email'                => 'nullable|email|max:255',
            'contact_phone'                => 'nullable|string|max:30',
            'city'                         => 'nullable|string|max:100',
            'is_active'                    => 'boolean',
            'logo'                         => 'nullable|image|mimes:jpeg,jpg,png,svg,webp|max:2048',
            'icon'                         => 'nullable|image|mimes:jpeg,jpg,png,svg,webp|max:2048',
            'remove_logo'                  => 'nullable|boolean',
            'remove_icon'                  => 'nullable|boolean',
            'education_systems'            => 'nullable|array',
            'education_systems.*'          => 'in:matric,higher_sec,o_a_level,acca,other',
            'organization_assignment_type' => 'nullable|in:none,existing,new',
            'organization_id'              => 'nullable|required_if:organization_assignment_type,existing|exists:organizations,id',
            'new_organization_name'        => 'nullable|required_if:organization_assignment_type,new|string|max:255',
            'new_max_campuses'             => 'nullable|integer|min:1|max:50',
        ]);

        DB::beginTransaction();
        try {
            // Handle logo remove, cropped base64, or direct file upload
            if ($request->boolean('remove_logo')) {
                if ($institute->logo_path) {
                    Storage::disk('public')->delete($institute->logo_path);
                }
                if ($institute->icon_path) {
                    Storage::disk('public')->delete($institute->icon_path);
                }
                $validated['logo_path'] = null;
                $validated['icon_path'] = null;
            } elseif ($request->filled('cropped_logo') && str_starts_with($request->input('cropped_logo'), 'data:image')) {
                $dataUri = $request->input('cropped_logo');
                if (str_contains($dataUri, ',')) {
                    [$meta, $encoded] = explode(',', $dataUri, 2);
                    $imageData = base64_decode($encoded);
                    if ($imageData !== false) {
                        $filename = 'institute-logos/logo_' . $institute->id . '_' . time() . '.png';
                        if ($institute->logo_path) {
                            Storage::disk('public')->delete($institute->logo_path);
                        }
                        if ($institute->icon_path && $institute->icon_path !== $institute->logo_path) {
                            Storage::disk('public')->delete($institute->icon_path);
                        }
                        Storage::disk('public')->put($filename, $imageData);
                        $validated['logo_path'] = $filename;
                        $validated['icon_path'] = $filename;
                    }
                }
            } elseif ($request->hasFile('logo')) {
                if ($institute->logo_path) {
                    Storage::disk('public')->delete($institute->logo_path);
                }
                $validated['logo_path'] = $request->file('logo')->store('institute-logos', 'public');
                $validated['icon_path'] = $validated['logo_path'];
            }

            // Handle optional icon remove or update
            if ($request->boolean('remove_icon')) {
                if ($institute->icon_path && $institute->icon_path !== $institute->logo_path) {
                    Storage::disk('public')->delete($institute->icon_path);
                }
                $validated['icon_path'] = null;
            } elseif ($request->hasFile('icon')) {
                if ($institute->icon_path && $institute->icon_path !== $institute->logo_path) {
                    Storage::disk('public')->delete($institute->icon_path);
                }
                $validated['icon_path'] = $request->file('icon')->store('institute-logos', 'public');
            }

            // Ensure boolean and array fields are properly formatted
            $validated['is_active'] = $request->boolean('is_active');
            $validated['education_systems'] = $request->input('education_systems', []);
            if ($request->filled('country')) {
                $validated['country'] = $request->input('country');
            }

            // Handle Organization Network Assignment / Conversion
            if ($request->filled('organization_assignment_type')) {
                $type = $request->input('organization_assignment_type');

                if ($type === 'none') {
                    $validated['organization_id'] = null;
                } elseif ($type === 'new') {
                    $primaryPrincipal = $institute->principals()->where('is_primary_principal', true)->first()
                                      ?? $institute->principals()->first();

                    $newOrg = \App\Models\Organization::create([
                        'name'         => trim($request->input('new_organization_name')),
                        'max_campuses' => (int)$request->input('new_max_campuses', 3),
                        'owner_user_id'=> $primaryPrincipal?->id,
                    ]);
                    $validated['organization_id'] = $newOrg->id;

                    if ($primaryPrincipal) {
                        $primaryPrincipal->update([
                            'organization_id'      => $newOrg->id,
                            'is_primary_principal' => true,
                        ]);
                    }
                } elseif ($type === 'existing') {
                    $targetOrg = \App\Models\Organization::find($request->input('organization_id'));
                    if ($targetOrg) {
                        if ($institute->organization_id != $targetOrg->id && !$targetOrg->canAddMoreCampuses()) {
                            DB::rollBack();
                            return back()->withInput()->with('error', "Organization '{$targetOrg->name}' has reached its maximum campus limit ({$targetOrg->campus_usage_text}). Please increase the campus quota limit before attaching another campus.");
                        }
                        $validated['organization_id'] = $targetOrg->id;

                        if ($primaryPrincipal = ($institute->principals()->where('is_primary_principal', true)->first() ?? $institute->principals()->first())) {
                            $primaryPrincipal->update([
                                'organization_id' => $targetOrg->id,
                            ]);
                        }
                    }
                }
            }

            $institute->update($validated);

            DB::commit();
            Log::info('Global Admin: Institute updated', ['institute_id' => $institute->id, 'actor' => auth()->id()]);

            return redirect()
                ->route('global-admin.institutes.show', $institute)
                ->with('success', 'Institute updated successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    // ── Toggle Institute Active / Inactive Status (Portal Suspension) ─────
    public function toggleStatus(Institute $institute, \App\Services\InstitutePurgeService $purgeService): RedirectResponse
    {
        if ($institute->is_active) {
            $purgeService->deactivateInstitute($institute);
            $msg = "Services for '{$institute->name}' have been temporarily paused (e.g. pending payment resolution). Portal access is suspended, but all institutional data remains 100% preserved.";
        } else {
            $purgeService->activateInstitute($institute);
            $msg = "Services for '{$institute->name}' have been resumed! All linked portals (Principal, Faculty, Student, Staff, and LMS) are now active.";
        }

        return back()->with('success', $msg);
    }

    // ── Delete institute (Purges all data and portals) ───────────────────────
    public function destroy(Institute $institute, \App\Services\InstitutePurgeService $purgeService): RedirectResponse
    {
        $name = $institute->name;
        $purgeService->purgeInstitute($institute);

        return redirect()
            ->route('global-admin.institutes.index')
            ->with('success', "Institute '{$name}' and all associated data, users, and portals have been permanently deleted.");
    }

    // ── Restore soft-deleted institute ───────────────────────────────────
    public function restore(int $id, \App\Services\InstitutePurgeService $purgeService): RedirectResponse
    {
        $institute = Institute::withTrashed()->findOrFail($id);
        $institute->restore();
        $purgeService->activateInstitute($institute);

        return redirect()
            ->route('global-admin.institutes.index')
            ->with('success', 'Institute "' . $institute->name . '" has been restored and portals activated.');
    }

    // ── Permanently Purge soft-deleted institute ──────────────────────────
    public function forceDestroy(int $id, \App\Services\InstitutePurgeService $purgeService): RedirectResponse
    {
        $institute = Institute::withTrashed()->find($id);
        if (!$institute) {
            return redirect()
                ->route('global-admin.institutes.index')
                ->with('info', "Institute #{$id} has already been permanently deleted.");
        }
        $name = $institute->name;
        $purgeService->purgeInstitute($institute);

        return redirect()
            ->route('global-admin.institutes.index')
            ->with('success', "Institute '{$name}' and all associated data and portals have been permanently deleted.");
    }
}
