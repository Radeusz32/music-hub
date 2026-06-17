<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts access to routes that must only be reachable on a central domain
 * (e.g. the superadmin panel). Any other host - including tenant subdomains -
 * gets a 404 so the panel cannot be reached from a tenant's domain.
 */
final class EnsureCentralDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var list<string> $centralDomains */
        $centralDomains = config('tenancy.central_domains', []);

        if (! in_array($request->getHost(), $centralDomains, true)) {
            abort(404);
        }

        return $next($request);
    }
}
