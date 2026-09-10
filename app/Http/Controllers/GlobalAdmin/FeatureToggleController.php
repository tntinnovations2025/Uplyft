<?php

namespace App\Http\Controllers\GlobalAdmin;

use App\Http\Controllers\Controller;
use App\Models\Institute;
use App\Models\InstituteFeatureToggle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class FeatureToggleController extends Controller
{
    /**
     * Show the feature toggle panel for a specific institute.
     * Master switchboard for Global Admins.
     */
    public function edit(Institute $institute): View
    {
        $toggles         = $institute->featureToggles ?? new InstituteFeatureToggle();
        $featureLabels   = InstituteFeatureToggle::$featureLabels;
        $featureKeys     = InstituteFeatureToggle::$featureKeys;
        $featureMetadata = InstituteFeatureToggle::$featureMetadata;

        return view('global-admin.toggles.edit', compact(
            'institute', 'toggles', 'featureLabels', 'featureKeys', 'featureMetadata'
        ));
    }

    /**
     * Update toggles for one institute (supports standard POST and dynamic AJAX toggle).
     */
    public function update(Request $request, Institute $institute): RedirectResponse|JsonResponse
    {
        $featureKeys = InstituteFeatureToggle::$featureKeys;

        // If it's a single AJAX key toggle
        if ($request->wantsJson() && $request->has('feature_key')) {
            $key = $request->input('feature_key');
            if (in_array($key, $featureKeys, true)) {
                $toggles = InstituteFeatureToggle::firstOrNew(['institute_id' => $institute->id]);
                $newState = $request->boolean('state');
                $toggles->{$key} = $newState;
                $toggles->last_updated_by = auth()->id();
                $toggles->last_updated_at = now();
                $toggles->save();

                $activeCount = collect($featureKeys)->filter(fn ($k) => $toggles->{$k})->count();

                return response()->json([
                    'success' => true,
                    'key' => $key,
                    'state' => $newState,
                    'active_count' => $activeCount,
                    'total_count' => count($featureKeys),
                    'message' => ($newState ? 'Activated ' : 'Deactivated ') . (InstituteFeatureToggle::$featureLabels[$key] ?? $key),
                ]);
            }
        }

        // Standard bulk form submission
        $rules = collect($featureKeys)->mapWithKeys(fn ($key) => [
            $key => 'boolean'
        ])->toArray();

        $validated = $request->validate($rules);

        $toggleData = collect($featureKeys)->mapWithKeys(fn ($key) => [
            $key => (bool) ($validated[$key] ?? false)
        ])->toArray();

        $toggleData['last_updated_by'] = auth()->id();
        $toggleData['last_updated_at'] = now();

        $toggles = InstituteFeatureToggle::updateOrCreate(
            ['institute_id' => $institute->id],
            $toggleData
        );

        Log::info('Global Admin: Feature toggles updated', [
            'institute_id' => $institute->id,
            'actor'        => auth()->id(),
            'changes'      => $toggleData,
        ]);

        if ($request->wantsJson()) {
            $activeCount = collect($featureKeys)->filter(fn ($k) => $toggles->{$k})->count();
            return response()->json([
                'success' => true,
                'active_count' => $activeCount,
                'total_count' => count($featureKeys),
                'message' => 'All modular features saved successfully!',
            ]);
        }

        return redirect()
            ->route('global-admin.institutes.toggles.edit', $institute)
            ->with('success', "SaaS feature toggles successfully updated for {$institute->name}.");
    }

    /**
     * Bulk enable features based on subscription tier preset.
     */
    public function applyTierDefaults(Request $request, Institute $institute): RedirectResponse|JsonResponse
    {
        $targetTier = $request->input('tier', $institute->subscription_tier);
        $defaults   = $this->tierDefaults($targetTier);

        $toggles = InstituteFeatureToggle::updateOrCreate(
            ['institute_id' => $institute->id],
            array_merge($defaults, [
                'last_updated_by' => auth()->id(),
                'last_updated_at' => now(),
            ])
        );

        if ($request->wantsJson()) {
            $activeCount = collect(InstituteFeatureToggle::$featureKeys)->filter(fn ($k) => $toggles->{$k})->count();
            return response()->json([
                'success' => true,
                'tier' => $targetTier,
                'toggles' => $defaults,
                'active_count' => $activeCount,
                'total_count' => count(InstituteFeatureToggle::$featureKeys),
                'message' => "Applied " . ucfirst($targetTier) . " preset defaults successfully!",
            ]);
        }

        return redirect()
            ->route('global-admin.institutes.toggles.edit', $institute)
            ->with('success', "Applied {$targetTier} tier defaults for {$institute->name}.");
    }

    // ── Tier default presets ─────────────────────────────────────────────
    private function tierDefaults(string $tier): array
    {
        $allKeys = InstituteFeatureToggle::$featureKeys;

        return match ($tier) {
            'basic' => [
                'principal_portal'     => true,
                'teacher_portal'       => true,
                'parent_portal'        => false,
                'staff_governance'     => true,
                'security_management'  => false,
                'registration_portals' => true,
                'master_directory'     => true,
                'fee_invoicing'        => false,
                'financial_accounts'   => false,
                'scholarships'         => false,
                'classes_sections'     => true,
                'subjects_catalog'     => true,
                'teacher_allocations'  => true,
                'faculty_hours'        => true,
                'rooms_facilities'     => true,
                'attendance_system'    => true,
                'timetable'            => false,
                'ai_bot'               => false,
                'practice_tests'       => false,
                'lms_content'          => false,
                'assessment_engine'    => false,
                'datesheet_manager'    => false,
                'exam_reports'         => false,
                'grading_normalizer'   => false,
                'sms_notifications'    => false,
            ],
            'standard' => [
                'principal_portal'     => true,
                'teacher_portal'       => true,
                'parent_portal'        => true,
                'staff_governance'     => true,
                'security_management'  => true,
                'registration_portals' => true,
                'master_directory'     => true,
                'fee_invoicing'        => true,
                'financial_accounts'   => true,
                'scholarships'         => true,
                'classes_sections'     => true,
                'subjects_catalog'     => true,
                'teacher_allocations'  => true,
                'faculty_hours'        => true,
                'rooms_facilities'     => true,
                'attendance_system'    => true,
                'timetable'            => true,
                'ai_bot'               => false,
                'practice_tests'       => false,
                'lms_content'          => true,
                'assessment_engine'    => true,
                'datesheet_manager'    => true,
                'exam_reports'         => true,
                'grading_normalizer'   => false,
                'sms_notifications'    => true,
            ],
            'premium', 'enterprise' => collect($allKeys)
                ->mapWithKeys(fn ($k) => [$k => true])
                ->toArray(),
            'all_off' => collect($allKeys)
                ->mapWithKeys(fn ($k) => [$k => false])
                ->toArray(),
            default => collect($allKeys)
                ->mapWithKeys(fn ($k) => [$k => true])
                ->toArray(),
        };
    }
}
