<?php

declare(strict_types=1);

use App\Enums\Tenant\RoleEnum;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->tenant = createBootedTenant();
});

it('renders the login page for guests', function (): void {
    $this->get(tenantUrl($this->tenant, '/login'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Tenant/Auth/Login'));
});

it('logs in a tenant user with valid credentials', function (): void {
    tenantUser(RoleEnum::Owner, ['email' => 'owner@acme.test']);

    $response = $this->post(tenantUrl($this->tenant, '/login'), [
        'email' => 'owner@acme.test',
        'password' => 'password',
    ]);

    $response->assertRedirectContains('/dashboard');
    $this->assertAuthenticated();
});

it('rejects login with an invalid password', function (): void {
    tenantUser(RoleEnum::Owner, ['email' => 'owner@acme.test']);

    $response = $this->post(tenantUrl($this->tenant, '/login'), [
        'email' => 'owner@acme.test',
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('validates that email and password are required', function (): void {
    $this->post(tenantUrl($this->tenant, '/login'), [])
        ->assertSessionHasErrors(['email', 'password']);
});

it('logs out an authenticated user', function (): void {
    $user = tenantUser(RoleEnum::Owner);

    $response = $this->actingAs($user)->post(tenantUrl($this->tenant, '/logout'));

    $response->assertRedirectContains('/login');
    $this->assertGuest();
});

it('redirects unverified users to the verification notice', function (): void {
    $user = tenantUser(RoleEnum::Owner, ['email_verified_at' => null]);

    $this->actingAs($user)
        ->get(tenantUrl($this->tenant, '/dashboard'))
        ->assertRedirectContains('/verify-email');
});

it('lets verified users reach the dashboard', function (): void {
    $user = tenantUser(RoleEnum::Owner);

    $this->actingAs($user)
        ->get(tenantUrl($this->tenant, '/dashboard'))
        ->assertOk();
});

it('sends a password reset link to a known email', function (): void {
    Notification::fake();

    $user = tenantUser(RoleEnum::Owner, ['email' => 'owner@acme.test']);

    $this->post(tenantUrl($this->tenant, '/forgot-password'), [
        'email' => 'owner@acme.test',
    ])->assertSessionHasNoErrors();

    Notification::assertSentTo($user, ResetPassword::class);
});

it('resets the password with a valid token', function (): void {
    $user = tenantUser(RoleEnum::Owner, ['email' => 'owner@acme.test']);
    $token = Password::broker()->createToken($user);

    $response = $this->post(tenantUrl($this->tenant, '/reset-password'), [
        'email' => 'owner@acme.test',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
        'token' => $token,
    ]);

    $response->assertRedirectContains('/login');

    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();
});

it('verifies the email through a signed link and logs the user in', function (): void {
    $user = tenantUser(RoleEnum::Owner, ['email' => 'owner@acme.test', 'email_verified_at' => null]);

    URL::forceRootUrl('http://acme.test');

    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $user->id,
        'hash' => sha1($user->email),
    ]);

    $response = $this->actingAs($user)->get($url);

    $response->assertRedirectContains('/dashboard');
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});
