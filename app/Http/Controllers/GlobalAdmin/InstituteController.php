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

    // ── Store new institute ──────────────────────────────────────────────
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
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
        ]);

        DB::beginTransaction();
        try {
            // Store logo
            $logoPath = null;
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('institute-logos', 'public');
            }

            $institute = Institute::create([
                ...$validated,
                'logo_path'   => $logoPath,
                'is_active'   => true,
                'is_onboarded'=> false,
            ]);

            // Create default feature toggles row (all OFF)
            InstituteFeatureToggle::create([
                'institute_id'   => $institute->id,
                'last_updated_by'=> auth()->id(),
            ]);

            DB::commit();
            Log::info('Global Admin: Institute created', ['institute_id' => $institute->id, 'actor' => auth()->id()]);

            return redirect()
                ->route('global-admin.institutes.show', $institute)
                ->with('success', 'Institute "' . $institute->name . '" registered successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Institute creation failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to create institute. Please try again.');
        }
    }

    // ── Show single institute ─────────────────────────────────────────────
    public function show(Institute $institute): View
    {
        $institute->load(['featureToggles']);
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
