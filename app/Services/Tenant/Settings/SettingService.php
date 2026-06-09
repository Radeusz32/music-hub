<?php

declare(strict_types=1);

namespace App\Services\Tenant\Settings;

use App\Models\Tenant\User;
use App\Services\BaseService;
use App\Services\Tenant\Users\UserService;

final class SettingService extends BaseService
{
    public function __construct(private readonly UserService $userService) {}

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
}
