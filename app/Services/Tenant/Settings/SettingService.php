<?php

declare(strict_types=1);

namespace App\Services\Tenant\Settings;

use App\Models\Central\Tenant;
use App\Models\Tenant\User;
use App\Services\BaseService;
use App\Services\Central\TenantService;
use App\Services\Tenant\Users\UserService;

final class SettingService extends BaseService
{
    public function __construct(
        private readonly UserService $userService,
        private readonly TenantService $tenantService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function profile(User $user): array
    {
        return $this->userService->show($user);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateProfile(User $user, array $data): User
    {
        return $this->userService->update($user, $data);
    }

    /**
     * @return array<string, mixed>
     */
    public function organization(): array
    {
        return $this->tenantService->organization($this->currentTenant());
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateOrganization(array $data): Tenant
    {
        return $this->tenantService->updateOrganization($this->currentTenant(), $data);
    }

    private function currentTenant(): Tenant
    {
        $tenant = tenancy()->tenant;

        abort_unless($tenant instanceof Tenant, 404);

        return $tenant;
    }
}
