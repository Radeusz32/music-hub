# CRUD Generator - Agent Instructions

A complete, opinionated recipe for building a **new tenant CRUD module** in this
codebase, modeled 1:1 on the **Inventory / InventoryRecord** module. Follow it
top-to-bottom. When unsure about a detail, **open the matching Inventory file and
copy its structure** - Inventory is the canonical reference implementation.

> Read first: `DOCS.md` (architecture), `FILTERABLE_COLUMNS_GUIDE.md` (table
> filters), `README-BASE-COMPONENTS.md` (UI components).

---

## 0. Naming & golden rules

Pick two names and stay consistent:

- **Module** (feature group), e.g. `Inventory` → prefix `inventory`
- **Entity** (the model), e.g. `InventoryRecord`, table `inventory_records`

Worked placeholders used below - substitute throughout:

| Placeholder        | Inventory example                     |
| ------------------ | ------------------------------------- |
| `{Module}`         | `Inventory`                           |
| `{module}`         | `inventory`                           |
| `{Entity}`         | `InventoryRecord`                     |
| `{entity}`         | `inventoryRecord` (route param / var) |
| `{entities}` table | `inventory_records`                   |
| `{plural}` segment | `records`                             |
| permission prefix  | `inventory-records`                   |
| route name prefix  | `tenant.inventory.records`            |
| Inertia page dir   | `Tenant/Inventory`                    |

**Hard rules (enforced by CLAUDE.md / tooling):**

- `declare(strict_types=1);` in every PHP file; `final` classes.
- Explicit return types + param type hints everywhere. PHP 8 constructor promotion.
- Curly braces on all control structures. PHPDoc array shapes where useful.
- Validation lives in **FormRequest** classes (array-syntax rules + Polish `messages()`), never inline.
- Services extend **`BaseService`**; controllers stay thin (constructor-inject the service, return redirects/Inertia).
- Use `php artisan make:*` to scaffold, then edit. Pass `--no-interaction`.
- Tenant models go under `App\Models\Tenant`; tenant migrations under `database/migrations/tenant/`.
- Run `vendor/bin/pint` (PHP) and `npx prettier --write` + `npx vue-tsc --noEmit` (frontend) before finishing.
- UI: reuse **Base\* components**, the **composables**, **DataTable**, **BaseDialog**, and the **IndexLayout/ShowLayout** templates. Match the dark theme and Polish copy.

---

## 1. Backend

Build in this order (each step depends on the previous).

### 1.1 Enums (optional) - `app/Enums/Tenant/{Name}Enum.php`

For fixed option sets (status, type, condition…). Backed **string** enum with
`label()`, `color()`, and a static `options(): list<array{value,label,color}>`
used by both the frontend dropdowns/badges and column filters. Copy
`DiscFormatEnum` / `DiscConditionEnum`.

### 1.2 Migration - `database/migrations/tenant/xxxx_create_{entities}_table.php`

```bash
php artisan make:migration create_{entities}_table --no-interaction
# then MOVE it into database/migrations/tenant/
```

- `$table->id();`, typed columns, `->nullable()` where appropriate.
- For enum columns: `$table->enum('col', array_column({Name}Enum::cases(), 'value'))`.
- Money: `$table->decimal('col', 10, 2)->nullable();`
- Owner: `$table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();`
- Always `$table->timestamps();` and `$table->softDeletes();`.

If the entity stores files, also ensure the tenant `media` table migration exists
(Inventory ships `…_create_media_table.php` under `migrations/tenant/`).

### 1.3 Model - `app/Models/Tenant/{Entity}.php`

