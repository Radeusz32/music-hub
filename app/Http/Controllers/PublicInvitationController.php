<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TenantInvitationStatusEnum;
use App\Http\Requests\Central\Invitation\FillInvitationRequest;
use App\Services\Central\InvitationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

final class PublicInvitationController extends Controller
{
    public function __construct(private readonly InvitationService $invitationService) {}

    public function show(string $uuid): Response|RedirectResponse
    {
        $invitation = $this->invitationService->findByUuid($uuid);

        if (! $invitation) {
            return Inertia::render('Public/InvitationExpired', ['reason' => 'not_found']);
        }

        if ($invitation->isExpired()) {
            $invitation->update(['status' => TenantInvitationStatusEnum::Expired]);

            return Inertia::render('Public/InvitationExpired', ['reason' => 'expired']);
        }

        if (! $invitation->isPending()) {
            return Inertia::render('Public/InvitationExpired', ['reason' => $invitation->status->value]);
        }

        return Inertia::render('Public/Invitation', [
            'invitation' => [
                'uuid' => $invitation->uuid,
                'email' => $invitation->email,
                'expires_at' => $invitation->expires_at->toISOString(),
            ],
            'baseDomain' => config('app.base_domain'),
        ]);
    }

    public function validateStep(Request $request, string $uuid): JsonResponse
    {
        $invitation = $this->invitationService->findByUuid($uuid);

        if (! $invitation || ! $invitation->isPending() || $invitation->isExpired()) {
            return response()->json(['message' => 'Zaproszenie wygasło lub jest nieprawidłowe.'], 410);
        }

        $rules = FillInvitationRequest::stepRules()[$request->input('step')] ?? null;

        if ($rules === null) {
            return response()->json(['message' => 'Nieznany krok formularza.'], 422);
        }

        Validator::make($request->all(), $rules, (new FillInvitationRequest())->messages())->validate();

        return response()->json(['valid' => true]);
    }

    public function submit(FillInvitationRequest $request, string $uuid): Response|RedirectResponse
    {
        $invitation = $this->invitationService->findByUuid($uuid);

        if (! $invitation || ! $invitation->isPending() || $invitation->isExpired()) {
            return Inertia::render('Public/InvitationExpired', ['reason' => 'expired']);
        }

        $data = $request->validated();

        $companyData = array_intersect_key($data, array_flip([
            'domain', 'company_name', 'tax_id', 'regon', 'krs_number',
            'company_email', 'company_phone', 'website',
            'street', 'building_number', 'apartment_number',
            'postal_code', 'city', 'country',
        ]));

        $ownerData = array_intersect_key($data, array_flip([
            'first_name', 'last_name', 'phone', 'pesel', 'password',
        ]));
        $ownerData['email'] = $invitation->email;

        $this->invitationService->fill($invitation, $companyData, $ownerData);

        return Inertia::render('Public/InvitationSuccess', [
            'email' => $invitation->email,
        ]);
    }
}
