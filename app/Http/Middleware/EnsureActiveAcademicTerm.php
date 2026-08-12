<?php

namespace App\Http\Middleware;

use App\Models\AcademicTerm;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveAcademicTerm
{
    /**
     * Handle an incoming request.
     * Checks if the user's institute has an active academic term.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Global admins bypass term restriction
        if ($user && $user->isGlobalAdmin()) {
            return $next($request);
        }

        if ($user && $user->institute_id) {
            $hasActiveTerm = AcademicTerm::where('institute_id', $user->institute_id)
                ->where('is_active', true)
                ->exists();

            if (!$hasActiveTerm) {
                // Allow access to academic terms configuration page itself
                if ($request->routeIs('principal.academic-terms.*')) {
                    return $next($request);
                }

                return redirect()
                    ->route('principal.academic-terms.index')
                    ->with('warning', '⚠️ Action Required: Please create and set an Active Academic Term before managing classes, sections, timetables, or staff assignments.');
            }
        }

        return $next($request);
    }
}
