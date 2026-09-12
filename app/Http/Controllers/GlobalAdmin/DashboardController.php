<?php

namespace App\Http\Controllers\GlobalAdmin;

use App\Http\Controllers\Controller;
use App\Models\Institute;
use App\Models\Scopes\TenantPrivacyScope;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Global Admin Platform Overview Dashboard.
     * Hardened against Tenant Privacy Scope bypass (BUG-ADMIN-002).
     */
    public function index(Request $request): View
    {
        // 1. Verify if an active, authorized emergency override token and configuration exist
        $overrideConfigured = config('app.global_admin_emergency_override', false)
            || config('uplyft.global_admin_emergency_override', false);

        $emergencyOverrideActive = session()->has('emergency_override_token') && $overrideConfigured;

        // 2. Base Query: Strict TenantPrivacyScope enforced by default
        $query = Institute::query();

        if ($emergencyOverrideActive) {
            // Explicit audited override: bypass the scope only when authenticated token is present
            $query = Institute::withoutGlobalScope(TenantPrivacyScope::class);
        }

        $stats = [
            'total_institutes'  => (clone $query)->count(),
            'active_institutes' => (clone $query)->where('is_active', true)->count(),
            'basic_plan'        => (clone $query)->where('subscription_tier', 'basic')->count(),
            'standard_plan'     => (clone $query)->where('subscription_tier', 'standard')->count(),
            'premium_plan'      => (clone $query)->where('subscription_tier', 'premium')->count(),
            'emergency_override_active' => $emergencyOverrideActive,
        ];

        $recentInstitutes = (clone $query)
            ->with('featureToggles')
            ->latest()
            ->take(5)
            ->get();

        return view('global-admin.dashboard', compact('stats', 'recentInstitutes', 'emergencyOverrideActive'));
    }
}
