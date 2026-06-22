<?php

declare(strict_types=1);

use App\Enums\FilterTypeEnum;

it('marks date and number ranges as ranges', function (): void {
    expect(FilterTypeEnum::DateRange->isRange())->toBeTrue()
        ->and(FilterTypeEnum::NumberRange->isRange())->toBeTrue();
});

it('marks non-range filter types as not ranges', function (FilterTypeEnum $type): void {
    expect($type->isRange())->toBeFalse();
})->with([
    FilterTypeEnum::Select,
    FilterTypeEnum::Text,
    FilterTypeEnum::Number,
    FilterTypeEnum::Relation,
    FilterTypeEnum::NullStatus,
    FilterTypeEnum::Boolean,
]);
