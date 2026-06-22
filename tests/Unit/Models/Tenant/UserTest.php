<?php

declare(strict_types=1);

use App\Enums\Tenant\RoleEnum;
use App\Models\Tenant\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

beforeEach(function (): void {
    createBootedTenant();
});

it('builds the full name from first and last name', function (): void {
    $user = User::factory()->create(['first_name' => 'Jan', 'last_name' => 'Kowalski']);

    expect($user->name)->toBe('Jan Kowalski');
});

it('hashes the password', function (): void {
    $user = User::factory()->create();

    expect(Hash::check('password', $user->password))->toBeTrue();
});

it('casts is_active to a boolean', function (): void {
    $user = User::factory()->create(['is_active' => 1]);

    expect($user->refresh()->is_active)->toBeTrue();
});

it('hides sensitive attributes from the array form', function (): void {
    $user = User::factory()->create();

    expect(array_keys($user->toArray()))
        ->not->toContain('password')
        ->not->toContain('remember_token');
});

it('encrypts the pesel at rest yet finds it through the blind index', function (): void {
    $user = User::factory()->create(['pesel' => '44051401458']);

    $raw = DB::table('users')->where('id', $user->id)->value('pesel');

    expect($raw)->not->toBe('44051401458')
        ->and(User::whereBlind('pesel', 'pesel', '44051401458')->first()?->id)->toBe($user->id);
});

it('assigns roles through factory states', function (): void {
    expect(User::factory()->owner()->create()->hasRole(RoleEnum::Owner->value))->toBeTrue()
        ->and(User::factory()->admin()->create()->hasRole(RoleEnum::Admin->value))->toBeTrue()
        ->and(User::factory()->regularUser()->create()->hasRole(RoleEnum::User->value))->toBeTrue();
});

it('tracks email verification state', function (): void {
    expect(User::factory()->create()->hasVerifiedEmail())->toBeTrue()
        ->and(User::factory()->unverified()->create()->hasVerifiedEmail())->toBeFalse();
});
