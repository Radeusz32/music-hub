<?php

declare(strict_types=1);

use App\Http\Controllers\PublicInvitationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public invitation form (central domain, no auth required)
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'central'])
    ->prefix('invitation')
    ->group(function (): void {
        Route::get('/{uuid}', [PublicInvitationController::class, 'show'])
            ->name('invitation.show');

        Route::post('/{uuid}/validate-step', [PublicInvitationController::class, 'validateStep'])
            ->name('invitation.validate-step');

        Route::post('/{uuid}', [PublicInvitationController::class, 'submit'])
            ->name('invitation.submit');
    });

require __DIR__.'/admin.php';
require __DIR__.'/tenant.php';