```php
final class {Entity} extends Model // implements HasMedia (only if it has files)
{
    /** @use HasFactory<{Entity}Factory> */
    use HasFactory, SoftDeletes; // + InteractsWithMedia when HasMedia

    protected $fillable = [ /* all writable columns + 'user_id' */ ];

    /** @return array<string, mixed> */
    public function casts(): array
    {
        return [
            'format' => {Name}Enum::class,
            'price'  => 'decimal:2',
            'year'   => 'integer',
            // ... + created_at/updated_at/deleted_at => 'datetime'
        ];
    }

    // Only if it has files:
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

Use `casts()` **method** (not `$casts`). Add a `@property-read` docblock like Inventory's.

#### 1.3.1 Encrypting sensitive fields (optional - CipherSweet)

If the entity stores **PII or other sensitive data** (national IDs, full address,
phone, bank/tax numbers, etc.), encrypt those columns at rest with CipherSweet
instead of storing plaintext. See **`DOCS.md` → Encrypted Attributes** for the
full mechanism. Quick recipe:

1. **Migration** - make every encrypted column `text` (ciphertext is long), e.g.
   `$table->text('pesel')->nullable();`. Do **not** add per-column hash columns.
2. **Model** - `implements CipherSweetEncrypted` + `use UsesCipherSweet`, and:
    ```php
    public static function configureCipherSweet(EncryptedRow $row): void
    {
        $row->addOptionalTextField('pesel')              // Optional = tolerates null
            ->addBlindIndex('pesel', new BlindIndex('pesel')); // only if it must be searchable
    }
    ```
    Do **not** give encrypted columns a `'string'`/`'encrypted'` cast (CipherSweet
    handles it via model events). Keep them in `$fillable`.
3. **Blind index table** - ensure the polymorphic `blind_indexes` migration exists
   in `database/migrations/tenant/` (shipped already; only encrypted models need it).
4. **Search / filters** - encrypted columns support **exact match only** (no `LIKE`,
   sort, or range). Keep them **out** of `searchableColumns()` /
   `allowedSortColumns()` / `filterableColumns()`, and search them by overriding
   `applySearch()` in the service with `orWhereBlind('col', 'col', $search)` (copy
   `UserService::applySearch()`).
5. **Uniqueness** - validate with `EncryptedUniqueRule` or a closure using
   `Model::whereBlind('col', 'col', $value)` (copy `Store/UpdateUserRequest`).

> If a sensitive field never needs to be searched, skip the blind index entirely -
> just `addOptionalTextField()` and it becomes write/read-only encrypted data.

### 1.4 Factory + Seeder

```bash
php artisan make:factory Tenant/{Entity}Factory --no-interaction
```

- Factory: realistic Polish fake data; add useful **states** (e.g. `outOfStock()`, `mint()`).
- Data seeder `database/seeders/Tenant/{Entities}Seeder.php`: a handful of hand-written
  realistic rows + `factory()->count(N)->create(['user_id' => $owner?->id])`.
  Grab the owner with `User::query()->first()`.
- Register it in `TenantDatabaseSeeder::run()` (after `OwnerSeeder`).

### 1.5 Permissions - `database/seeders/Tenant/{Module}PermissionsSeeder.php`

Extend `PermissionsBaseSeeder`. Permission names: `{module}-{plural}-{action}`.
Assign to role groups `$all` / `$admins` / `$owners`:

```php
final class {Module}PermissionsSeeder extends PermissionsBaseSeeder
{
    public function run(): void
    {
        $read = Permission::findOrCreate('{module}-{plural}-read', GuardEnum::Web->value);
        $this->setPermissions($this->all, $read);

        $create = Permission::findOrCreate('{module}-{plural}-create', GuardEnum::Web->value);
        $this->setPermissions($this->admins, $create);

        $update = Permission::findOrCreate('{module}-{plural}-update', GuardEnum::Web->value);
        $this->setPermissions($this->admins, $update);

        $delete = Permission::findOrCreate('{module}-{plural}-delete', GuardEnum::Web->value);
        $this->setPermissions($this->owners, $delete);
    }
}
```

Then add it to `AllPermissionsSeeder::run()`'s `$this->call([...])`. (Convention:
read → everyone, create/update → admins, delete → owners.)

### 1.6 Transformer - `app/Transformers/Tenant/{Module}/{Entity}Transformer.php`

Shapes each row for the frontend (the JSON the table/detail pages consume).

```php
final class {Entity}Transformer extends Transformer
{
    protected array $defaultIncludes = ['user'];

    /** @return list<string> */
    public static function eagerLoads(): array
    {
        return ['user:id,first_name,last_name,email']; // + 'media' if HasMedia
    }

