<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class VerifyEmailController extends Controller
{
    /**
     * Verify the e-mail using the signed link, independent of the current
     * session, then log the user straight in. The link is signature-protected,
     * so possession of it (delivered only to the address being verified) is
     * sufficient proof — this supports admin-created accounts whose owner is
     * not logged in when they click it.
     */
    public function __invoke(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = User::query()->findOrFail($id);

        if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            abort(403);
        }

        $message = $user->hasVerifiedEmail()
            ? 'Adres e-mail jest już zweryfikowany.'
            : 'Adres e-mail został zweryfikowany.';

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();

            event(new Verified($user));
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('tenant.dashboard')->with('success', $message);
    }
}
