<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaffPortalPrefix
{
    /**
     * Ensure staff members (accountant, general staff, teacher) browse under their designated URL prefix.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            if (method_exists($user, 'getStaffUrlPrefix')) {
                $expectedPrefix = $user->getStaffUrlPrefix(); // 'accountant', 'staff', or 'teacher'
                $firstSegment = $request->segment(1);

                if (in_array($firstSegment, ['teacher', 'staff', 'accountant']) && $firstSegment !== $expectedPrefix) {
                    $newPath = preg_replace('#^[^/]+#', $expectedPrefix, $request->path());
                    $query = $request->getQueryString();
                    return redirect('/' . $newPath . ($query ? '?' . $query : ''));
                }
            }
        }

        return $next($request);
    }
}
