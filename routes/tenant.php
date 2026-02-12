<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware([
    'web',
    'tenant',
    'prevent-central',
])
    ->group(function () {

        Route::get('/', function () {
            return Inertia::render('Tenant/Dashboard');
        })->name('tenant.dashboard');

    });
