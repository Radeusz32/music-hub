<?php

declare(strict_types=1);

use App\Models\Central\Tenant;

it('belongs to its tenant', function (): void {
    $tenant = createTenant(domain: 'belongs.test');

    $domain = $tenant->domains()->first();

    expect($domain->domain)->toBe('belongs.test')
        ->and($domain->tenant)->toBeInstanceOf(Tenant::class)
        ->and($domain->tenant->id)->toBe($tenant->id);
});
