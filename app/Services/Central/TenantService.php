<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Http\Resources\Central\TenantDataTable;
use App\Models\Central\Tenant;
use App\Services\BaseService;
use App\Transformers\Central\TenantTransformer;
use Illuminate\Http\Request;

final class TenantService extends BaseService
{
    /**
     * @return array<string, mixed>
     */
    public function index(Request $request): array
    {
        return $this->fetchForDataTable(TenantDataTable::class, $request);
    }

    /**
     * @return array<string, mixed>
     */
    public function show(Tenant $tenant): array
    {
        $tenant->loadMissing(TenantTransformer::eagerLoads());

        return (new TenantTransformer())->toArray($tenant, request());
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Tenant
    {
        return Tenant::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Tenant $tenant, array $data): Tenant
    {
        $tenant->update($data);

        return $tenant->refresh();
    }

    /**
     * Delete a tenant. Stancl model events tear down its database/domains.
     */
    public function delete(Tenant $tenant): void
    {
        $tenant->delete();
    }

    /**
     * Flip the tenant's active state. A deactivated tenant is blocked from
     * accessing its app by the `tenant-active` middleware.
     */
    public function toggleActive(Tenant $tenant): Tenant
    {
        $tenant->update(['is_active' => ! $tenant->is_active]);

        return $tenant->refresh();
    }

    /**
     * Delete multiple tenants. Iterates so each model's delete events fire
     * (database teardown). Returns the number of tenants deleted.
     *
     * @param  array<int, string>  $ids
     */
    public function bulkDelete(array $ids): int
    {
        $tenants = Tenant::query()->whereIn('id', $ids)->get();

        foreach ($tenants as $tenant) {
            $tenant->delete();
        }

        return $tenants->count();
    }

    /**
     * Organization / company data shaped for the settings panel.
     *
     * @return array<string, mixed>
     */
    public function organization(Tenant $tenant): array
    {
        return [
            'company_name' => $tenant->company_name,
            'tax_id' => $tenant->tax_id,
            'regon' => $tenant->regon,
            'krs_number' => $tenant->krs_number,
            'company_email' => $tenant->company_email,
            'company_phone' => $tenant->company_phone,
            'website' => $tenant->website,
            'street' => $tenant->street,
            'building_number' => $tenant->building_number,
            'apartment_number' => $tenant->apartment_number,
            'postal_code' => $tenant->postal_code,
            'city' => $tenant->city,
            'country' => $tenant->country,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateOrganization(Tenant $tenant, array $data): Tenant
    {
        return $this->update($tenant, $data);
    }
}