    /** @return array<string, mixed> */
    public function transform(mixed $model): array
    {
        /** @var {Entity} $model */
        return [
            'id' => $model->id,
            // ... scalar fields; enums as ->value
            'cover_image' => $model->getFirstMediaUrl('cover') ?: null, // if HasMedia
            'created_at' => $model->created_at,
            'updated_at' => $model->updated_at,
        ];
    }

    /** @return array<string, mixed>|null */
    public function includeUser(mixed $model, mixed $_request = null): ?array
    {
        /** @var {Entity} $model */
        if (! $model->relationLoaded('user') || ! $model->user) {
            return null;
        }
        return (new UserTransformer())->transform($model->user);
    }
}
```

### 1.7 DataTableConfig - `app/Http/Resources/Tenant/{Module}/{Entity}DataTable.php`

Declares the server-side query rules (see `FILTERABLE_COLUMNS_GUIDE.md`).

```php
final class {Entity}DataTable extends DataTableConfig
{
    /** @return Builder<{Entity}> */
    public static function baseQuery(): Builder
    {
        return {Entity}::query()->with(self::transformer()::eagerLoads());
    }

    /** @return list<string> */
    public static function searchableColumns(): array { return ['name', /* ... */]; }

    /** @return list<string> */
    public static function allowedSortColumns(): array { return ['name', 'created_at', /* ... */]; }

    /** @return array<string, array{column: string, type: string, options?: array<int, array{value: string, label: string}>}> */
    public static function filterableColumns(): array
    {
        return [
            'status'     => ['column' => 'status', 'type' => 'select', 'options' => {Name}Enum::options()],
            'price'      => ['column' => 'price',  'type' => 'number-range'],
            'created_at' => ['column' => 'created_at', 'type' => 'date-range'],
        ];
    }

    public static function defaultSort(): string { return 'name'; }
    public static function defaultDirection(): string { return 'asc'; }
    public static function defaultPerPage(): int { return 20; }

    protected static function transformer(): Transformer { return new {Entity}Transformer(); }
}
```

### 1.8 Service - `app/Services/Tenant/{Module}/{Entity}Service.php`

Extend `BaseService`. `index()` just delegates to `fetchForDataTable`. Keep CRUD
methods small; add `bulkDelete` for table bulk actions. Use the `ManagesFiles`
trait for media.

```php
final class {Entity}Service extends BaseService
{
    use ManagesFiles; // only if it has files

    /** @return array<string, mixed> */
    public function index(Request $request): array
    {
        return $this->fetchForDataTable({Entity}DataTable::class, $request);
    }

    /** @return array<string, mixed> */
    public function show({Entity} $model): array
    {
        $model->loadMissing({Entity}Transformer::eagerLoads());
        return (new {Entity}Transformer())->toArray($model, request());
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): {Entity} { return {Entity}::query()->create($data); }

    /** @param array<string, mixed> $data */
    public function update({Entity} $model, array $data): {Entity}
    {
        $model->update($data);
        return $model->fresh();
    }

    public function delete({Entity} $model): void { $model->delete(); }

