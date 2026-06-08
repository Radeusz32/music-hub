<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Users;

use App\Enums\Tenant\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Users\BulkDestroyUsersRequest;
use App\Http\Requests\Tenant\Users\StoreUserRequest;
use App\Http\Requests\Tenant\Users\UpdateUserRequest;
use App\Models\Tenant\User;
use App\Services\Tenant\Users\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class UserController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Tenant/Users/Index', [
            'users' => $this->userService->index($request),
            'roleOptions' => RoleEnum::options(),
        ]);
    }

    public function show(User $user): Response
    {
        return Inertia::render('Tenant/Users/Show', [
            'user' => $this->userService->show($user),
            'roleOptions' => RoleEnum::options(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->userService->create($request->validated());

        return redirect()
            ->route('tenant.users.index')
            ->with('success', 'Użytkownik został dodany.');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->userService->update($user, $request->validated());

        return redirect()
            ->route('tenant.users.index')
            ->with('success', 'Dane użytkownika zostały zaktualizowane.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Nie możesz usunąć własnego konta.');
        }

        $this->userService->delete($user);

        return redirect()
            ->route('tenant.users.index')
            ->with('success', 'Użytkownik został usunięty.');
    }

    public function bulkDestroy(BulkDestroyUsersRequest $request): RedirectResponse
    {
        $deleted = $this->userService->bulkDelete($request->validated()['ids'], (int) auth()->id());

        return redirect()
            ->route('tenant.users.index')
            ->with('success', "Usunięto zaznaczonych użytkowników ({$deleted}).");
    }
}
