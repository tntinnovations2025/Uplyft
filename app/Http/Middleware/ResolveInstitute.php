<?php

namespace App\Http\Middleware;

use App\Services\Tenancy\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveInstitute
{
    public function __construct(protected TenantManager $tenantManager)
    {
    }

    /**
     * Route requests through the tenant's database when an authenticated
     * user belongs to an institute.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && ! empty($user->institute_id)) {
            $this->tenantManager->switchTo((int) $user->institute_id);
        }

        return $next($request);
    }
}
