<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\Tenant\BulkDestroyTenantsRequest;
use App\Http\Requests\Central\Tenant\StoreTenantRequest;
use App\Http\Requests\Central\Tenant\UpdateTenantRequest;
use App\Models\Central\Tenant;
use App\Services\Central\TenantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class TenantController extends Controller
{
    public function __construct(private readonly TenantService $service) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Central/Tenants/Index', [
            'records' => $this->service->index($request),
        ]);
    }

    public function store(StoreTenantRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()
            ->route('central.tenants.index')
            ->with('success', 'Sklep został dodany.');
    }

    public function show(Tenant $tenant): Response
    {
        return Inertia::render('Central/Tenants/Show', [
            'record' => $this->service->show($tenant),
        ]);
    }

    public function update(UpdateTenantRequest $request, Tenant $tenant): RedirectResponse
    {
        $this->service->update($tenant, $request->validated());

        return redirect()
            ->route('central.tenants.index')
            ->with('success', 'Dane sklepu zostały zaktualizowane.');
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        $this->service->delete($tenant);

        return redirect()
            ->route('central.tenants.index')
            ->with('success', 'Sklep został usunięty.');
    }

    public function bulkDestroy(BulkDestroyTenantsRequest $request): RedirectResponse
    {
        $deleted = $this->service->bulkDelete($request->validated()['ids']);

        return redirect()
            ->route('central.tenants.index')
            ->with('success', "Usunięto sklepy ({$deleted}).");
    }

    public function toggleActive(Tenant $tenant): RedirectResponse
    {
        $tenant = $this->service->toggleActive($tenant);

        $message = $tenant->is_active
            ? 'Sklep został aktywowany.'
            : 'Sklep został dezaktywowany.';

        return back()->with('success', $message);
    }
}
