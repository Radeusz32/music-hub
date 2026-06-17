<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

final class UserInactiveController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Tenant/UserInactivePage');
    }
}
