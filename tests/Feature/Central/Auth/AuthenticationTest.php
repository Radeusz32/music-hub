<?php

declare(strict_types=1);

use App\Models\Central\Admin;
use Inertia\Testing\AssertableInertia as Assert;

const CENTRAL_PANEL = 'http://localhost/panel/central/superadmin';

it('renders the central login page for guests', function (): void {
    $this->get(CENTRAL_PANEL.'/login')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Central/Auth/Login'));
});

it('logs in a superadmin with valid credentials', function (): void {
    Admin::factory()->create(['email' => 'admin@central.test']);

    $response = $this->post(CENTRAL_PANEL.'/login', [
        'email' => 'admin@central.test',
        'password' => 'password',
    ]);

    $response->assertRedirect(route('central.dashboard'));
    $this->assertAuthenticated('superadmin');
});

it('rejects a superadmin login with an invalid password', function (): void {
    Admin::factory()->create(['email' => 'admin@central.test']);

    $response = $this->post(CENTRAL_PANEL.'/login', [
        'email' => 'admin@central.test',
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest('superadmin');
});

it('blocks the central dashboard for guests', function (): void {
    $this->get(CENTRAL_PANEL)
        ->assertRedirect(route('central.login'));
});

it('lets an authenticated superadmin reach the dashboard', function (): void {
    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'superadmin')
        ->get(CENTRAL_PANEL)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Central/Dashboard'));
});