    /** @param array<int, int> $ids */
    public function bulkDelete(array $ids): int
    {
        return {Entity}::query()->whereIn('id', $ids)->delete();
    }
}
```

### 1.9 FormRequests - `app/Http/Requests/Tenant/{Module}/`

One per write action. `authorize()` returns `true` (route middleware enforces
permissions). Array-syntax rules + Polish `messages()`. Mirror Inventory:

- `Store{Entity}Request` - full create rules (`required`, `Rule::enum(...)`, `max:`…).
- `Update{Entity}Request` - usually the same rule set.
- `BulkDestroy{Entities}Request` - `'ids' => ['required','array','min:1'], 'ids.*' => ['integer']`.
- `Upload…Request` / `Import…Request` - file rules (`file`, `image`, `mimes:…`, `max:KB`) - only if relevant.

### 1.10 Controller - `app/Http/Controllers/Tenant/{Module}/{Entity}Controller.php`

Thin. Constructor-inject the service. Return `Inertia::render` (reads) and
`redirect()->route(...)->with('success', ...)` (writes). Pass enum `options()`
to the frontend.

```php
final class {Entity}Controller extends Controller
{
    public function __construct(private readonly {Entity}Service $service) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Tenant/{Module}/Index', [
            'records' => $this->service->index($request),
            'statusOptions' => {Name}Enum::options(), // enum options for filters/forms
        ]);
    }

    public function store(Store{Entity}Request $request): RedirectResponse
    {
        $this->service->create(array_merge($request->validated(), ['user_id' => auth()->id()]));
        return redirect()->route('{routePrefix}.index')->with('success', '... dodano.');
    }

    public function show({Entity} $model): Response { /* Inertia::render('Tenant/{Module}/Show', [...]) */ }
    public function update(Update{Entity}Request $request, {Entity} $model): RedirectResponse { /* ... */ }
    public function destroy({Entity} $model): RedirectResponse { /* ... */ }

    public function bulkDestroy(BulkDestroy{Entities}Request $request): RedirectResponse
    {
        $deleted = $this->service->bulkDelete($request->validated()['ids']);
        return redirect()->route('{routePrefix}.index')->with('success', "Usunięto ({$deleted}).");
    }
}
```

### 1.11 Routes - `routes/tenant.php`

Inside the authenticated group, add a feature-gated prefix. **Literal collection
routes first, model-bound `{param}` routes after.** Each write action gets a
`permission:` middleware.

```php
Route::prefix('{module}')
    ->middleware('feature:{module}')
    ->group(function (): void {
        Route::prefix('{plural}')->group(function (): void {
            Route::get('/', [{Entity}Controller::class, 'index'])
                ->name('{routePrefix}.index')->middleware('permission:{module}-{plural}-read');
            Route::post('/', [{Entity}Controller::class, 'store'])
                ->name('{routePrefix}.store')->middleware('permission:{module}-{plural}-create');
            Route::post('/bulk-destroy', [{Entity}Controller::class, 'bulkDestroy'])
                ->name('{routePrefix}.bulk-destroy')->middleware('permission:{module}-{plural}-delete');

            Route::get('/{{entity}}', [{Entity}Controller::class, 'show'])
                ->name('{routePrefix}.show')->middleware('permission:{module}-{plural}-read');
            Route::put('/{{entity}}', [{Entity}Controller::class, 'update'])
                ->name('{routePrefix}.update')->middleware('permission:{module}-{plural}-update');
            Route::delete('/{{entity}}', [{Entity}Controller::class, 'destroy'])
                ->name('{routePrefix}.destroy')->middleware('permission:{module}-{plural}-delete');
        });
    });
```

`/bulk-destroy` is **POST** (reliable request body for the `ids[]`).

### 1.12 Feature flag & menu (only for a brand-new module)

If the module is new (not just a new entity inside an existing module):

1. Add a case to `FeatureEnum` (value + label).
2. Assign it to the dev tenant in `CentralTenantSeeder` (the `sync()` list).
3. Add a nav entry in `resources/js/composables/Tenant/useMenu.ts` under the
   right top-level group, with `feature` and optional `permission`. The menu is
   auto-filtered by `hasFeature` + `hasPermission`, so only declare the keys.

---

## 2. Frontend (`resources/js/Pages/Tenant/{Module}/`)

### 2.1 Resource file - `{module}.resource.ts`

Single source of TS types + form defaults + display helpers + column builder.

```ts
import type { ColumnDef, FilterOption } from "@/types/datatable";
export type { ColumnDef, FilterOption };

export interface {Entity} { id: number; name: string; /* ...; */ created_at: string; updated_at: string; }
export interface {Entity}FormData { name: string; /* writable fields, no id */ }
export const default{Entity}Form: {Entity}FormData = { name: "", /* ... */ };

// Reuse generic helpers (do NOT re-implement currency/date):
//   import { useMoney } from "@/composables/useMoney";
//   import { useDate } from "@/composables/useDate";
export function resolveOptionLabel(options: FilterOption[], v: string): string { /* find label */ }
export function resolveOptionColor(options: FilterOption[], v: string): string { /* find color */ }

