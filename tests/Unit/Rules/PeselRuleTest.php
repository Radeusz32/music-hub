<?php

declare(strict_types=1);

use App\Rules\Tenant\PeselRule;

/**
 * Run the rule and return the failure message, or null when it passes.
 * Uniqueness is disabled so the rule stays a pure unit (no database).
 */
function runPeselRule(string $value): ?string
{
    $message = null;

    (new PeselRule(checkUniqueness: false))->validate(
        'pesel',
        $value,
        function (string $error) use (&$message): void {
            $message = $error;
        },
    );

    return $message;
}

it('accepts a PESEL with a valid checksum', function (string $pesel): void {
    expect(PeselRule::hasValidChecksum($pesel))->toBeTrue()
        ->and(runPeselRule($pesel))->toBeNull();
})->with([
    '44051401458',
    '44444444444',
]);

it('rejects a PESEL with an invalid checksum', function (): void {
    expect(PeselRule::hasValidChecksum('44051401459'))->toBeFalse()
        ->and(runPeselRule('44051401459'))->toBe('Numer PESEL jest nieprawidłowy.');
});

it('rejects a value that is not 11 digits', function (string $value): void {
    expect(runPeselRule($value))->toBe('Numer PESEL musi składać się z 11 cyfr.');
})->with([
    'too short' => '123',
    'too long' => '440514014580',
    'contains letters' => '4405140145a',
    'empty' => '',
]);
