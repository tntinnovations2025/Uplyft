<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     * Usage in routes: middleware('role:admin') or middleware('role:teacher,student')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (!in_array($user->role, $roles)) {
            // Redirect to their own dashboard if they try to access a different role's area
            return redirect($user->dashboardRoute())
                ->with('error', 'Access denied. You do not have permission to view that area.');
        }

        return $next($request);
    }
}
