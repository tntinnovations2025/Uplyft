<?php

namespace App\Http\Middleware;

use App\Models\Institute;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckInstituteFeature Middleware
 *
 * Usage in routes:
 *   Route::middleware('feature:ai_bot')->group(...)
 *   Route::middleware('feature:assessment_engine,timetable')->group(...)  // multiple features (AND logic)
 *
 * How it resolves the institute:
 *   1. From route parameter {institute} or {institute_slug}
 *   2. From authenticated user's institute_id (for future user-tenant binding)
 *   3. From X-Institute-ID request header (API use)
 *
 * If the feature is OFF → 403 response (JSON for API, redirect for web).
 */
class CheckInstituteFeature
{
    public function handle(Request $request, Closure $next, string ...$features): Response
    {
        $institute = $this->resolveInstitute($request);

        if (! $institute) {
            return $this->deny($request, 'Institute not found or not specified.', 404);
        }

        if (! $institute->is_active) {
            return $this->deny($request, 'This institute account is currently inactive.');
        }

        // Load feature toggles (cached per request via Eloquent eager load)
        $toggles = $institute->featureToggles;

        if (! $toggles) {
            return $this->deny($request, 'Feature configuration not found for this institute.');
        }

        foreach ($features as $feature) {
            $feature = trim($feature);

            // Validate the feature key exists on the model
            if (! in_array($feature, \App\Models\InstituteFeatureToggle::$featureKeys, true)) {
                return $this->deny($request, "Unknown feature flag: [{$feature}].");
            }

            if (! $toggles->$feature) {
                return $this->deny(
                    $request,
                    "The [{$feature}] module is not enabled for this institute."
                );
            }
        }

        return $next($request);
    }

    // ── Private helpers ──────────────────────────────────────────────────

    private function resolveInstitute(Request $request): ?Institute
    {
        // 1. Route model binding or slug parameter
        if ($request->route('institute') instanceof Institute) {
            return $request->route('institute');
        }

        $slug = $request->route('institute_slug')
             ?? $request->route('institute')
             ?? $request->header('X-Institute-Slug');

        if ($slug) {
            return Institute::where('slug', $slug)->with('featureToggles')->first();
        }

        // 2. Authenticated user's institute_id (will be wired in Module 2)
        if (auth()->check() && method_exists(auth()->user(), 'institute')) {
            return auth()->user()->institute?->load('featureToggles');
        }

        return null;
    }

    private function deny(Request $request, string $message, int $status = 403): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => $message], $status);
        }

        return redirect()->back()->with('error', $message);
    }
}
