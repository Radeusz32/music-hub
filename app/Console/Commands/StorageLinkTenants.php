<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use Illuminate\Console\Command;

final class StorageLinkTenants extends Command
{
    protected $signature = 'tenants:storage-link {--tenant= : Specific tenant ID}';

    protected $description = 'Create public/storage symlinks for tenant file storage';

    public function handle(): int
    {
        $this->ensureStorageDirectoryExists();

        $tenants = $this->option('tenant')
            ? Tenant::where('id', $this->option('tenant'))->get()
            : Tenant::all();

        foreach ($tenants as $tenant) {
            $this->createSymlink($tenant->id);
        }

        return self::SUCCESS;
    }

    private function ensureStorageDirectoryExists(): void
    {
        $storageDir = public_path('storage');

        if (is_link($storageDir)) {
            unlink($storageDir);
            $this->line('  <comment>REMOVED</comment>  existing public/storage symlink');
        }

        if (! is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
            $this->line('  <info>CREATED</info>  public/storage directory');
        }
    }

    private function createSymlink(string $tenantId): void
    {
        $link = public_path("storage/tenant{$tenantId}");
        $target = base_path("storage/tenant{$tenantId}/app/public");

        if (is_link($link)) {
            $this->line("  <comment>EXISTS</comment>  tenant{$tenantId}");

            return;
        }

        if (! is_dir($target)) {
            mkdir($target, 0755, true);
        }

        symlink($target, $link);
        $this->line("  <info>LINKED</info>  tenant{$tenantId}");
    }
}
