<?php

declare(strict_types=1);

namespace App\Console\Commands\Tenant;

use App\Models\Central\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDO;

final class RemoveUnusedTenants extends Command
{
    protected $signature = 'tenant:remove-unused';

    protected $description = 'Drop tenant databases that no longer have a matching tenant record';

    public function handle(): int
    {
        $pdo = DB::connection()->getPdo();

        $databases = $pdo->query('SHOW DATABASES')->fetchAll(PDO::FETCH_COLUMN);

        $existingTenantIds = Tenant::pluck('id');

        $dropped = 0;

        foreach ($databases as $database) {
            if (! str_starts_with($database, 'tenant')) {
                continue;
            }

            $tenantId = str_replace('tenant', '', $database);

            if ($existingTenantIds->contains($tenantId)) {
                continue;
            }

            $pdo->exec("DROP DATABASE `{$database}`");

            $this->line("Dropped: {$database}");

            $dropped++;
        }

        $this->info("Done. Dropped {$dropped} database(s).");

        return self::SUCCESS;
    }
}
