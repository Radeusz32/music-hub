<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Central\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CheckFeature
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $tenant = tenancy()->tenant;

        if (! $tenant instanceof Tenant || ! $tenant->features->pluck('name')->map(fn ($f) => $f->value)->contains($feature)) {
            abort(403);
        }

        return $next($request);
    }
}
