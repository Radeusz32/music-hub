<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Auth\LoginRequest;
use App\Services\Tenant\Auth\LoginService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class LoginController extends Controller
{
    public function __construct(
        private readonly LoginService $loginService,
    ) {}

    public function create(): Response
    {
        return Inertia::render('Tenant/Auth/Login');
    }

    public function store(LoginRequest $request): RedirectResponse
{
    $credentials = $request->validated();

    $this->loginService->login(
        $credentials['email'],
        $credentials['password'],
    );

    $request->session()->regenerate();

    return redirect()->route('dashboard');
}

    public function destroy(): RedirectResponse
    {
        auth('tenant')->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('tenant.login');
    }
}
