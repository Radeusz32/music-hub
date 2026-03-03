<?php

declare(strict_types=1);

namespace App\Services\Tenant\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

final class LoginService
{
    public function attempt(string $email, string $password): void
    {
        if (! Auth::guard('tenant')->attempt([
            'email' => $email,
            'password' => $password,
        ])) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }
    }
}
