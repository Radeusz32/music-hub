<?php

declare(strict_types=1);

use App\Models\Central\Admin;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Hash;

it('hashes the password', function (): void {
    $admin = Admin::factory()->create();

    expect($admin->password)->not->toBe('password')
        ->and(Hash::check('password', $admin->password))->toBeTrue();
});

it('hides sensitive attributes from the array form', function (): void {
    $admin = Admin::factory()->create();

    expect(array_keys($admin->toArray()))
        ->not->toContain('password')
        ->not->toContain('remember_token');
});

it('casts the verification timestamp to a datetime', function (): void {
    $admin = Admin::factory()->create();

    expect($admin->email_verified_at)->toBeInstanceOf(CarbonInterface::class);
});
