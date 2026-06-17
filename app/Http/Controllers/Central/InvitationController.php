<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Enums\TenantInvitationStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Central\Invitation\StoreInvitationRequest;
use App\Models\Central\TenantInvitation;
use App\Services\Central\InvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class InvitationController extends Controller
{
    public function __construct(private readonly InvitationService $invitationService) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Central/Invitations/Index', [
            'invitations' => $this->invitationService->index($request),
            'statusOptions' => TenantInvitationStatusEnum::options(),
        ]);
    }

    public function show(TenantInvitation $invitation): Response
    {
        return Inertia::render('Central/Invitations/Show', [
            'invitation' => $this->invitationService->showData($invitation),
        ]);
    }

    public function store(StoreInvitationRequest $request): RedirectResponse
    {
        $this->invitationService->create($request->validated()['email']);

        return back()->with('success', 'Zaproszenie zostało wysłane.');
    }

    public function resend(TenantInvitation $invitation): RedirectResponse
    {
        if ($invitation->isAccepted()) {
            return back()->with('error', 'Nie można ponowić zaproszenia — organizacja już istnieje.');
        }

        $this->invitationService->resend($invitation);

        return back()->with('success', 'Zaproszenie zostało wysłane ponownie.');
    }

    public function accept(TenantInvitation $invitation): RedirectResponse
    {
        if (! $invitation->isFilled()) {
            return back()->with('error', 'Zaproszenie nie zostało jeszcze wypełnione.');
        }

        $this->invitationService->accept($invitation);

        return back()->with('success', 'Organizacja jest w trakcie tworzenia. Zostaniesz powiadomiony po zakończeniu.');
    }

    public function destroy(TenantInvitation $invitation): RedirectResponse
    {
        $invitation->delete();

        return redirect()->route('central.invitations.index')
            ->with('success', 'Zaproszenie zostało usunięte.');
    }
}
