<?php

declare(strict_types=1);

use App\Enums\TenantInvitationStatusEnum;

it('covers every invitation status', function (): void {
    expect(array_column(TenantInvitationStatusEnum::cases(), 'value'))
        ->toEqualCanonicalizing(['PENDING', 'FILLED', 'ACCEPTED', 'EXPIRED']);
});

it('builds an option for every status', function (): void {
    $options = TenantInvitationStatusEnum::options();

    expect($options)->toHaveCount(count(TenantInvitationStatusEnum::cases()));

    foreach ($options as $option) {
        expect($option)->toHaveKeys(['value', 'label', 'color'])
            ->and($option['label'])->not->toBe('')
            ->and($option['color'])->toMatch('/^#[0-9a-fA-F]{6}$/');
    }
});

it('gives every status a non-empty label', function (TenantInvitationStatusEnum $status): void {
    expect($status->label())->not->toBe('');
})->with(TenantInvitationStatusEnum::cases());
