<?php

declare(strict_types=1);

namespace App\Rules\Tenant;

use App\Models\Tenant\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class PeselRule implements ValidationRule
{
    /**
     * Official PESEL control-digit weights for the first 10 digits.
     *
     * @var list<int>
     */
    private const array WEIGHTS = [1, 3, 7, 9, 1, 3, 7, 9, 1, 3];

    /**
     * @param  int|null  $ignoreUserId  user id to skip in the uniqueness check (for updates)
     */
    public function __construct(
        private readonly ?int $ignoreUserId = null,
        private readonly bool $checkUniqueness = true,
    ) {}

    public static function hasValidChecksum(string $pesel): bool
    {
        $sum = 0;

        for ($i = 0; $i < 10; $i++) {
            $sum += (int) $pesel[$i] * self::WEIGHTS[$i];
        }

        $control = (10 - ($sum % 10)) % 10;

        return $control === (int) $pesel[10];
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $pesel = (string) $value;

        if (preg_match('/^\d{11}$/', $pesel) !== 1) {
            $fail('Numer PESEL musi składać się z 11 cyfr.');

            return;
        }

        if (! self::hasValidChecksum($pesel)) {
            $fail('Numer PESEL jest nieprawidłowy.');

            return;
        }

        if (! $this->checkUniqueness) {
            return;
        }

        $alreadyTaken = User::whereBlind('pesel', 'pesel', $pesel)
            ->when($this->ignoreUserId !== null, fn ($query) => $query->whereKeyNot($this->ignoreUserId))
            ->exists();

        if ($alreadyTaken) {
            $fail('Podany numer PESEL jest już zajęty.');
        }
    }
}
