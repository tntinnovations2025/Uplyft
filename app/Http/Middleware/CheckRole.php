<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to restrict route access by user role.
 *
 * Usage in routes:
 *   ->middleware('role:global_admin')
 *   ->middleware('role:global_admin,principal')    // allows either role
 *   ->middleware('role:principal,teacher')
 */
class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Comma-separated list of allowed roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        // Global Admin has master bypass authority across all portals
        if ($user->isGlobalAdmin()) {
            return $next($request);
        }

        // Check if user has any of the allowed roles
        if (! in_array($user->role, $roles, true)) {
            // If accessing Global Admin portal without Global Admin role, redirect to Global Admin Login
            if (in_array('global_admin', $roles, true)) {
                return redirect()->route('global-admin.login')
                    ->with('error', 'Please sign in with Global Administrator credentials (admin@uplyft.com) to access the Global Admin Control Tower.');
            }

            // If accessing Principal portal without Principal role, redirect to Principal Login
            if (in_array('principal', $roles, true) && ! $user->isPrincipal()) {
                return redirect()->route('principal.login')
                    ->with('error', 'Please sign in with Principal credentials to access the Principal Portal.');
            }

            abort(403, 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
