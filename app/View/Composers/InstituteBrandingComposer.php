<?php

namespace App\View\Composers;

use App\Models\Institute;
use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InstituteBrandingComposer
{
    protected static ?object $cachedGlobalAdminBranding = null;
    protected static array $cachedTenantBranding = [];

    /**
     * Clear the static cache (useful for tests or tenant switches).
     */
    public static function clearCache(): void
    {
        static::$cachedGlobalAdminBranding = null;
        static::$cachedTenantBranding = [];
    }

    /**
     * Bind dynamic institute branding data to the view.
     */
    public function compose(View $view): void
    {
        $user = Auth::user();
        $viewName = $view->name();
        $port = request() ? (request()->getPort() ?? (int)($_SERVER['SERVER_PORT'] ?? 0)) : 0;
        
        // Global Admin Master Platform branding applies if:
        // 1) Authenticated user is Global Admin
        // 2) Route path is 'global-admin*' or 'globaladmin*' or named 'global-admin.*' or 'globaladmin.*'
        // 3) View is 'auth.global-admin-login' or inside 'global-admin.*'
        $isGlobalAdminRoute = (
            ($user && $user->isGlobalAdmin()) ||
            (request() && (request()->is('global-admin*') || request()->is('globaladmin*') || request()->routeIs('global-admin.*') || request()->routeIs('globaladmin.*'))) ||
            $viewName === 'auth.global-admin-login' ||
            str_starts_with($viewName, 'global-admin.')
        );

        if ($isGlobalAdminRoute) {
            if (static::$cachedGlobalAdminBranding) {
                $view->with('instituteBranding', static::$cachedGlobalAdminBranding);
                return;
            }

            $platformLogoPath = PlatformSetting::get('platform_logo_path');
            $logoUrl = null;
            if ($platformLogoPath && file_exists(public_path('storage/' . $platformLogoPath))) {
                $logoUrl = asset('storage/' . $platformLogoPath);
            } elseif (file_exists(public_path('images/uplyft-logo.png'))) {
                $logoUrl = asset('images/uplyft-logo.png');
            } elseif ($platformLogoPath) {
                $logoUrl = asset('storage/' . $platformLogoPath);
            } else {
                $logoUrl = asset('images/uplyft-logo.png');
            }

            $branding = (object) [
                'is_tenant'        => false,
                'institute_id'     => null,
                'name'             => 'UPLYFT',
                'slug'             => 'uplyft',
                'logo_url'         => $logoUrl,
                'icon_url'         => $logoUrl,
                'has_custom_logo'  => (bool) $logoUrl,
                'has_custom_icon'  => (bool) $logoUrl,
                'initial'          => 'U',
                'powered_by'       => 'TNT Innovations',
                'powered_by_title' => 'POWERED BY TNT INNOVATIONS',
                'bg_url'           => asset('images/default_campus_bg.jpg'),
            ];

            static::$cachedGlobalAdminBranding = $branding;
            $view->with('instituteBranding', $branding);
            return;
        }

        // Fast path for tenant views when not explicitly overridden per view
        $explicitInstitute = (isset($view->getData()['institute']) && $view->getData()['institute'] instanceof Institute) 
            ? $view->getData()['institute'] 
            : null;

        $cacheKey = $explicitInstitute ? ('inst_' . $explicitInstitute->id) : ('user_' . ($user?->id ?? 'guest') . '_sess_' . (session('active_institute_id') ?? session('tenant_institute_id') ?? 'def'));

        if (isset(static::$cachedTenantBranding[$cacheKey])) {
            $cached = static::$cachedTenantBranding[$cacheKey];
            $view->with('instituteBranding', $cached['branding']);
            $view->with('currencySymbol', $cached['currency']);
            $view->with('currency', $cached['currency']);
            return;
        }

        // ── Tenant Context Resolution (Principal, Student, Teacher Portals & Logins) ──
        $institute = null;

        // 1. Check if view has an explicit institute passed
        if (isset($view->getData()['institute']) && $view->getData()['institute'] instanceof Institute) {
            $institute = $view->getData()['institute'];
        }

        // 2. Check request parameter (e.g. ?institute=1 or ?institute_id=1 or ?slug=superior)
        if (!$institute && request()) {
            if (request()->has('institute_id')) {
                $institute = Institute::withoutGlobalScopes()->find(request()->get('institute_id'));
            } elseif (request()->has('institute')) {
                $instParam = request()->get('institute');
                $institute = is_numeric($instParam) 
                    ? Institute::withoutGlobalScopes()->find($instParam)
                    : Institute::withoutGlobalScopes()->where('slug', $instParam)->first();
            }
        }

        // 3. Check active session campus switch or user's active institute ID
        if (!$institute && session()->has('active_institute_id')) {
            $institute = Institute::withoutGlobalScopes()->find(session('active_institute_id'));
        }

        if (!$institute && session()->has('tenant_institute_id')) {
            $institute = Institute::withoutGlobalScopes()->find(session('tenant_institute_id'));
        }

        // 4. Check authenticated tenant user's active institute
        if (!$institute && $user && !$user->isGlobalAdmin()) {
            $activeId = method_exists($user, 'getActiveInstituteId') ? $user->getActiveInstituteId() : $user->institute_id;
            if ($activeId) {
                $institute = Institute::withoutGlobalScopes()->find($activeId);
            }
        }

        // 5. If user is a student without direct institute_id on user record, check student profile
        if (!$institute && $user && $user->isStudent()) {
            $student = $user->getStudentModel();
            if ($student && !empty($student->institute_id)) {
                $institute = Institute::withoutGlobalScopes()->find($student->institute_id);
            }
        }

        // 6. If no institute context is resolved (e.g. /login on master domain), default to Uplyft Platform branding
        if (!$institute) {
            $platformLogoPath = PlatformSetting::get('platform_logo_path');
            $logoUrl = null;
            if ($platformLogoPath && file_exists(public_path('storage/' . $platformLogoPath))) {
                $logoUrl = asset('storage/' . $platformLogoPath);
            } elseif (file_exists(public_path('images/uplyft-logo.png'))) {
                $logoUrl = asset('images/uplyft-logo.png');
            } elseif ($platformLogoPath) {
                $logoUrl = asset('storage/' . $platformLogoPath);
            } else {
                $logoUrl = asset('images/uplyft-logo.png');
            }

            $branding = (object) [
                'is_tenant'        => false,
                'institute_id'     => null,
                'name'             => 'Uplyft',
                'slug'             => 'uplyft',
                'logo_url'         => $logoUrl,
                'icon_url'         => $logoUrl,
                'has_custom_logo'  => (bool) $logoUrl,
                'has_custom_icon'  => (bool) $logoUrl,
                'initial'          => 'U',
                'powered_by'       => 'TNT Innovations',
                'powered_by_title' => 'POWERED BY TNT INNOVATIONS',
                'currency_symbol'  => 'PKR',
                'currency'         => 'PKR',
                'bg_url'           => asset('images/default_campus_bg.jpg'),
            ];

            static::$cachedTenantBranding[$cacheKey] = [
                'branding' => $branding,
                'currency' => 'PKR',
            ];

            $view->with('instituteBranding', $branding);
            $view->with('currencySymbol', 'PKR');
            $view->with('currency', 'PKR');
            return;
        }

        $setting = null;
        try {
            $setting = \App\Models\InstituteSetting::getForInstitute($institute->id);
        } catch (\Throwable $e) {
            // Fallback
        }
        $currencySymbol = $setting?->currency_symbol ?? 'PKR';

        $bgUrl = $institute->bg_url ?? asset('images/default_campus_bg.jpg');

        $defaultLogoUrl = asset('images/uplyft-logo.png');
        $resolvedLogoUrl = $institute->logo_url;
        if ($resolvedLogoUrl) {
            $parsedPath = parse_url($resolvedLogoUrl, PHP_URL_PATH);
            if (!file_exists(public_path(ltrim($parsedPath, '/')))) {
                $resolvedLogoUrl = $defaultLogoUrl;
            }
        } else {
            $resolvedLogoUrl = $defaultLogoUrl;
        }

        $resolvedIconUrl = $institute->icon_url;
        if ($resolvedIconUrl) {
            $parsedPath = parse_url($resolvedIconUrl, PHP_URL_PATH);
            if (!file_exists(public_path(ltrim($parsedPath, '/')))) {
                $resolvedIconUrl = $resolvedLogoUrl;
            }
        } else {
            $resolvedIconUrl = $resolvedLogoUrl;
        }

        $branding = (object) [
            'is_tenant'        => true,
            'institute_id'     => $institute->id,
            'name'             => $institute->name ?? 'Uplyft',
            'slug'             => $institute->slug ?? 'uplyft',
            'logo_url'         => $resolvedLogoUrl,
            'icon_url'         => $resolvedIconUrl,
            'has_custom_logo'  => (bool) $institute->logo_path,
            'has_custom_icon'  => (bool) ($institute->icon_path || $institute->logo_path),
            'initial'          => $institute->display_initial ?? 'U',
            'powered_by'       => 'TNT Innovations',
            'powered_by_title' => 'POWERED BY TNT INNOVATIONS',
            'currency_symbol'  => $currencySymbol,
            'currency'         => $currencySymbol,
            'bg_url'           => $bgUrl,
        ];

        static::$cachedTenantBranding[$cacheKey] = [
            'branding' => $branding,
            'currency' => $currencySymbol,
        ];

        $view->with('instituteBranding', $branding);
        $view->with('currencySymbol', $currencySymbol);
        $view->with('currency', $currencySymbol);
    }
}
