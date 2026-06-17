<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Central\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks all access to a tenant (organization) whose account has been
 * deactivated by a superadmin and redirects to a dedicated notice page.
 * Runs after tenancy is initialized, so `tenancy()->tenant` is available.
 */
final class CheckIfTenantIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenancy()->tenant;

        if ($tenant instanceof Tenant && $tenant->is_active === false) {
            return redirect()->route('tenant.inactive');
        }

        return $next($request);
    }
}
