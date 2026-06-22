<?php

declare(strict_types=1);

use App\Enums\TenantInvitationStatusEnum;
use App\Models\Central\TenantInvitation;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;

function makeInvitation(array $attributes = []): TenantInvitation
{
    return TenantInvitation::create(array_merge([
        'uuid' => (string) Str::uuid(),
        'email' => 'invitee@example.test',
        'status' => TenantInvitationStatusEnum::Pending,
        'company_data' => ['name' => 'Acme'],
        'owner_data' => ['email' => 'owner@example.test'],
        'expires_at' => now()->addDays(7),
    ], $attributes));
}

it('casts status, json payloads and timestamps', function (): void {
    $invitation = makeInvitation()->refresh();

    expect($invitation->status)->toBe(TenantInvitationStatusEnum::Pending)
        ->and($invitation->company_data)->toBe(['name' => 'Acme'])
        ->and($invitation->owner_data)->toBe(['email' => 'owner@example.test'])
        ->and($invitation->expires_at)->toBeInstanceOf(CarbonInterface::class);
});

it('reports its status through helpers', function (): void {
    expect(makeInvitation(['status' => TenantInvitationStatusEnum::Pending])->isPending())->toBeTrue()
        ->and(makeInvitation(['status' => TenantInvitationStatusEnum::Filled])->isFilled())->toBeTrue()
        ->and(makeInvitation(['status' => TenantInvitationStatusEnum::Accepted])->isAccepted())->toBeTrue();
});

it('is expired only when pending and past the expiry date', function (): void {
    expect(makeInvitation([
        'status' => TenantInvitationStatusEnum::Pending,
        'expires_at' => now()->subDay(),
    ])->isExpired())->toBeTrue();

    expect(makeInvitation([
        'status' => TenantInvitationStatusEnum::Pending,
        'expires_at' => now()->addDay(),
    ])->isExpired())->toBeFalse();

    expect(makeInvitation([
        'status' => TenantInvitationStatusEnum::Accepted,
        'expires_at' => now()->subDay(),
    ])->isExpired())->toBeFalse();
});

it('belongs to a tenant once linked', function (): void {
    $tenant = createTenant();

    $invitation = makeInvitation(['tenant_id' => $tenant->id]);

    expect($invitation->tenant->id)->toBe($tenant->id);
});