export interface BuildColumnsOptions { statusOptions: FilterOption[]; /* ... */ }
export function build{Entity}Columns(o: BuildColumnsOptions): ColumnDef[] {
    return [
        { key: "name", label: "Nazwa", sortable: true },
        { key: "status", label: "Status", sortable: true, width: "120px",
          filter: { type: "select", options: o.statusOptions } },
        { key: "price", label: "Cena", sortable: true, width: "150px", align: "right",
          filter: { type: "number-range", fromKey: "price_from", toKey: "price_to", currency: true, min: 0 } },
        { key: "created_at", label: "Dodano", sortable: true, width: "180px",
          filter: { type: "date-range", fromKey: "created_at_from", toKey: "created_at_to" } },
    ];
}
```

Keep column `filter` keys in sync with the backend `filterableColumns()`.

### 2.2 Table composable - `composables/Tenant/use{Entity}Table.ts`

Wrap the generic `useTable` (server-side state) and expose columns + display
helpers (pulling shared `useMoney`/`useDate`). Copy `useInventoryTable.ts`.

```ts
export function use{Entity}Table(options: { initialFilters?: TableFilters; statusOptions: FilterOption[]; }) {
    const table = useTable({ routeName: "{routePrefix}.index", initialFilters: options.initialFilters,
        defaultSort: "name", defaultDirection: "asc", defaultPerPage: 20 });
    const { formatPrice } = useMoney();
    const { formatDate } = useDate();
    const columns = build{Entity}Columns({ statusOptions: options.statusOptions });
    return { ...table, columns, formatPrice, formatDate, /* badge/label helpers */ };
}
```

### 2.3 Index page - `Index.vue`

`IndexLayout` (title + `#toolbar`) wrapping `DataTable`. Wire all events to the
composable. Modals for create/edit (and import if relevant). Handlers use
`useForm` + `useToast`.

```vue
<DataTable
    v-model:search="table.search.value"
    :columns="table.columns"
    :rows="records.data as unknown as Record<string, unknown>[]"
    :pagination="records as unknown as Pagination"
    :sort-by="table.sortBy.value"
    :direction="table.direction.value"
    :filter-values="table.extraFilters.value"
    searchable
    row-route="{routePrefix}.show"
    @search="table.onSearchInput"
    @sort="table.toggleSort"
    @page="table.goToPage"
    @filter="table.setFilter"
    @clear-filters="table.clearFilters"
    @edit="openEdit"
    @delete="handleDelete"
    @bulk-delete="handleBulkDelete"
>
    <template #cell-status="{ value }"><!-- custom badge --></template>
    <template #delete-confirm-text="{ row }">Usunąć <strong>{{ row?.name }}</strong>?</template>
</DataTable>
```

Handlers: `form.post/put` on submit, `deleteForm.delete(route('{routePrefix}.destroy', { {entity}: row.id }))`,
`bulkForm.post(route('{routePrefix}.bulk-destroy'))`. Toast each success.

**`DataTable` API quick reference:**

- Props: `columns, rows, pagination, sortBy, direction, filterValues, searchable, search, searchPlaceholder, rowRoute, loading, emptyMessage, canEdit, canDelete`.
- Emits: `search, update:search, sort, page, filter(key,value), clear-filters, edit(row), delete(row), bulk-delete(ids)`.
- Slots: `cell-{key}`, `toolbar`, `delete-confirm-text`.
- Built-in: search box, per-column filters, row selection, **bulk actions** (≥2 selected → "Akcje grupowe" with delete), **clear-filters**, top mirror scrollbar.
- `canEdit` / `canDelete` (both default `true`) hide the per-row edit/delete buttons and the bulk-delete action when `false` - pass your permission flags here (see 2.7).

### 2.4 Create/Edit modal - `{Entity}Modal.vue`

Built on **`BaseDialog`**; manage width + columns with **Tailwind** at the call
site (`panel-class`, `flex md:flex-row`). Use **Base\* components** for every
field with `:error="!!form.errors.x"` + a `<small>` error. Expose `show` /
`@close` / `@submit`; map `show`→`BaseDialog` `:visible`. Copy
`InventoryRecordModal.vue`.

