<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Enums\FeatureEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Central\Feature\ToggleFeatureRequest;
use App\Models\Central\Tenant;
use App\Services\Central\FeatureService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class FeatureController extends Controller
{
    public function __construct(private readonly FeatureService $service) {}

    public function index(): Response
    {
        return Inertia::render('Central/Features/Index', [
            'tenants' => $this->service->tenantsWithFeatures(),
            'features' => $this->service->availableFeatures(),
        ]);
    }

    public function toggle(ToggleFeatureRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->service->toggle(
            Tenant::query()->findOrFail($data['tenant_id']),
            FeatureEnum::from($data['feature']),
        );

        return back()->with('success', 'Moduły sklepu zostały zaktualizowane.');
    }
}
