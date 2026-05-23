<?php

declare(strict_types=1);

namespace App\Http\Middleware;

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
        ];
    }

    private function resolveAuth(Request $request): ?array
    {
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

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],

            'permissions' => $permissions,

            'roles' => $roles,

            'features' => $this->resolveFeatures(),
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
