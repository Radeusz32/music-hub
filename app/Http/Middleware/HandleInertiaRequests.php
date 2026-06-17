<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Central\Admin;
use App\Models\Central\Tenant;
use Illuminate\Http\Request;
use Inertia\Middleware;

final class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),

            'auth' => fn () => $this->resolveAuth($request),

            'tenant' => fn () => $this->resolveTenantData(),

            'flash' => fn () => [
                'success' => session()->get('success'),
                'error' => session()->get('error'),
            ],
        ];
    }

    private function resolveAuth(Request $request): ?array
    {
        $admin = auth('superadmin')->user();

        if ($admin instanceof Admin) {
            return [
                'user' => [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'email' => $admin->email,
                ],

                'permissions' => [],

                'roles' => ['superadmin'],

                'features' => [],
            ];
        }

        $user = $request->user();

        if (! $user) {
            return null;
        }

        $roles = $user->getRoleNames()
            ->values()
            ->all();

        $permissions = $user->getAllPermissions()
            ->pluck('name')
            ->values()
            ->all();

        $features = $this->resolveFeatures();

        return [
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'name' => $user->name,
                'email' => $user->email,
            ],

            'permissions' => $permissions,

            'roles' => $roles,

            'features' => $features,
        ];
    }

    /** @return array{company_name: string|null}|null */
    private function resolveTenantData(): ?array
    {
        $tenant = tenancy()->tenant;

        if (! $tenant instanceof Tenant) {
            return null;
        }

        return [
            'company_name' => $tenant->company_name,
        ];
    }

    /** @return list<string> */
    private function resolveFeatures(): array
    {
        $tenant = tenancy()->tenant;

        if (! $tenant instanceof Tenant) {
            return [];
        }

        return $tenant->features
            ->pluck('name')
            ->map(fn ($feature) => $feature->value)
            ->values()
            ->all();
    }
}
