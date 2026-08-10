<?php

namespace App\Http\Controllers\GlobalAdmin;

use App\Http\Controllers\Controller;
use App\Models\Institute;
use App\Models\InstituteFeatureToggle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class FeatureToggleController extends Controller
{
    /**
     * Show the feature toggle panel for a specific institute.
     * This is the master switch board.
     */
    public function edit(Institute $institute): View
    {
        $toggles       = $institute->featureToggles ?? new InstituteFeatureToggle();
        $featureLabels = InstituteFeatureToggle::$featureLabels;
        $featureKeys   = InstituteFeatureToggle::$featureKeys;

        return view('global-admin.toggles.edit', compact(
            'institute', 'toggles', 'featureLabels', 'featureKeys'
        ));
    }

    /**
     * Update toggles for one institute.
     * Only validated feature keys are accepted (no mass-assignment risk).
     */
    public function update(Request $request, Institute $institute): RedirectResponse
    {
        $featureKeys = InstituteFeatureToggle::$featureKeys;

        // Build validation rules dynamically from known feature keys
        $rules = collect($featureKeys)->mapWithKeys(fn ($key) => [
            $key => 'boolean'
        ])->toArray();

        $validated = $request->validate($rules);

        // Normalize: unchecked checkboxes are absent from POST → treat as false
        $toggleData = collect($featureKeys)->mapWithKeys(fn ($key) => [
            $key => (bool) ($validated[$key] ?? false)
        ])->toArray();

        $toggleData['last_updated_by'] = auth()->id();
        $toggleData['last_updated_at'] = now();

        // updateOrCreate ensures the row exists even if it was never created
        $toggles = InstituteFeatureToggle::updateOrCreate(
            ['institute_id' => $institute->id],
            $toggleData
        );

        Log::info('Global Admin: Feature toggles updated', [
            'institute_id' => $institute->id,
            'actor'        => auth()->id(),
            'changes'      => $toggleData,
        ]);

        return redirect()
            ->route('global-admin.institutes.show', $institute)
            ->with('success', 'Feature toggles updated for ' . $institute->name . '.');
    }

    /**
     * Bulk enable features based on subscription tier preset.
     * Calling this applies the standard defaults for a given plan.
     */
    public function applyTierDefaults(Institute $institute): RedirectResponse
    {
        $defaults = $this->tierDefaults($institute->subscription_tier);

        InstituteFeatureToggle::updateOrCreate(
            ['institute_id' => $institute->id],
            array_merge($defaults, [
                'last_updated_by' => auth()->id(),
                'last_updated_at' => now(),
            ])
        );

        return redirect()
            ->route('global-admin.institutes.show', $institute)
            ->with('success', "Applied {$institute->subscription_tier} tier defaults.");
    }

    // ── Tier default presets ─────────────────────────────────────────────
    private function tierDefaults(string $tier): array
    {
        return match ($tier) {
            'basic' => [
                'attendance_system'    => true,
                'registration_portals' => true,
                'teacher_portal'       => true,
                'principal_portal'     => true,
                // Everything else stays OFF
                'ai_bot'               => false,
                'assessment_engine'    => false,
                'fee_invoicing'        => false,
                'timetable'            => false,
                'lms_content'          => false,
                'grading_normalizer'   => false,
                'parent_portal'        => false,
                'sms_notifications'    => false,
            ],
            'standard' => [
                'attendance_system'    => true,
                'registration_portals' => true,
                'teacher_portal'       => true,
                'principal_portal'     => true,
                'assessment_engine'    => true,
                'fee_invoicing'        => true,
                'timetable'            => true,
                'lms_content'          => true,
                // Premium-only OFF
                'ai_bot'               => false,
                'grading_normalizer'   => false,
                'parent_portal'        => false,
                'sms_notifications'    => false,
            ],
            'premium' => collect(InstituteFeatureToggle::$featureKeys)
                ->mapWithKeys(fn ($k) => [$k => true])
                ->toArray(),
            default => [],
        };
    }
}
