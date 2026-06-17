<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Tenant\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks access for individual tenant users whose account has been deactivated
 * and redirects to a dedicated notice page.
 * Must run after auth middleware so auth()->user() is available.
 */
final class CheckIfUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User && $user->is_active === false) {
            return redirect()->route('tenant.user.inactive');
        }

        return $next($request);
    }
}
