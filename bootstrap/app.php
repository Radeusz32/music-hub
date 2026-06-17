<?php

declare(strict_types=1);

use App\Http\Middleware\CheckIfTenantIsActive;
use App\Http\Middleware\CheckIfUserIsActive;
use App\Http\Middleware\EnsureCentralDomain;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);

        $middleware->redirectGuestsTo(fn (Illuminate\Http\Request $request): string => $request->is('panel/central/superadmin*')
            ? route('central.login')
            : route('login'));

        $middleware->redirectUsersTo(fn (Illuminate\Http\Request $request): string => $request->is('panel/central/superadmin*')
            ? route('central.dashboard')
            : route('tenant.dashboard'));

        $middleware->alias([
            'tenant' => InitializeTenancyByDomain::class,
            'permission' => Spatie\Permission\Middleware\PermissionMiddleware::class,
            'prevent-central' => PreventAccessFromCentralDomains::class,
            'central' => EnsureCentralDomain::class,
            'tenant-active' => CheckIfTenantIsActive::class,
            'user-active' => CheckIfUserIsActive::class,
            'feature' => App\Http\Middleware\CheckFeature::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (
            Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedOnDomainException $e,
            Illuminate\Http\Request $request,
        ) {
            abort(404);
        });
    })
    ->create();
