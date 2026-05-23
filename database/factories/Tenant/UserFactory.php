<?php

declare(strict_types=1);

namespace Database\Factories\Tenant;

use App\Enums\Tenant\RoleEnum;
use App\Models\Tenant\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
final class UserFactory extends Factory
{
    protected $model = User::class;

    private static ?string $password = null;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => self::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function owner(): self
    {
        return $this->state(fn (array $attributes): array => [
            'first_name' => 'Tenant',
            'last_name' => 'Owner',
            'email' => 'owner@music1.test',
        ])->afterCreating(function (User $user): void {
            $user->syncRoles([RoleEnum::Owner->value]);
        });
    }

    public function admin(): self
    {
        return $this->afterCreating(function (User $user): void {
            $user->syncRoles([RoleEnum::Admin->value]);
        });
    }

    public function regularUser(): self
    {
        return $this->afterCreating(function (User $user): void {
            $user->syncRoles([RoleEnum::User->value]);
        });
    }

    public function unverified(): self
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
        ]);
    }
}
