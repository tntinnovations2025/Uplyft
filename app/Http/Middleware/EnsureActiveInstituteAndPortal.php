<?php

namespace App\Http\Middleware;

use App\Models\Institute;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveInstituteAndPortal
{
    /**
     * Enforce that deactivated or deleted institutes/organizations have all linked portals disabled.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Global administrators always maintain platform access
        if ($user->isGlobalAdmin()) {
            return $next($request);
        }

        // 1. Check if user account is deactivated
        if (isset($user->is_active) && !$user->is_active) {
            return $this->terminateSession(
                $request,
                $user,
                'Your user account has been deactivated. Access to all portals is suspended.'
            );
        }

        // 2. Resolve institute
        $institute = $user->institute;
        if (!$institute && $user->current_institute_id) {
            $institute = Institute::withoutGlobalScopes()->withTrashed()->find($user->current_institute_id);
        }

        if ($institute) {
            if (!$institute->is_active || $institute->trashed()) {
                return $this->terminateSession(
                    $request,
                    $user,
                    "Services Temporarily Paused: Portal services for '{$institute->name}' are currently paused (e.g. pending payment resolution or administrative review). All institutional data is safely preserved and will resume once resolved."
                );
            }

            // Check parent organization
            if ($institute->organization_id && $institute->organization) {
                $org = $institute->organization;
                if (!$org->is_active || $org->trashed()) {
                    return $this->terminateSession(
                        $request,
                        $user,
                        "Services Temporarily Paused: Services for the organization network '{$org->name}' are currently paused. All campus data is safely preserved and will resume once resolved."
                    );
                }
            }
        }

        // 3. Check direct user organization link (e.g. Primary Principal Network Owner)
        if ($user->organization_id && $user->organization) {
            $org = $user->organization;
            if (!$org->is_active || $org->trashed()) {
                return $this->terminateSession(
                    $request,
                    $user,
                    "Services Temporarily Paused: Services for the organization network '{$org->name}' are currently paused. All campus data is safely preserved and will resume once resolved."
                );
            }
        }

        return $next($request);
    }

    /**
     * Invalidate session, log out user, and redirect with friendly error notice.
     */
    protected function terminateSession(Request $request, $user, string $message): Response
    {
        $role = $user->role ?? 'student';
        $port = $request->getPort();

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'status'  => 'error',
                'message' => $message,
            ], 403);
        }

        if ($port == 8001 || $role === 'principal') {
            return redirect()->route('principal.login')->with('error', $message);
        }

        return redirect()->route('login')->with('error', $message);
    }
}
