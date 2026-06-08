<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use App\Enums\GuardEnum;
use Carbon\CarbonInterface;
use Database\Factories\Tenant\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use ParagonIE\CipherSweet\BlindIndex;
use ParagonIE\CipherSweet\EncryptedRow;
use Spatie\LaravelCipherSweet\Concerns\UsesCipherSweet;
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property-read int $id
 * @property-read string $first_name
 * @property-read string $last_name
 * @property-read string $name
 * @property-read string $email
 * @property-read string|null $phone
 * @property-read string|null $street
 * @property-read string|null $building_number
 * @property-read string|null $apartment_number
 * @property-read string|null $postal_code
 * @property-read string|null $city
 * @property-read string|null $pesel
 * @property-read bool $is_active
 * @property-read CarbonInterface|null $email_verified_at
 * @property-read string $password
 * @property-read string|null $remember_token
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class User extends Authenticatable implements CipherSweetEncrypted, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, UsesCipherSweet;

    /**
     * @var list<string>
     */
    protected string $guard_name = GuardEnum::Web->value;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'street',
        'building_number',
        'apartment_number',
        'postal_code',
        'city',
        'pesel',
        'is_active',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Encrypt PII at rest and register blind indexes for exact-match search.
     * Blind indexes are persisted to the `blind_indexes` table and queried via
     * the `whereBlind` / `orWhereBlind` scopes.
     */
    public static function configureCipherSweet(EncryptedRow $encryptedRow): void
    {
        $encryptedRow
            ->addOptionalTextField('phone')
            ->addOptionalTextField('street')
            ->addOptionalTextField('building_number')
            ->addOptionalTextField('apartment_number')
            ->addOptionalTextField('postal_code')
            ->addOptionalTextField('city')
            ->addOptionalTextField('pesel')
            ->addBlindIndex('phone', new BlindIndex('phone'))
            ->addBlindIndex('postal_code', new BlindIndex('postal_code'))
            ->addBlindIndex('city', new BlindIndex('city'))
            ->addBlindIndex('pesel', new BlindIndex('pesel'));
    }

    public function getNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'first_name' => 'string',
            'last_name' => 'string',
            'email' => 'string',
            'is_active' => 'boolean',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'remember_token' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