```vue
<BaseDialog
    :visible="show"
    :title="title"
    panel-class="w-11/12 max-w-5xl"
    @update:visible="
        (v) => {
            if (!v) emit('close');
        }
    "
>
    <form id="{entity}-form" class="flex w-full flex-col gap-5 md:flex-row" @submit.prevent="emit('submit')">
        <section class="flex flex-1 flex-col gap-3.5 rounded-xl border border-sky-400/10 bg-slate-950/40 p-4">
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-medium uppercase tracking-wide text-slate-400">Nazwa *</label>
                <BaseInput v-model="form.name" :error="!!form.errors.name" />
                <small v-if="form.errors.name" class="text-xs text-red-400">{{ form.errors.name }}</small>
            </div>
            <!-- BaseDropdown / BaseInputNumber (currency) / BaseTextArea ... -->
        </section>
    </form>
    <template #footer>
        <button type="button" @click="emit('close')">Anuluj</button>
        <button type="submit" form="{entity}-form" :disabled="form.processing">Zapisz</button>
    </template>
</BaseDialog>
```

### 2.5 Detail page - `Show.vue` + `Show/` components (if you need a detail view)

`ShowLayout` (title + `#actions` slot for back/edit/delete buttons). Split the
page into **section components** under `Show/` (e.g. `*HeroCard`, `*DetailsCard`,
`*MetaCard`), and reuse the confirm dialog. Copy the Inventory `Show.vue`
structure. Use `useMoney`/`useDate` for formatting; pass enum `options` for
labels/colors.

### 2.6 Confirm/secondary dialogs

Reuse `BaseDialog` with `align="center"`, `:mobile-fullscreen="false"`,
`:show-close="false"` for compact confirmations (see `InventoryDeleteDialog.vue`).
The table's own row-delete + bulk-delete already use `DataTableDeleteDialog`.

### 2.7 Gate CRUD action buttons (feature + permission)

The route middleware (`feature:` + `permission:`) is the real enforcement, but
**every CRUD action button must also be hidden on the frontend** when the user
lacks the matching feature/permission - a second, UX layer of the same rule used
by `useMenu` (`hasFeature(...) && hasPermission(...)`).

In the **Index** and **Show** pages, compute capability flags once and reuse them:

```ts
import { usePermissions } from "@/composables/Tenant/usePermissions";
import { useFeatures } from "@/composables/Tenant/useFeatures";

const { hasPermission } = usePermissions();
const { hasFeature } = useFeatures();

const canCreate = computed(
    () => hasFeature("{module}") && hasPermission("{module}-{plural}-create"),
);
const canUpdate = computed(
    () => hasFeature("{module}") && hasPermission("{module}-{plural}-update"),
);
const canDelete = computed(
    () => hasFeature("{module}") && hasPermission("{module}-{plural}-delete"),
);
```

Then:

- **Toolbar buttons** (`Dodaj`, `Importuj`, …) → `v-if="canCreate"` (use `canRead` for read-only utilities like export).
- **Row + bulk actions** → pass the flags to `DataTable`: `:can-edit="canUpdate" :can-delete="canDelete"`. The shared `DataTable`/`DataTableRow` hide the row edit/delete buttons and the "Akcje grupowe" bulk-delete accordingly (both props default `true`, so untouched tables are unaffected).
- **Show page actions** (`Edytuj`, `Usuń`) → `v-if="canUpdate"` / `v-if="canDelete"` (combine with any extra guard, e.g. `canDelete && !isSelf`).

Keep the permission strings in sync with the `{Module}PermissionsSeeder` and the
route middleware. Read → everyone/admins, create/update → admins, delete → owners.

---

## 3. Verify

```bash
vendor/bin/pint                          # PHP formatting (run before finishing)
npx vue-tsc --noEmit -p tsconfig.json    # frontend type-check
npx prettier --write resources/js/<changed files>
php artisan test --filter={Entity}       # add/adjust Pest tests for the change
```

- Routes are available to the frontend at runtime via the `@routes` directive - no rebuild needed, but `npm run dev` must be running to see UI changes.
- Re-seed after migration/permission changes: `make reset` (or `php artisan migrate:fresh --seed && php artisan tenants:seed`).

