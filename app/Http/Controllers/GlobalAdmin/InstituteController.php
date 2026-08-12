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
            ->with(['featureToggles'])
            ->withTrashed()               // Show soft-deleted too (with badge)
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('global-admin.institutes.index', compact('institutes'));
    }

    // ── Show create form ─────────────────────────────────────────────────
    public function create(): View
    {
        return view('global-admin.institutes.create', [
            'educationSystemLabels' => Institute::$educationSystemLabels,
        ]);
    }

    // ── Store new institute + create Principal master login ────────────
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // Institute fields
            'name'                    => 'required|string|max:255|unique:institutes,name',
            'subscription_tier'       => 'required|in:basic,standard,premium',
            'subscription_starts_at'  => 'nullable|date',
            'subscription_expires_at' => 'nullable|date|after_or_equal:subscription_starts_at',
            'contact_email'           => 'nullable|email|max:255',
            'contact_phone'           => 'nullable|string|max:30',
            'city'                    => 'nullable|string|max:100',
            'logo'                    => 'nullable|image|mimes:jpeg,png,svg|max:2048',
            'education_systems'       => 'nullable|array',
            'education_systems.*'     => 'in:matric,higher_sec,o_a_level,acca,other',

            // Principal master login fields
            'principal_name'                  => 'required|string|max:255',
            'principal_email'                 => 'required|email|max:255|unique:users,email',
            'principal_password'              => ['required', 'string', 'confirmed', new \App\Rules\StrongPassword],
            'principal_identifier'            => ['nullable', 'string', new \App\Rules\ValidIdentifier, 'unique:users,identifier'],
        ]);

        DB::beginTransaction();
        try {
            // Store logo
            $logoPath = null;
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('institute-logos', 'public');
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
                'is_active'               => true,
                'is_onboarded'            => false,
            ]);

            // 2. Create default feature toggles row (all OFF)
            InstituteFeatureToggle::create([
                'institute_id'    => $institute->id,
                'last_updated_by' => auth()->id(),
            ]);

            // 3. Create the Principal master login account
            $plainPassword = $validated['principal_password'];

            $principal = \App\Models\User::create([
                'name'         => $validated['principal_name'],
                'email'        => $validated['principal_email'],
                'identifier'   => $validated['principal_identifier'] ?? null,
                'password'     => \Illuminate\Support\Facades\Hash::make($plainPassword),
                'role'         => \App\Models\User::ROLE_PRINCIPAL,
                'institute_id' => $institute->id,
                'created_by'   => auth()->id(),
            ]);

            // 4. Send login credentials to the principal via email
            \Illuminate\Support\Facades\Mail::to($principal->email)
                ->send(new \App\Mail\PrincipalCredentialsMail($principal, $institute, $plainPassword));

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
        return view('global-admin.institutes.edit', [
            'institute'             => $institute,
            'educationSystemLabels' => Institute::$educationSystemLabels,
        ]);
    }

    // ── Update institute (name/logo/subscription only — no tenant data) ──
    public function update(Request $request, Institute $institute): RedirectResponse
    {
        $validated = $request->validate([
            'name'                    => 'required|string|max:255|unique:institutes,name,' . $institute->id,
            'subscription_tier'       => 'required|in:basic,standard,premium',
            'subscription_starts_at'  => 'nullable|date',
            'subscription_expires_at' => 'nullable|date|after_or_equal:subscription_starts_at',
            'contact_email'           => 'nullable|email|max:255',
            'contact_phone'           => 'nullable|string|max:30',
            'city'                    => 'nullable|string|max:100',
            'is_active'               => 'boolean',
            'logo'                    => 'nullable|image|mimes:jpeg,png,svg|max:2048',
            'education_systems'       => 'nullable|array',
            'education_systems.*'     => 'in:matric,higher_sec,o_a_level,acca,other',
        ]);

        DB::beginTransaction();
        try {
            if ($request->hasFile('logo')) {
                // Delete old logo
                if ($institute->logo_path) {
                    Storage::disk('public')->delete($institute->logo_path);
                }
                $validated['logo_path'] = $request->file('logo')->store('institute-logos', 'public');
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

    // ── Soft delete (deactivate) ─────────────────────────────────────────
    public function destroy(Institute $institute): RedirectResponse
    {
        $institute->update(['is_active' => false]);
        $institute->delete(); // Soft delete

        Log::warning('Global Admin: Institute deactivated', ['institute_id' => $institute->id, 'actor' => auth()->id()]);

        return redirect()
            ->route('global-admin.institutes.index')
            ->with('success', 'Institute "' . $institute->name . '" has been deactivated.');
    }

    // ── Restore soft-deleted institute ───────────────────────────────────
    public function restore(int $id): RedirectResponse
    {
        $institute = Institute::withTrashed()->findOrFail($id);
        $institute->restore();
        $institute->update(['is_active' => true]);

        return redirect()
            ->route('global-admin.institutes.index')
            ->with('success', 'Institute "' . $institute->name . '" has been restored.');
    }
}
