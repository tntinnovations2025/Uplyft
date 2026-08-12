<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the authenticated user belongs to a specific institute.
 *
 * Used in institute-scoped routes to verify that the user
 * cannot access another institute's data.
 */
class EnsureUserBelongsToInstitute
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        // Global admins bypass institute checks
        if ($user->isGlobalAdmin()) {
            return $next($request);
        }

        // Get institute_id from route parameter
        $routeInstituteId = $request->route('institute_id')
            ?? $request->route('institute')?->id
            ?? $request->input('institute_id');

        if ($routeInstituteId && (int) $user->institute_id !== (int) $routeInstituteId) {
            abort(403, 'You do not have access to this institute\'s resources.');
        }

        return $next($request);
    }
}
