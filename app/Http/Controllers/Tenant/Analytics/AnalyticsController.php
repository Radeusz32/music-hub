<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Analytics;

use App\Http\Controllers\Controller;
use App\Services\Tenant\Analytics\AnalyticsService;
use Inertia\Inertia;
use Inertia\Response;

final class AnalyticsController extends Controller
{
    public function __construct(private readonly AnalyticsService $service) {}

    public function overview(): Response
    {
        return Inertia::render('Tenant/Analytics/Overview', $this->service->overview());
    }

    public function sales(): Response
    {
        return Inertia::render('Tenant/Analytics/Sales', $this->service->sales());
    }

    public function artists(): Response
    {
        return Inertia::render('Tenant/Analytics/Artists', $this->service->artists());
    }

    public function reports(): Response
    {
        return Inertia::render('Tenant/Analytics/Reports', $this->service->reports());
    }
}
