<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Settings\UpdateOrganizationRequest;
use App\Http\Requests\Tenant\Settings\UpdateProfileRequest;
use App\Services\Tenant\Settings\SettingService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class SettingController extends Controller
{
    public function __construct(private readonly SettingService $settingService) {}

    public function profile(): Response
    {
        return Inertia::render('Tenant/Settings/Profile', [
            'profile' => $this->settingService->profile(request()->user()),
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        $this->settingService->updateProfile($request->user(), $request->validated());

        return back()->with('success', 'Dane profilu zostały zaktualizowane.');
    }

    public function organization(): Response
    {
        return Inertia::render('Tenant/Settings/Organization', [
            'organization' => $this->settingService->organization(),
        ]);
    }

    public function updateOrganization(UpdateOrganizationRequest $request): RedirectResponse
    {
        $this->settingService->updateOrganization($request->validated());

        return back()->with('success', 'Dane organizacji zostały zaktualizowane.');
    }
}
