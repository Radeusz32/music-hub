<?php

declare(strict_types=1);

use App\Http\Controllers\Central\Auth\AuthController;
use App\Http\Controllers\Central\FeatureController;
use App\Http\Controllers\Central\InvitationController;
use App\Http\Controllers\Central\TenantController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Central / Superadmin panel
|--------------------------------------------------------------------------
| Runs on the central domain (e.g. localhost) - no tenancy bootstrapping.
| The prefix is deliberately obscure so the panel is hard to stumble upon.
| Authentication is handled by the dedicated `superadmin` guard (central
| `admins` table), fully separate from tenant users.
*/

Route::prefix('panel/central/superadmin')
    ->middleware(['web', 'central'])
    ->group(function (): void {
        /*
        |----------------------------------------------------------------------
        | Guest
        |----------------------------------------------------------------------
        */
        Route::middleware('guest:superadmin')->group(function (): void {
            Route::get('/login', fn () => Inertia::render('Central/Auth/Login'))
                ->name('central.login');

            Route::post('/login', [AuthController::class, 'login'])
                ->name('central.login.store');
        });

        /*
        |----------------------------------------------------------------------
        | Authenticated superadmin
        |----------------------------------------------------------------------
        */
        Route::middleware('auth:superadmin')->group(function (): void {
            Route::post('/logout', [AuthController::class, 'logout'])
                ->name('central.logout');

            Route::get('/', fn () => Inertia::render('Central/Dashboard'))
                ->name('central.dashboard');

            Route::prefix('tenants')->group(function (): void {
                Route::get('/', [TenantController::class, 'index'])
                    ->name('central.tenants.index');
                Route::post('/', [TenantController::class, 'store'])
                    ->name('central.tenants.store');
                Route::post('/bulk-destroy', [TenantController::class, 'bulkDestroy'])
                    ->name('central.tenants.bulk-destroy');

                Route::get('/{tenant}', [TenantController::class, 'show'])
                    ->name('central.tenants.show');
                Route::put('/{tenant}', [TenantController::class, 'update'])
                    ->name('central.tenants.update');
                Route::delete('/{tenant}', [TenantController::class, 'destroy'])
                    ->name('central.tenants.destroy');
                Route::post('/{tenant}/toggle-active', [TenantController::class, 'toggleActive'])
                    ->name('central.tenants.toggle-active');
            });

            Route::prefix('invitations')->group(function (): void {
                Route::get('/', [InvitationController::class, 'index'])
                    ->name('central.invitations.index');
                Route::post('/', [InvitationController::class, 'store'])
                    ->name('central.invitations.store');

                Route::get('/{invitation}', [InvitationController::class, 'show'])
                    ->name('central.invitations.show');
                Route::post('/{invitation}/resend', [InvitationController::class, 'resend'])
                    ->name('central.invitations.resend');
                Route::post('/{invitation}/accept', [InvitationController::class, 'accept'])
                    ->name('central.invitations.accept');
                Route::delete('/{invitation}', [InvitationController::class, 'destroy'])
                    ->name('central.invitations.destroy');
            });

            Route::prefix('features')->group(function (): void {
                Route::get('/', [FeatureController::class, 'index'])
                    ->name('central.features.index');
                Route::post('/toggle', [FeatureController::class, 'toggle'])
                    ->name('central.features.toggle');
            });
        });
    });
