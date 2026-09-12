<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permissionKey
     */
    public function handle(Request $request, Closure $next, string $permissionKey, string $mode = 'view'): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        // Check if user has specific granular permission
        if (! $user->hasPermission($permissionKey, $mode)) {
            if ($request->expectsJson() || ! $request->isMethodSafe()) {
                abort(403, "Access Denied: You do not have {$mode} rights for this module.");
            }

            $fallbackRoute = match ($user->role) {
                'student' => 'student.dashboard',
                'teacher' => 'teacher.dashboard',
                'global_admin' => 'global-admin.dashboard',
                default => 'principal.dashboard',
            };

            return redirect()
                ->route($fallbackRoute)
                ->with('error', "Access Denied: You do not have {$mode} rights for this module.");
        }

        return $next($request);
    }
}
