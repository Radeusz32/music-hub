<?php

declare(strict_types=1);

use App\Enums\GuardEnum;

it('exposes the web guard value', function (): void {
    expect(GuardEnum::Web->value)->toBe('web');
});
