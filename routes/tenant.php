<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\Auth\AuthController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware([
    'web',
    'tenant',
    'prevent-central',
])->group(function (): void {
    /*
    |----------------------------------------------------------------------
    | Guest
    |----------------------------------------------------------------------
    */

    Route::middleware('guest')->group(function (): void {
        Route::get('/login', fn () => Inertia::render('Tenant/Auth/Login'))
            ->name('login');

        Route::post('/login', [AuthController::class, 'login'])
            ->name('login.store');
    });

    /*
    |----------------------------------------------------------------------
    | Authenticated
    |----------------------------------------------------------------------
    */

    Route::middleware('auth')->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout'])
            ->name('logout');

        Route::redirect('/', '/dashboard');

        Route::get('/dashboard', fn () => Inertia::render('Tenant/Dashboard'))
            ->name('tenant.dashboard');

        /*
        |------------------------------------------------------------------
        | Inventory
        |------------------------------------------------------------------
        */

        Route::prefix('inventory')
            ->middleware('feature:inventory')
            ->group(function (): void {
                Route::prefix('records')
                    ->group(function (): void {
                        Route::get('/', [InventoryController::class, 'index'])
                            ->name('tenant.inventory.records.index')
                            ->middleware('permission:inventory-records-read');

                        Route::get('/create', [InventoryController::class, 'create'])
                            ->name('tenant.inventory.records.create')
                            ->middleware('permission:inventory-records-create');

                        Route::post('/', [InventoryController::class, 'store'])
                            ->name('tenant.inventory.records.store')
                            ->middleware('permission:inventory-records-create');

                        Route::get('/{inventoryRecord}', [InventoryController::class, 'show'])
                            ->name('tenant.inventory.records.show')
                            ->middleware('permission:inventory-records-read');

                        Route::get('/{inventoryRecord}/edit', [InventoryRecordController::class, 'edit'])
                            ->name('tenant.inventory.records.edit')
                            ->middleware('permission:inventory-records-update');

                        Route::put('/{inventoryRecord}', [InventoryController::class, 'update'])
                            ->name('tenant.inventory.records.update')
                            ->middleware('permission:inventory-records-update');

                        Route::delete('/{inventoryRecord}', [InventoryController::class, 'destroy'])
                            ->name('tenant.inventory.records.destroy')
                            ->middleware('permission:inventory-records-delete');
                    });

                Route::prefix('movements')
                    ->group(function (): void {
                        Route::get('/', [InventoryMovementController::class, 'index'])
                            ->name('tenant.inventory.movements.index')
                            ->middleware('permission:inventory-movements-read');

                        Route::post('/', [InventoryMovementController::class, 'store'])
                            ->name('tenant.inventory.movements.store')
                            ->middleware('permission:inventory-movements-create');

                        Route::get('/{inventoryMovement}', [InventoryMovementController::class, 'show'])
                            ->name('tenant.inventory.movements.show')
                            ->middleware('permission:inventory-movements-read');

                        Route::delete('/{inventoryMovement}', [InventoryMovementController::class, 'destroy'])
                            ->name('tenant.inventory.movements.destroy')
                            ->middleware('permission:inventory-movements-delete');
                    });
            });

        /*
        |------------------------------------------------------------------
        | Trading
        |------------------------------------------------------------------
        */

        Route::prefix('trading')
            ->name('tenant.trading.')
            ->group(function (): void {
                Route::prefix('events')
                    ->name('events.')
                    ->group(function (): void {
                        Route::get('/', fn () => Inertia::render('Tenant/Trading/Events/Index'))
                            ->name('index');

                        Route::get('/create', fn () => Inertia::render('Tenant/Trading/Events/Create'))
                            ->name('create');
                    });

                Route::prefix('listings')
                    ->name('listings.')
                    ->group(function (): void {
                        Route::get('/', fn () => Inertia::render('Tenant/Trading/Listings/Index'))
                            ->name('index');
                    });

                Route::prefix('sales')
                    ->name('sales.')
                    ->group(function (): void {
                        Route::get('/', fn () => Inertia::render('Tenant/Trading/Sales/Index'))
                            ->name('index');
                    });

                Route::get('/analytics', fn () => Inertia::render('Tenant/Trading/Analytics/Index'))
                    ->name('analytics');
            });

        /*
        |------------------------------------------------------------------
        | Analytics
        |------------------------------------------------------------------
        */

        Route::prefix('analytics')
            ->name('tenant.analytics.')
            ->group(function (): void {
                Route::get('/overview', fn () => Inertia::render('Tenant/Analytics/Overview'))
                    ->name('overview');

                Route::get('/sales', fn () => Inertia::render('Tenant/Analytics/Sales'))
                    ->name('sales');

                Route::get('/artists', fn () => Inertia::render('Tenant/Analytics/Artists'))
                    ->name('artists');

                Route::get('/reports', fn () => Inertia::render('Tenant/Analytics/Reports'))
                    ->name('reports');
            });

        /*
        |------------------------------------------------------------------
        | Integrations
        |------------------------------------------------------------------
        */

        Route::prefix('integrations')
            ->name('tenant.integrations.')
            ->group(function (): void {
                Route::get('/allegro', fn () => Inertia::render('Tenant/Integrations/Allegro'))
                    ->name('allegro');

                Route::get('/discogs', fn () => Inertia::render('Tenant/Integrations/Discogs'))
                    ->name('discogs');

                Route::get('/api', fn () => Inertia::render('Tenant/Integrations/Api'))
                    ->name('api');
            });

        /*
        |------------------------------------------------------------------
        | Users
        |------------------------------------------------------------------
        */

        Route::prefix('users')
            ->name('tenant.users.')
            ->group(function (): void {
                Route::get('/', fn () => Inertia::render('Tenant/Users/Index'))
                    ->name('index');

                Route::get('/invites', fn () => Inertia::render('Tenant/Users/Invites'))
                    ->name('invites');

                Route::get('/roles', fn () => Inertia::render('Tenant/Users/Roles'))
                    ->name('roles');
            });

        /*
        |------------------------------------------------------------------
        | Settings
        |------------------------------------------------------------------
        */

        Route::prefix('settings')
            ->name('tenant.settings.')
            ->group(function (): void {
                Route::get('/profile', fn () => Inertia::render('Tenant/Settings/Profile'))
                    ->name('profile');

                Route::get('/organization', fn () => Inertia::render('Tenant/Settings/Organization'))
                    ->name('organization');

                Route::get('/billing', fn () => Inertia::render('Tenant/Settings/Billing'))
                    ->name('billing');
            });
    });
});