> **Tests:** the project rule is "every change is programmatically tested" (Pest).
> Tenant-DB feature tests need tenancy bootstrapped in the test - if the harness
> for that isn't set up yet, flag it and cover what you can (pure units like
> filter resolution, transformers, request rules via datasets).

---

## 4. Copy-paste checklist

Backend:

- [ ] Enum(s) with `label()/color()/options()` (if fixed option sets)
- [ ] Tenant migration in `database/migrations/tenant/` (timestamps + softDeletes)
- [ ] `App\Models\Tenant\{Entity}` - `casts()`, `$fillable`, relations, SoftDeletes (+ HasMedia if files)
- [ ] (Sensitive PII?) CipherSweet: `text` columns + `configureCipherSweet()` + blind-index search/uniqueness (§1.3.1)
- [ ] Factory (+ states) and `{Entities}Seeder`, registered in `TenantDatabaseSeeder`
- [ ] `{Module}PermissionsSeeder` registered in `AllPermissionsSeeder`
- [ ] `{Entity}Transformer` (+ `eagerLoads`, includes)
- [ ] `{Entity}DataTable` (searchable/sortable/filterable/defaults/transformer)
- [ ] `{Entity}Service extends BaseService` (index → fetchForDataTable, CRUD, bulkDelete)
- [ ] FormRequests: Store / Update / BulkDestroy (+ Upload/Import if needed)
- [ ] Thin `{Entity}Controller` (constructor-injected service, Inertia + redirects)
- [ ] Routes in `tenant.php` (feature + permission middleware; collection then model-bound)
- [ ] (New module only) `FeatureEnum` case + `CentralTenantSeeder` assignment

Frontend:

- [ ] `{module}.resource.ts` (types, default form, column builder, option helpers)
- [ ] `use{Entity}Table.ts` (wraps `useTable`, columns + `useMoney`/`useDate`)
- [ ] `Index.vue` (IndexLayout + DataTable + modals, all events wired)
- [ ] `{Entity}Modal.vue` (BaseDialog + Base\* fields)
- [ ] `Show.vue` (+ `Show/` section components) - if a detail page is needed
- [ ] CRUD buttons gated by `hasFeature(...) && hasPermission(...)` - toolbar `v-if`, `DataTable :can-edit/:can-delete`, Show actions (see 2.7)
- [ ] `useMenu.ts` nav entry (feature/permission keys)

---

## 5. Reference files (study these)

| Concern            | File                                                                  |
| ------------------ | --------------------------------------------------------------------- |
| Controller         | `app/Http/Controllers/Tenant/Inventory/InventoryRecordController.php` |
| Service            | `app/Services/Tenant/Inventory/InventoryRecordService.php`            |
| BaseService        | `app/Services/BaseService.php`                                        |
| DataTableConfig    | `app/Http/Resources/Tenant/Inventory/InventoryRecordDataTable.php`    |
| Transformer        | `app/Transformers/Tenant/Inventory/InventoryRecordTransformer.php`    |
| Model              | `app/Models/Tenant/InventoryRecord.php`                               |
| FormRequests       | `app/Http/Requests/Tenant/Inventory/*`                                |
| Permissions seeder | `database/seeders/Tenant/InventoryPermissionsSeeder.php`              |
| Data seeder        | `database/seeders/Tenant/InventoryRecordsSeeder.php`                  |
| Routes             | `routes/tenant.php` (inventory group)                                 |
| Resource (TS)      | `resources/js/Pages/Tenant/Inventory/inventory.resource.ts`           |
| Table composable   | `resources/js/composables/Tenant/useInventoryTable.ts`                |
| Index page         | `resources/js/Pages/Tenant/Inventory/Index.vue`                       |
| Modal              | `resources/js/Pages/Tenant/Inventory/InventoryRecordModal.vue`        |
| Detail page        | `resources/js/Pages/Tenant/Inventory/Show.vue` (+ `Show/`)            |
| DataTable          | `resources/js/Pages/Tenant/Components/DataTable.vue`                  |
| Layouts            | `resources/js/layout/Tenant/{IndexLayout,ShowLayout,PageToolbar}.vue` |
