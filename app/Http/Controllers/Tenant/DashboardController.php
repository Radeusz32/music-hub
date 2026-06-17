<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Enums\Tenant\InventoryMovementTypeEnum;
use App\Http\Controllers\Controller;
use App\Services\Tenant\Dashboard\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $service) {}

    public function __invoke(Request $request): Response
    {
        return Inertia::render('Tenant/Dashboard', [
            'stats' => $this->service->stats(),
            'movements' => $this->service->recentMovements($request),
            'typeOptions' => InventoryMovementTypeEnum::options(),
        ]);
    }
}
