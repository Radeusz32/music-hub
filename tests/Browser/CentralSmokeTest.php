<?php

declare(strict_types=1);

use App\Models\Central\Admin;

it('renders the central login page without JavaScript errors', function (): void {
    $page = visit('/panel/central/superadmin/login');

    $page->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

it('logs a superadmin in through the browser', function (): void {
    Admin::factory()->create(['email' => 'admin@central.test']);

    $page = visit('/panel/central/superadmin/login');

    $page->assertSee('Panel administracyjny')
        ->fill('email', 'admin@central.test')
        ->fill('password', 'password')
        ->press('Zaloguj się')
        ->assertPathIs('/panel/central/superadmin')
        ->assertNoJavascriptErrors();
});
