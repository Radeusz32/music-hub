<?php

declare(strict_types=1);

namespace App\Services\Tenant\Users;

use App\Http\Resources\Tenant\Users\UserDataTable;
use App\Models\Tenant\User;
use App\Services\BaseService;
use App\Transformers\Tenant\Users\UserTransformer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class UserService extends BaseService
{
    /**
     * Encrypted columns searchable by exact value via their blind indexes
     * (configured in {@see User::configureCipherSweet()}).
     *
     * @var list<string>
     */
    private const array BLIND_SEARCHABLE_COLUMNS = ['phone', 'postal_code', 'city', 'pesel'];

    /**
     * @return array<string, mixed>
     */
    public function index(Request $request): array
    {
        return $this->fetchForDataTable(UserDataTable::class, $request);
    }

    /**
     * @return array<string, mixed>
     */
    public function show(User $user): array
    {
        $user->loadMissing(UserTransformer::eagerLoads());

        return (new UserTransformer())->toArray($user, request());
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User
    {
        $role = $data['role'];
        unset($data['role']);

        $data['email_verified_at'] ??= now();

        $user = User::query()->create($data);
        $user->syncRoles([$role]);

        return $user;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): User
    {
        $role = $data['role'] ?? null;
        unset($data['role'], $data['password']);

        $user->update($data);

        if ($role !== null) {
            $user->syncRoles([$role]);
        }

        return $user->fresh();
    }

    public function delete(User $user): void
    {
        $user->delete();
    }

    /**
     * Delete multiple users by id, optionally excluding one (e.g. the current user).
     * Returns the number of users deleted.
     *
     * @param  array<int, int>  $ids
     */
    public function bulkDelete(array $ids, ?int $exceptId = null): int
    {
        return User::query()
            ->whereIn('id', $ids)
            ->when($exceptId !== null, fn ($query) => $query->whereKeyNot($exceptId))
            ->delete();
    }

    /**
     * Plain columns are matched with LIKE. The PII columns are encrypted at rest,
     * so they are matched by exact value through their CipherSweet blind indexes.
     *
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @param  array<int, string>  $columns
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    protected function applySearch(Builder $query, string $search, array $columns): Builder
    {
        return $query->where(function (Builder $q) use ($search, $columns): void {
            foreach ($columns as $column) {
                $q->orWhere($column, 'like', "%{$search}%");
            }

            foreach (self::BLIND_SEARCHABLE_COLUMNS as $column) {
                $q->orWhereBlind($column, $column, $search);
            }
        });
    }
}
