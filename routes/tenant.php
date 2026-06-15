<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\Auth\AuthController;
use App\Http\Controllers\Tenant\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Tenant\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Tenant\Auth\NewPasswordController;
use App\Http\Controllers\Tenant\Auth\PasswordResetLinkController;
use App\Http\Controllers\Tenant\Auth\VerifyEmailController;
use App\Http\Controllers\Tenant\Inventory\InventoryMovementController;
use App\Http\Controllers\Tenant\Inventory\InventoryRecordController;
use App\Http\Controllers\Tenant\Inventory\InventorySaleController;
use App\Http\Controllers\Tenant\Settings\PasswordController;
use App\Http\Controllers\Tenant\Settings\SettingController;
use App\Http\Controllers\Tenant\Users\UserController;
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

        Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
            ->name('password.request');

        Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
            ->name('password.email');

        Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
            ->name('password.reset');

        Route::post('/reset-password', [NewPasswordController::class, 'store'])
            ->name('password.store');
    });

    /*
    |----------------------------------------------------------------------
    | E-mail verification link (signed, session-independent)
    |----------------------------------------------------------------------
    */

    Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    /*
    |----------------------------------------------------------------------
    | Authenticated
    |----------------------------------------------------------------------
    */

    Route::middleware('auth')->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout'])
            ->name('logout');

        /*
        |------------------------------------------------------------------
        | Email verification
        |------------------------------------------------------------------
        */

        Route::get('/verify-email', EmailVerificationPromptController::class)
            ->name('verification.notice');

        Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
            ->middleware('throttle:6,1')
            ->name('verification.send');

        /*
        |------------------------------------------------------------------
        | Account password
        |------------------------------------------------------------------
        */

        Route::put('/settings/password', [PasswordController::class, 'update'])
            ->name('tenant.settings.password.update');

        /*
        |------------------------------------------------------------------
        | Verified-only application
        |------------------------------------------------------------------
        */

        Route::middleware('verified')->group(function (): void {
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
                            Route::get('/', [InventoryMovementController::class, 'index'])
                                ->name('tenant.inventory.movements.index')
                                ->middleware('permission:inventory-movements-read');

                            Route::post('/', [InventoryMovementController::class, 'store'])
                                ->name('tenant.inventory.movements.store')
                                ->middleware('permission:inventory-movements-create');

                            Route::post('/bulk-destroy', [InventoryMovementController::class, 'bulkDestroy'])
                                ->name('tenant.inventory.movements.bulk-destroy')
                                ->middleware('permission:inventory-movements-delete');

                            Route::delete('/{inventoryMovement}', [InventoryMovementController::class, 'destroy'])
                                ->name('tenant.inventory.movements.destroy')
                                ->middleware('permission:inventory-movements-delete');
                        });

                    Route::prefix('sales')
                        ->group(function (): void {
                            Route::get('/', [InventorySaleController::class, 'index'])
                                ->name('tenant.inventory.sales.index')
                                ->middleware('permission:inventory-sales-read');

                            Route::post('/', [InventorySaleController::class, 'store'])
                                ->name('tenant.inventory.sales.store')
                                ->middleware('permission:inventory-sales-create');
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
                    Route::get('/', [UserController::class, 'index'])
                        ->name('index')
                        ->middleware('permission:users-read');

                    Route::post('/', [UserController::class, 'store'])
                        ->name('store')
                        ->middleware('permission:users-create');

                    Route::post('/bulk-destroy', [UserController::class, 'bulkDestroy'])
                        ->name('bulk-destroy')
                        ->middleware('permission:users-delete');

                    Route::get('/invites', fn () => Inertia::render('Tenant/Users/Invites'))
                        ->name('invites');

                    Route::get('/roles', fn () => Inertia::render('Tenant/Users/Roles'))
                        ->name('roles');

                    Route::get('/{user}', [UserController::class, 'show'])
                        ->name('show')
                        ->middleware('permission:users-read');

                    Route::put('/{user}', [UserController::class, 'update'])
                        ->name('update')
                        ->middleware('permission:users-update');

                    Route::delete('/{user}', [UserController::class, 'destroy'])
                        ->name('destroy')
                        ->middleware('permission:users-delete');

                    Route::post('/{user}/resend-verification', [UserController::class, 'resendVerification'])
                        ->name('resend-verification')
                        ->middleware('permission:users-update');
                });

            /*
            |------------------------------------------------------------------
            | Settings
            |------------------------------------------------------------------
            */

            Route::prefix('settings')
                ->name('tenant.settings.')
                ->group(function (): void {
                    Route::get('/profile', [SettingController::class, 'profile'])
                        ->name('profile')
                        ->middleware('permission:setting-profile');

                    Route::put('/profile', [SettingController::class, 'updateProfile'])
                        ->name('profile.update')
                        ->middleware('permission:setting-profile');

                    Route::middleware('feature:settings')->group(function (): void {
                        Route::get('/organization', fn () => Inertia::render('Tenant/Settings/Organization'))
                            ->name('organization');

                        Route::get('/billing', fn () => Inertia::render('Tenant/Settings/Billing'))
                            ->name('billing');
                    });
                });
        });
    });
});
