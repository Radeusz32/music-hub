<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\Auth\AuthController;
use App\Http\Controllers\Tenant\Inventory\InventoryRecordController;
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
                        Route::get('/', [InventoryRecordController::class, 'index'])
                            ->name('tenant.inventory.records.index')
                            ->middleware('permission:inventory-records-read');

                        Route::post('/', [InventoryRecordController::class, 'store'])
                            ->name('tenant.inventory.records.store')
                            ->middleware('permission:inventory-records-create');

                        Route::post('/import', [InventoryRecordController::class, 'importExcel'])
                            ->name('tenant.inventory.records.import')
                            ->middleware('permission:inventory-records-create');

                        Route::get('/export-template', [InventoryRecordController::class, 'exportExcelTemplate'])
                            ->name('tenant.inventory.records.export-template')
                            ->middleware('permission:inventory-records-read');

                        Route::post('/bulk-destroy', [InventoryRecordController::class, 'bulkDestroy'])
                            ->name('tenant.inventory.records.bulk-destroy')
                            ->middleware('permission:inventory-records-delete');

                        Route::get('/{inventoryRecord}', [InventoryRecordController::class, 'show'])
                            ->name('tenant.inventory.records.show')
                            ->middleware('permission:inventory-records-read');

                        Route::put('/{inventoryRecord}', [InventoryRecordController::class, 'update'])
                            ->name('tenant.inventory.records.update')
                            ->middleware('permission:inventory-records-update');

                        Route::delete('/{inventoryRecord}', [InventoryRecordController::class, 'destroy'])
                            ->name('tenant.inventory.records.destroy')
                            ->middleware('permission:inventory-records-delete');

                        Route::post('/{inventoryRecord}/cover', [InventoryRecordController::class, 'uploadCover'])
                            ->name('tenant.inventory.records.cover')
                            ->middleware('permission:inventory-records-update');

                        Route::delete('/{inventoryRecord}/cover', [InventoryRecordController::class, 'destroyCover'])
                            ->name('tenant.inventory.records.cover.destroy')
                            ->middleware('permission:inventory-records-update');
                    });

                Route::prefix('movements')
                    ->group(function (): void {
                        Route::get('/', fn () => Inertia::render('Tenant/Inventory/Movements'))
                            ->name('tenant.inventory.movements.index')
                            ->middleware('permission:inventory-movements-read');
                    });
            });

        /*
        |------------------------------------------------------------------
        | Trading
        |------------------------------------------------------------------
        */

        Route::prefix('trading')
            ->name('tenant.trading.')
            ->middleware('feature:trading')
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
            ->middleware('feature:analytics')
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
            ->middleware('feature:integrations')
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
            ->middleware('feature:users')
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
            ->middleware('feature:settings')
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
