<?php

declare(strict_types=1);

namespace App\Jobs;

use Stancl\Tenancy\Contracts\TenantWithDatabase;

final class CreateTenantStorageLink
{
    public function __construct(public readonly TenantWithDatabase $tenant) {}

    public function handle(): void
    {
        $tenantId = $this->tenant->getTenantKey();

        $storageDir = public_path('storage');
        if (! is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        $link = public_path("storage/tenant{$tenantId}");
        $target = base_path("storage/tenant{$tenantId}/app/public");

        if (is_link($link)) {
            return;
        }

        if (! is_dir($target)) {
            mkdir($target, 0755, true);
        }

        symlink($target, $link);
    }
}
