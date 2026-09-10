<?php

namespace App\Http\Controllers\GlobalAdmin;

use App\Http\Controllers\Controller;
use App\Models\Institute;
use App\Models\InstituteFeatureToggle;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_institutes'  => Institute::withoutGlobalScope(\App\Models\Scopes\TenantPrivacyScope::class)->count(),
            'active_institutes' => Institute::withoutGlobalScope(\App\Models\Scopes\TenantPrivacyScope::class)->where('is_active', true)->count(),
            'basic_plan'        => Institute::withoutGlobalScope(\App\Models\Scopes\TenantPrivacyScope::class)->where('subscription_tier', 'basic')->count(),
            'standard_plan'     => Institute::withoutGlobalScope(\App\Models\Scopes\TenantPrivacyScope::class)->where('subscription_tier', 'standard')->count(),
            'premium_plan'      => Institute::withoutGlobalScope(\App\Models\Scopes\TenantPrivacyScope::class)->where('subscription_tier', 'premium')->count(),
        ];

        $recentInstitutes = Institute::withoutGlobalScope(\App\Models\Scopes\TenantPrivacyScope::class)
            ->with('featureToggles')
            ->latest()
            ->take(5)
            ->get();

        return view('global-admin.dashboard', compact('stats', 'recentInstitutes'));
    }
}
