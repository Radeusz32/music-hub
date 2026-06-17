# SoundBased — Project Documentation

## Overview

Multi-tenant SaaS platform for music stores. Each tenant (store) runs on its own subdomain with an isolated database. The central database manages tenants, domains, and feature flags.

**App name:** SoundBased
**Base domain:** `localhost` (dev) — tenants at `{slug}.localhost`
**Default tenant (dev):** `music1.localhost`

---

## Tech Stack

### Backend

| Package                     | Version |
| --------------------------- | ------- |
| PHP                         | 8.4     |
| Laravel                     | ^12     |
| stancl/tenancy              | ^3.9    |
| spatie/laravel-permission   | ^7.0    |
| spatie/laravel-medialibrary | ^11     |
| spatie/laravel-ciphersweet  | ^1.7    |
| maatwebsite/excel           | ^3.1    |
| laravel/pint                | ^1      |
| pestphp/pest                | ^4      |

### Frontend

| Package      | Version |
| ------------ | ------- |
| Vue          | ^3.5    |
| Inertia.js   | ^2.3    |
| PrimeVue     | ^4.5    |
| Tailwind CSS | ^4      |
| chart.js     | ^4.5    |
| TypeScript   | —       |
| Vite         | —       |

---

## Architecture

### Multi-tenancy

Uses `stancl/tenancy` with domain-based tenant identification. Each tenant has:

- Own MySQL database (`tenant{uuid}`)
- Own subdomain (`{slug}.{base_domain}`)

**Central database** (`laravel`): tenants, domains, features, feature_tenant, cache, jobs
**Tenant databases**: users, roles, permissions, inventory_records, media (per module)

### Database Connections

| Connection        | Database       | Purpose                     |
| ----------------- | -------------- | --------------------------- |
| `mysql` (default) | `laravel`      | Central — tenants, features |
| dynamic           | `tenant{uuid}` | Per-tenant data             |

> Tenant Eloquent models live in `App\Models\Tenant\*`. During a tenant request the default connection is swapped to the tenant DB, so plain `Model::query()` calls hit the correct tenant database automatically.

---

## Directory Structure (key paths)

```
app/
├── Console/Commands/
│   ├── Tenant/RemoveUnusedTenants.php   # Drops orphaned tenant databases
│   └── StorageLinkTenants.php           # Per-tenant storage symlinks
├── Enums/
│   ├── FeatureEnum.php                  # Available modules/features
│   ├── GuardEnum.php                    # Auth guards
│   └── Tenant/
│       ├── RoleEnum.php                 # owner | admin | user
│       ├── DiscFormatEnum.php           # Inventory: LP, CD, ... (+ label()/color()/options())
│       ├── DiscConditionEnum.php        # Inventory: M, NM, VG+, ... (+ label()/color()/options())
│       └── InventoryMovementTypeEnum.php # Stock movement types (+ label()/color()/sign()/options()/manualOptions())
├── Http/
│   ├── Controllers/Tenant/
│   │   ├── Auth/AuthController.php
│   │   └── Inventory/
│   │       ├── InventoryRecordController.php
│   │       ├── InventoryMovementController.php   # index/store/destroy/bulk-destroy
│   │       └── InventorySaleController.php       # index (POS panel) / store (sell)
│   ├── Middleware/
│   │   ├── CheckFeature.php             # Blocks routes by tenant feature
│   │   └── HandleInertiaRequests.php
│   ├── Requests/Tenant/Inventory/       # One FormRequest per write action
│   │   ├── StoreInventoryRecordRequest.php
│   │   ├── UpdateInventoryRecordRequest.php
│   │   ├── BulkDestroyInventoryRecordsRequest.php
│   │   ├── UploadCoverRequest.php
│   │   ├── ImportInventoryRecordsRequest.php
│   │   ├── StoreInventoryMovementRequest.php
│   │   ├── BulkDestroyInventoryMovementsRequest.php
│   │   └── StoreInventorySaleRequest.php
│   └── Resources/
│       ├── DataTableConfig.php          # Abstract base for server-side table queries
│       └── Tenant/Inventory/
│           ├── InventoryRecordDataTable.php
│           └── InventoryMovementDataTable.php
├── Models/
│   ├── Central/{Domain,Feature,Tenant}.php
│   └── Tenant/
│       ├── User.php                     # HasRoles (Spatie), HasFactory
│       ├── InventoryRecord.php          # SoftDeletes, HasMedia, casts(), relations (hasMany movements); purchase_price_per_unit (no sale_price)
│       └── InventoryMovement.php        # SoftDeletes, casts(), belongsTo record + user; sale_price (set only for Sale)
├── Providers/{AppServiceProvider,TenancyServiceProvider}.php
├── Services/
│   ├── BaseService.php                  # fetchForDataTable() + filter strategies
│   ├── Tenant/Auth/LoginService.php
│   └── Tenant/Inventory/
│       ├── InventoryRecordService.php          # delegates stock logging to InventoryRecordMovementsService
│       ├── InventoryRecordMovementsService.php # ledger writes + stock mutation (record/recordSale/delete/bulkDelete)
│       └── InventorySaleService.php            # POS: sellableRecords/recentSales/todayStats, sell() → recordSale()
├── Transformers/
│   ├── Transformer.php                  # Abstract: transform() + eagerLoads() + includes
│   ├── Tenant/Users/UserTransformer.php
│   └── Tenant/Inventory/
│       ├── InventoryRecordTransformer.php   # includes movements[] on the detail page
│       └── InventoryMovementTransformer.php
├── Traits/ManagesFiles.php              # Media upload/destroy + hooks
├── Imports/Tenant/Inventory/InventoryRecordImport.php   # maatwebsite/excel
├── Exports/Tenant/Inventory/InventoryRecordExport.php   # template export
├── Jobs/CreateTenantStorageLink.php
└── MediaLibrary/TenantUrlGenerator.php  # Tenant-aware media URLs

database/
├── migrations/                          # Central migrations
├── migrations/tenant/                   # Per-tenant (users, permissions, inventory_records, inventory_movements, media)
├── factories/Tenant/{UserFactory,InventoryRecordFactory,InventoryMovementFactory}.php
└── seeders/
    ├── DatabaseSeeder.php               # FeaturesSeeder → CentralTenantSeeder
    ├── FeaturesSeeder.php
    ├── CentralTenantSeeder.php
    ├── TenantDatabaseSeeder.php         # Roles → AllPermissions → Owner → Users → InventoryRecords → InventoryMovements
    └── Tenant/
        ├── RolesSeeder.php
        ├── AllPermissionsSeeder.php             # Calls every *PermissionsSeeder
        ├── PermissionsBaseSeeder.php            # Abstract: role groups + setPermissions()
        ├── InventoryPermissionsSeeder.php       # extends PermissionsBaseSeeder
        ├── AnalyticsPermissionsSeeder.php       # analytics-read → admins
        ├── OwnerSeeder.php
        ├── InventoryRecordsSeeder.php
        └── InventoryMovementsSeeder.php         # Seeds an Initial entry per record

resources/js/
├── Components/                          # Globally-registered UI primitives
│   ├── BaseInput.vue  BaseInputNumber.vue  BaseTextArea.vue  BaseDropdown.vue
│   ├── BaseCheckbox.vue  BasePassword.vue  BaseMaskedInput.vue
│   ├── BaseDialog.vue                   # Modal shell (size + columns via Tailwind)
│   ├── BaseTab.vue                      # Tabbed panel (used on Inventory Show)
│   ├── StepByStep.vue                   # Multi-step wizard (explicit import)
│   ├── FileUpload.vue  DatePicker.vue  Tooltip.vue  AppToast.vue
├── composables/
│   ├── useToast.ts  useMoney.ts  useDate.ts  useStepByStep.ts
│   └── Tenant/
│       ├── useFeatures.ts  usePermissions.ts  useMenu.ts
│       ├── useTable.ts                  # Generic server-side table state
│       ├── useInventoryTable.ts         # Module wrapper (columns + display helpers)
│       └── useMovementsTable.ts         # Stock-movements table wrapper
├── layout/Tenant/
│   ├── AppLayout.vue  AppSidebar.vue  AppTopbar.vue  AppFooter.vue
│   ├── IndexLayout.vue                  # Title/toolbar wrapper for list pages
│   ├── ShowLayout.vue                   # Title/actions wrapper for detail pages
│   └── PageToolbar.vue                  # Shared toolbar card
├── Pages/Tenant/
│   ├── Auth/Login.vue   Dashboard.vue
│   ├── Components/
│   │   ├── DataTable.vue                # Reusable server-side table
│   │   └── DataTableComponents/*        # Header, Row, Pagination, ColumnFilter, DeleteDialog
│   └── Inventory/
│       ├── Index.vue   Show.vue   inventory.resource.ts   movements.resource.ts
│       ├── InventoryRecordModal.vue   InventoryImportModal.vue   InventoryMovementModal.vue
│       ├── Movements/Index.vue          # Stock-movements list page
│       └── Show/*                       # HeroCard, Cover, DetailsCard, MetaCard, Lightbox, VinylDisc, HistoryCard
├── plugins/
│   ├── primevue.ts                      # PrimeVue + Aura dark preset
│   └── base-components.ts               # Registers all Base* globally
└── types/{datatable.ts, inertia.d.ts}
```

---

## Enums

### `FeatureEnum` — available modules

| Case           | Value          | Label       |
| -------------- | -------------- | ----------- |
| `Inventory`    | `inventory`    | Magazyn     |
| `Trading`      | `trading`      | Giełdy      |
| `Analytics`    | `analytics`    | Analityka   |
| `Integrations` | `integrations` | Integracje  |
| `Users`        | `users`        | Użytkownicy |
| `Settings`     | `settings`     | Ustawienia  |

### `TenantInvitationStatusEnum` — invitation lifecycle

| Case       | Value      | Label         |
| ---------- | ---------- | ------------- |
| `Pending`  | `PENDING`  | Oczekuje      |
| `Filled`   | `FILLED`   | Wypełnione    |
| `Accepted` | `ACCEPTED` | Zaakceptowane |
| `Expired`  | `EXPIRED`  | Wygasłe       |

`options()` / `label()` / `color()` follow the same pattern as other enums.

### `RoleEnum` — tenant user roles

| Case    | Value   |
| ------- | ------- |
| `Owner` | `owner` |
| `Admin` | `admin` |
| `User`  | `user`  |

### Inventory enums (backed string enums with `label()`, `color()`, `options()`)

- `DiscFormatEnum` — `LP, EP, Single, Double LP, CD, CD Single, DVD, Blu-Ray, Cassette, VHS, Digital, Box Set`
- `DiscConditionEnum` — `M, NM, VG+, VG, G+, G, F, P`
- `InventoryMovementTypeEnum` — `Initial, In, Return, Correction` (`sign() = +1`) and `Out, Sale, Loss` (`sign() = -1`). `manualOptions()` exposes only the four user-selectable types (`In, Out, Return, Loss`); `Initial`/`Correction` are produced automatically by the system, and `Sale` is produced by the Sales panel (see **Inventory Sales**).

`options()` returns `list<array{value,label,color}>` and is passed to the frontend for dropdowns/badges.

---

## Features / Modules System

Features are defined centrally and assigned per tenant via the `feature_tenant` pivot.

**Flow:**

1. `FeatureEnum` — source of truth for available features
2. `FeaturesSeeder` — inserts all enum cases into `features`
3. `CentralTenantSeeder` — assigns features to a tenant via `sync()`
4. `HandleInertiaRequests` — shares `auth.features[]` to the frontend
5. `CheckFeature` middleware — guards routes (`->middleware('feature:inventory')`)
6. `useFeatures` composable — `hasFeature('inventory')` in Vue
7. `useMenu` — filters nav items by feature + permission + optional visible state

---

## Permissions System

Uses `spatie/laravel-permission`, seeded per module.

**Naming convention:** `{module}-{entity}-{action}` — e.g. `inventory-records-read`, `inventory-records-delete`.

**Role groups in `PermissionsBaseSeeder`:**

| Property  | Roles              |
| --------- | ------------------ |
| `$all`    | owner, admin, user |
| `$admins` | owner, admin       |
| `$owners` | owner              |

`setPermissions($roles, $permission)` idempotently grants/revokes a permission for the given role group, so re-seeding is safe.

**Adding a new module's permissions:**

1. Create `XyzPermissionsSeeder extends PermissionsBaseSeeder` (use `Permission::findOrCreate(...)` + `setPermissions(...)`)
2. Register it in `AllPermissionsSeeder::run()`
3. Done — `TenantDatabaseSeeder` needs no changes

**Inventory permissions (reference):**
`inventory-records-{read|create|update|delete}`, `inventory-movements-{read|create|delete}`.
Read → `$all`, create/update → `$admins`, delete → `$owners`.

**Users permissions (reference):**
`users-{read|create|update|delete}`. Access is restricted to admin + owner:
read/create/update → `$admins`, delete → `$owners`.

**Analytics permissions (reference):**
`analytics-read` → `$admins` (owner + admin only). Seeded by
`AnalyticsPermissionsSeeder`; gates all `tenant.analytics.*` routes and the
"Analityka" menu group. See **Analytics Module**.

**Settings permissions (reference):**
`setting-profile` → `$all` (every role can manage their own profile/password).
Seeded by `SettingsPermissionsSeeder`; gates `tenant.settings.profile[.update]`.

### Frontend enforcement (defense in depth)

Route middleware (`permission:` + `feature:`) is the real gate, but the UI also
hides every CRUD action button the user can't use, mirroring the menu's
`hasFeature(...) && hasPermission(...)` check. Each Index/Show page computes
`canCreate` / `canUpdate` / `canDelete` flags from `usePermissions()` +
`useFeatures()` and:

- `v-if`s the toolbar buttons (`Dodaj`, `Importuj`, …) and the Show-page actions (`Edytuj`, `Usuń`);
- passes `:can-edit` / `:can-delete` to `DataTable`, which hides the per-row edit/delete buttons and the bulk-delete action.

This is documented step-by-step in **`CRUD_GENERATOR_AGENT_INSTRUCTIONS.md` §2.7**.

---

## Server-Side DataTable Stack

A reusable pipeline powers every list page (search + filters + sort + pagination), split across three backend pieces and one frontend component.

### 1. `DataTableConfig` (abstract) — `app/Http/Resources/`

A module declares its query rules by extending `DataTableConfig`:

| Method                                                      | Returns                                                                           |
| ----------------------------------------------------------- | --------------------------------------------------------------------------------- |
| `baseQuery()`                                               | `Builder` (with eager loads)                                                      |
| `searchableColumns()`                                       | `list<string>` columns for `LIKE` search                                          |
| `allowedSortColumns()`                                      | `list<string>` whitelist for sorting                                              |
| `filterableColumns()`                                       | keyed config `['key' => ['column','type',...]]` (see FILTERABLE_COLUMNS_GUIDE.md) |
| `defaultSort()` / `defaultDirection()` / `defaultPerPage()` | defaults                                                                          |
| `transformer()`                                             | the `Transformer` used to shape each row                                          |

`toArray()` runs each row through the transformer.

### 2. `BaseService::fetchForDataTable($configClass, $request)` — `app/Services/`

Reads `search`, `sortBy`, `direction`, `perPage`, and filter params from the request, applies them to the config's `baseQuery()`, paginates, maps each row through the config's transformer, and returns the paginator array merged with an `active filters` key. Filter strategies: `select` (exact), `text` (LIKE), `number` (exact), `date-range` and `number-range` (inclusive `{key}_from`/`{key}_to`), `boolean` (true/false), `null-status` (nullable column presence), and `relation` (`whereHas` on a related model's column).

### 3. `Transformer` (abstract) — `app/Transformers/`

`transform($model)` returns the base row array; `eagerLoads()` lists relations to load in `baseQuery()`; `defaultIncludes` + `includeXxx()` methods append nested relation data. `toArray()` assembles everything.

### 4. `DataTable.vue` + `useTable.ts` (frontend)

`DataTable.vue` renders the table, built-in search, per-column filters, row selection, bulk actions, and a synced top mirror scrollbar. `useTable.ts` holds table state and pushes Inertia visits (debounced search, filters, sort, pagination). A module wraps `useTable` in a `useXxxTable` composable that also builds the column definitions and display helpers.

`DataTable` also accepts `canEdit` / `canDelete` props (both default `true`); when `false` they hide the per-row edit/delete buttons and the bulk-delete action. Pages pass their permission flags here (see **Permissions System → Frontend enforcement**).

> Full how-to for adding filters: **`FILTERABLE_COLUMNS_GUIDE.md`**.

---

## Step-by-Step Wizard

A reusable multi-step form stack: the **`useStepByStep`** composable owns the state, and the **`StepByStep.vue`** component is the presentational stepper. First used by the public **Tenant Invitation** form (see **Tenant Invitations**).

### `useStepByStep<T>` — `composables/useStepByStep.ts`

```ts
const stepper = useStepByStep({
    steps,                                   // StepDefinition[]
    data: form,                              // reactive form (e.g. Inertia useForm)
    storageKey: `sb-invitation-${uuid}`,     // optional — persists to localStorage
    excludeFromStorage: ["password", ...],   // fields never written to storage
    onValidateStep: async (step, data) => true, // async per-step guard (false = stay)
});
```

| Returns                            | Meaning                                                          |
| ---------------------------------- | ---------------------------------------------------------------- |
| `currentIndex` / `currentStep`     | active step (ref / computed)                                     |
| `completed`                        | `number[]` of completed step indices                             |
| `validating`                       | `true` while `onValidateStep` is in flight                       |
| `isFirst` / `isLast` / `progress`  | navigation helpers                                               |
| `next()`                           | awaits `onValidateStep`, marks complete, advances (returns bool) |
| `prev()` / `goTo(i)` / `canAccess(i)` | navigation (back/completed steps are free; forward runs validation) |
| `clearStorage()`                   | wipe the localStorage entry (call on successful submit)          |

`StepDefinition` = `{ key, label, icon?, description?, fields?: string[] }`. The `fields` drive which values get persisted (minus `excludeFromStorage`). State is **hydrated from localStorage on init** (field values + position + completed) and re-persisted on change.

### `StepByStep.vue` — `Components/StepByStep.vue`

Presentational only — the parent owns the composable and passes state down + handles events:

- **Props:** `steps`, `currentIndex`, `completed`, `validating`, `isFirst`, `isLast`, `nextLabel`, `finalLabel`, `finalProcessing`.
- **Emits:** `next`, `prev`, `go(index)`, `finish`.
- **Slots:** one per step, named by `step.key` (e.g. `#domain`, `#company`), receiving `{ step }`.

Renders a stepper header (gradient fuchsia→indigo circles — completed = check, current = glow ring, pending = muted, clickable when accessible), the active step's body slot, and a Wstecz / Dalej / final-action footer with spinners.

### Backend per-step validation pattern

A FormRequest exposes a static **`stepRules()`** keyed by step; `rules()` merges all groups (final submit) while a controller endpoint validates a single group for the async per-step check. Errors come back as JSON `422` and are mapped onto `form.setError(...)`. Reference: `FillInvitationRequest::stepRules()` + `PublicInvitationController::validateStep()`.

---

## Media Library

`spatie/laravel-medialibrary` with a tenant-aware URL generator (`App\MediaLibrary\TenantUrlGenerator`). The `ManagesFiles` trait (used by services) wraps `uploadFile()` / `uploadFiles()` / `destroyFile()` / `destroyFiles()` with `beforeUpload`/`afterUpload`/`beforeDestroy` hooks (reserved for quota tracking + image optimization).

Models implement `HasMedia` + `InteractsWithMedia` and declare collections in `registerMediaCollections()` (e.g. `InventoryRecord` has a single-file `cover` collection limited to jpeg/png/webp). The transformer exposes the URL via `getFirstMediaUrl('cover') ?: null`.

---

## Encrypted Attributes (CipherSweet)

Sensitive PII is **encrypted at rest** with `spatie/laravel-ciphersweet` (wraps `paragonie/ciphersweet`). The encryption key lives in `.env` as `CIPHERSWEET_KEY` (generate with `php artisan ciphersweet:generate-key`; placeholder in `.env.example`). Config: `config/ciphersweet.php` (defaults: `nacl` backend, `string` key provider).

**How a model opts in:**

1. `implements CipherSweetEncrypted` + `use UsesCipherSweet`.
2. Declare fields + blind indexes in `configureCipherSweet(EncryptedRow $row)`:
   - `addOptionalTextField('col')` — encrypts the column; **`Optional`** variant tolerates `null` (use it for nullable columns).
   - `addBlindIndex('col', new BlindIndex('col'))` — registers a searchable index for that column.
3. The encrypted columns must be **`text`** in the migration (ciphertext is long), and they **must not** have a `'string'`/`'encrypted'` cast — CipherSweet handles encrypt/decrypt via model events (`saving`/`retrieved`).

**Blind indexes (searching):** stored in a separate polymorphic **`blind_indexes`** table (migration in `database/migrations/tenant/`, since the only encrypted model — `User` — is tenant-scoped). They enable **exact-value** lookups only:

- `Model::whereBlind('col', 'indexName', $value)` / `->orWhereBlind(...)` scopes.
- Uniqueness: `Spatie\LaravelCipherSweet\Rules\EncryptedUniqueRule` (or a closure using `whereBlind`).

> **Limitation (by design):** you **cannot** do partial `LIKE '%frag%'`, sorting, or range filters on encrypted columns — only exact full-value matches via blind indexes. Keep encrypted columns out of `searchableColumns()`/`allowedSortColumns()`/`filterableColumns()` and search them separately (see `UserService::applySearch()`).

**Re-encrypting existing rows:** `php artisan ciphersweet:encrypt "App\Models\Tenant\User"` (run per tenant). After a fresh `make reset` seeders write already-encrypted data, so this is only for retrofitting existing data.

Currently used by: **`App\Models\Tenant\User`** — `phone, street, building_number, apartment_number, postal_code, city, pesel` (blind indexes on `phone, postal_code, city, pesel`).

---

## Users Module

Tenant team management (the "Użytkownicy" feature). A full CRUD over the tenant
`App\Models\Tenant\User` model, built on the same stack as Inventory. **Access is
restricted to admin + owner.**

**Model fields** (auth columns + profile, added in
`migrations/tenant/..._add_profile_fields_to_users_table.php`):
`first_name`, `last_name`, `email`, `phone`, `street`, `building_number`,
`apartment_number`, `postal_code`, `city`, `pesel`, `is_active` (bool, default
`true`), `email_verified_at`, plus the Spatie `roles` relation. `password` is
`hashed` cast.

**Encrypted PII:** `phone, street, building_number, apartment_number,
postal_code, city, pesel` are encrypted at rest via CipherSweet (see **Encrypted
Attributes** above). Blind indexes exist for `phone, postal_code, city, pesel`,
so those (and `pesel` uniqueness in the FormRequests) are searched by **exact
value** through `whereBlind`. PESEL is validated as `digits:11` + unique.

**Backend pieces:**

| Concern         | File                                                          |
| --------------- | ------------------------------------------------------------- |
| Controller      | `app/Http/Controllers/Tenant/Users/UserController.php`        |
| Service         | `app/Services/Tenant/Users/UserService.php`                   |
| DataTableConfig | `app/Http/Resources/Tenant/Users/UserDataTable.php`           |
| Transformer     | `app/Transformers/Tenant/Users/UserTransformer.php`           |
| FormRequests    | `app/Http/Requests/Tenant/Users/{Store,Update,BulkDestroyUsers}Request.php` |
| Permissions     | `database/seeders/Tenant/UsersPermissionsSeeder.php`          |
| Data seeder     | `database/seeders/Tenant/UsersSeeder.php`                     |

**Permissions:** `users-{read|create|update|delete}`. read/create/update →
`$admins`, delete → `$owners` (so the whole module is admin + owner only). The
`useMenu` "Lista użytkowników" entry is gated by `users-read`.

**Routes** (`tenant.users.*`, under `feature:users`): `index`, `store`,
`bulk-destroy` (collection), then `show`, `update`, `destroy` (`{user}`
model-bound). Each write action carries its `permission:` middleware.

**Table & filters** (see `FILTERABLE_COLUMNS_GUIDE.md`): global search does `LIKE`
over `first_name`/`last_name`/`email` and **exact** blind-index matches over the
encrypted `phone`/`postal_code`/`city`/`pesel` (`UserService::applySearch()`
overrides `BaseService` to add the `orWhereBlind` clauses); filters for `role` (`relation` →
Spatie `roles.name`), `email_verified_at` (`null-status`, ✓/✗ toggle),
`is_active` (`boolean`, ✓/✗ toggle) and `created_at` (date-range). The list shows
only `phone` + `is_active` beyond the core columns; the rest live in the
create/edit modal and the Show page.

**Behaviour rules:**

- **Password is set only on create** — `UpdateUserRequest` has no `password` rule and `UserService::update()` always strips it, so editing a user can never change their password. Users change their own password on the Profile page; password reset is a separate guest flow (see **Auth: E-mail Verification, Password Reset & Change**).
- **Self-delete is blocked** — `destroy()` refuses the current user; `bulkDelete()` excludes the current user's id.
- **New users start unverified** — `UserService::create()` fires `event(new Registered($user))`, which sends the e-mail-verification link. Admins can re-send it from the Show page (`resend-verification`, shown only while `email_verified_at` is null).
- **Activate / Deactivate** — `POST /users/{user}/toggle-active` (`permission:users-update`) flips `is_active` via `UserService::toggleActive()`. Self-deactivation is blocked by the controller (`$user->id === auth()->id()` guard). Inactive users are blocked by the `user-active` middleware (`CheckIfUserIsActive`) and redirected to `tenant.user.inactive` (a notice page rendered by `UserInactiveController`). The `tenant.user.inactive` route lives **outside** the `user-active` middleware group to prevent a redirect loop. In the UI: an icon-only `<Tooltip>` button in `Users/Index.vue` row-actions slot; a Dezaktywuj/Aktywuj button in `Users/Show.vue` actions — both hidden when viewing your own account.

**Frontend:** `resources/js/Pages/Tenant/Users/` (`Index.vue`, `Show.vue`,
`UserModal.vue`, `users.resource.ts`) + `composables/Tenant/useUserTable.ts`.
CRUD buttons are feature/permission-gated per the §2.7 pattern in
`CRUD_GENERATOR_AGENT_INSTRUCTIONS.md`.

---

## Inventory Movements (stock ledger)

An append-only stock ledger for the Inventory module ("Ruchy magazynowe"). Every
change to an `InventoryRecord`'s `quantity` is mirrored as an `InventoryMovement`
row, so the warehouse history is auditable and each record's running stock is
reproducible.

**Model fields** (`inventory_movements`, tenant DB; SoftDeletes):
`inventory_record_id` (cascade-delete FK), `type` (enum), `quantity` (the
movement size, always positive), `quantity_before`, `quantity_after` (stock
snapshot around the movement), `sale_price` (nullable `decimal:2`, the unit price
**set only on `Sale` movements** — see **Inventory Sales**), `note` (nullable),
`user_id` (nullable FK, null on user delete). `type` casts to
`InventoryMovementTypeEnum`.

**The service is the single source of truth** — `InventoryRecordMovementsService`
(extends `BaseService`) owns both the stock mutation and the ledger write inside
one `DB::transaction`, so stock and history never drift:

| Method                                              | When                                                        | Effect on stock                                                                 |
| --------------------------------------------------- | ----------------------------------------------------------- | ------------------------------------------------------------------------------- |
| `record($record, $type, $qty, $note, $userId)`      | Manual movement from the form (`In/Out/Return/Loss`)        | Applies `type->sign() * qty`; **throws `ValidationException`** if it would go below 0 |
| `recordSale($record, $qty, $salePrice, $note, $userId)` | A sale from the Sales panel                             | Decreases stock by `qty`; logs a `Sale` entry storing `sale_price`; **throws** if it would go below 0 |
| `recordInitial($record, $userId)`                   | A new record is created with `quantity > 0`                 | None (record already carries the qty) — logs an `Initial` entry                 |
| `recordQuantityChange($record, $old, $new, $userId)`| A record's `quantity` is edited                             | None (record already updated) — logs a `Correction` entry (no-op if unchanged)  |
| `delete($movement)` / `bulkDelete($ids)`            | A movement is removed                                       | **Reverses** the entry's delta (`quantity_after - quantity_before`), clamped at 0 |

`InventoryRecordService` depends on it: `create()` calls `recordInitial()`,
`update()` calls `recordQuantityChange()` when `quantity` is in the payload, and
`show()` eager-loads `movements` (latest first, with `user.roles`) for the
detail page's history card.

**Backend pieces:**

| Concern         | File                                                                          |
| --------------- | ----------------------------------------------------------------------------- |
| Controller      | `app/Http/Controllers/Tenant/Inventory/InventoryMovementController.php`        |
| Service         | `app/Services/Tenant/Inventory/InventoryRecordMovementsService.php`            |
| DataTableConfig | `app/Http/Resources/Tenant/Inventory/InventoryMovementDataTable.php`           |
| Transformer     | `app/Transformers/Tenant/Inventory/InventoryMovementTransformer.php`           |
| FormRequests    | `app/Http/Requests/Tenant/Inventory/{StoreInventoryMovement,BulkDestroyInventoryMovements}Request.php` |
| Data seeder     | `database/seeders/Tenant/InventoryMovementsSeeder.php` (one `Initial` per record) |

**Permissions:** reuses the already-seeded `inventory-movements-{read|create|delete}`
(read → `$all`, create → `$admins`, delete → `$owners`). There is **no update** —
the ledger is append-only; corrections happen by editing the record (auto
`Correction` entry) or deleting a movement (auto stock reversal).

**Routes** (`tenant.inventory.movements.*`, under `feature:inventory`): `index`
(GET), `store` (POST), `bulk-destroy` (POST, literal path), `destroy` (DELETE
`{inventoryMovement}`). Each write action carries its `permission:` middleware.

**Validation:** `StoreInventoryMovementRequest` restricts `type` to the four
manual cases (`In/Out/Return/Loss`), requires `inventory_record_id`
(`exists:inventory_records`) and `quantity` (`1..999999`); the below-zero guard
lives in the service and surfaces as a `quantity` validation error.

**Table & filters** (see `FILTERABLE_COLUMNS_GUIDE.md`): search over `note`;
sortable `type`/`quantity`/`quantity_after`/`created_at` (default `created_at desc`,
20/page); filters for `type` (`select`), `inventory_record_id` (`number`) and
`created_at` (`date-range`). The transformer exposes a computed `delta`
(`quantity_after - quantity_before`), `sale_price` + a computed `sale_total`
(`sale_price × quantity`, null for non-sales), plus the related record and user.

**Frontend:** `resources/js/Pages/Tenant/Inventory/Movements/Index.vue` (list +
create modal `InventoryMovementModal.vue`, types in `movements.resource.ts`,
table wrapper `composables/Tenant/useMovementsTable.ts`). The Inventory **Show**
page renders the per-record history via `Show/InventoryHistoryCard.vue` inside a
`BaseTab`. CRUD buttons are feature/permission-gated per the §2.7 pattern in
`CRUD_GENERATOR_AGENT_INSTRUCTIONS.md`.

---

## Inventory Sales (point-of-sale)

A point-of-sale panel for the Inventory module ("Sprzedaż"). Selling a disc
decrements its stock by one (or more) and records a dedicated **`Sale`**
`InventoryMovement`, so every sale lives in the same auditable stock ledger as
the other movements.

**Sale price lives on the movement, not the record.** `InventoryRecord` no longer
has a `sale_price` column — the price is decided **at the point of sale** and
stored on the `Sale` movement's `sale_price`. (`InventoryRecord` keeps
`purchase_price_per_unit` — the per-unit buy price.)

**The service** — `InventorySaleService` constructor-injects
`InventoryRecordMovementsService` and delegates the stock+ledger write to it; it
is **not** a full CRUD/`BaseService`:

| Method                                            | Purpose                                                                 |
| ------------------------------------------------- | ----------------------------------------------------------------------- |
| `sellableRecords()`                               | In-stock records (`quantity > 0`) shaped for the POS grid (id, name, artist, format, condition, quantity, cover) |
| `recentSales($limit = 8)`                         | Latest `Sale` movements (newest first) for the side feed, via `InventoryMovementTransformer` |
| `todayStats()`                                    | `{ transactions, units }` aggregated over today's `Sale` movements      |
| `sell($record, $qty, $salePrice, $note, $userId)` | Delegates to `InventoryRecordMovementsService::recordSale()`            |

**Backend pieces:**

| Concern      | File                                                                   |
| ------------ | ---------------------------------------------------------------------- |
| Controller   | `app/Http/Controllers/Tenant/Inventory/InventorySaleController.php`    |
| Service      | `app/Services/Tenant/Inventory/InventorySaleService.php`               |
| FormRequest  | `app/Http/Requests/Tenant/Inventory/StoreInventorySaleRequest.php`     |

**Permissions:** `inventory-sales-{read|create}` (both → `$all`), seeded by
`InventoryPermissionsSeeder`. The `useMenu` "Sprzedaż" entry is gated by
`inventory-sales-read`.

**Routes** (`tenant.inventory.sales.*`, under `feature:inventory`): `index` (GET,
`inventory-sales-read`) renders the panel; `store` (POST, `inventory-sales-create`)
records a sale. The panel is **not** a DataTable — `index` just returns
`records`, `recentSales` and `todayStats` props.

**Validation:** `StoreInventorySaleRequest` requires `inventory_record_id`
(`exists:inventory_records`), `quantity` (`1..999999`) and `sale_price`
(`required numeric 0..999999.99`); the below-zero stock guard lives in
`recordSale()` and surfaces as a `quantity` validation error.

**Frontend:** `resources/js/Pages/Tenant/Inventory/Sales/Index.vue` — a card grid
of sellable discs with client-side search, a "Dziś" stats card and a recent-sales
feed; selling opens `Sales/SaleDialog.vue` (quantity capped at stock via
`BaseInputNumber`'s `:max`, unit-price input, live total). Types in
`sales.resource.ts`. The "Sprzedaj" buttons are feature/permission-gated
(`inventory-sales-create`) per the §2.7 pattern.

---

## Dashboard

The tenant landing page (`tenant.dashboard`, **not** feature-gated) — headline
stat tiles, an active-modules grid, and a paginated recent-movements table.

**Backend pieces:**

| Concern    | File                                                       |
| ---------- | ---------------------------------------------------------- |
| Controller | `app/Http/Controllers/Tenant/DashboardController.php` (`__invoke`) |
| Service    | `app/Services/Tenant/Dashboard/DashboardService.php` (extends `BaseService`) |

`DashboardService::stats()` returns headline counters (revenue, today's sold
units, records, stock units, stock value, users, low-stock count ≤3).
`recentMovements($request)` **reuses the `InventoryMovementDataTable` config** via
`fetchForDataTable`, forcing a small default page size (`perPage = 5`) — so the
dashboard table is the same server-side search/sort/filter/pagination pipeline as
the Inventory movements page, no extra config class. The controller passes
`stats`, `movements`, and `InventoryMovementTypeEnum::options()` to the page.

**Frontend:** `resources/js/Pages/Tenant/Dashboard.vue`:

- **Stat tiles** — grid of cards (icon + color + value; money via `useMoney`).
- **Active modules** — grid of cards built from `auth.features` (the tenant's
  enabled features, shared by `HandleInertiaRequests`) mapped to label/icon/color
  + the module's landing route; each is an Inertia `<Link>` to that module.
- **Recent movements** — the shared `DataTable` driven by `useTable`
  (`routeName: "tenant.dashboard"`, `defaultPerPage: 5`), reusing
  `buildMovementColumns` from `movements.resource.ts`; read-only (`:can-edit` /
  `:can-delete` = `false`). Table state pushes Inertia visits back to
  `tenant.dashboard`, which `recentMovements()` reads — no separate endpoint.

---

## Analytics Module

Read-only analytics dashboards (the "Analityka" feature) that aggregate the
existing Inventory + sales data — **no own model or table**. Four pages:
**Podsumowanie** (overview), **Sprzedaż** (sales), **Top artyści** (artists),
**Raporty** (reports).

**Data sources:** `InventoryMovement` rows of type `Sale` (revenue =
`SUM(sale_price * quantity)`, units, transactions) and `InventoryRecord` (stock
units/value, titles, format/condition/genre breakdowns).

**Backend pieces:**

| Concern    | File                                                        |
| ---------- | ----------------------------------------------------------- |
| Controller | `app/Http/Controllers/Tenant/Analytics/AnalyticsController.php` (`overview`/`sales`/`artists`/`reports`) |
| Service    | `app/Services/Tenant/Analytics/AnalyticsService.php`        |
| Permission | `database/seeders/Tenant/AnalyticsPermissionsSeeder.php`    |

`AnalyticsService` is **not** a `BaseService` (no CRUD) — it exposes one method
per page returning a ready-to-render array. Aggregations use `selectRaw` +
`groupBy` (and a `join` to `inventory_records` for per-format/artist rollups).
**Gotchas baked in:** `condition` is backticked (MySQL reserved word) and the
grouped rollups use `->toBase()` so `format`/`condition` come back as raw strings
(the `InventoryRecord` enum casts would otherwise break `keyBy()`). Daily (30d)
and monthly (12m) series are gap-filled with zeros; month labels are Polish
(`MONTHS_SHORT_PL` const → `Sty 2026`), not Carbon-locale dependent.

**Permission:** single `analytics-read`, granted to **`$admins`** (owner + admin)
by `AnalyticsPermissionsSeeder` (registered in `AllPermissionsSeeder`). The whole
feature is admin+owner only.

**Routes** (`tenant.analytics.*`, under `['feature:analytics', 'permission:analytics-read']`):
`overview`, `sales`, `artists`, `reports` (all GET). The `analytics` feature is
assigned to the dev tenant in `CentralTenantSeeder` and to invited tenants by
`ProvisionTenantJob`.

**Frontend:** `resources/js/Pages/Tenant/Analytics/` — `Overview.vue`,
`Sales.vue`, `Artists.vue`, `Reports.vue` (each `AppLayout` + `IndexLayout`),
types in `analytics.resource.ts`. Charts are **chart.js used directly** (a
`<canvas>` + `new Chart()` from `chart.js/auto`, rendered in `onMounted` and
re-rendered on prop change, destroyed on unmount — **not** PrimeVue's Chart
wrapper). Reusable chart components under `Analytics/Components/`:

| Component       | Purpose                                                       |
| --------------- | ------------------------------------------------------------- |
| `StatCard.vue`  | KPI card (value, icon, optional ± delta, caption) — pure CSS  |
| `BarChart.vue`  | chart.js vertical bars; `formatValue` drives tooltip + Y-axis |
| `DonutChart.vue`| chart.js doughnut; right legend + custom center-text plugin   |
| `RankBars.vue`  | horizontal ranking bars (artists) — pure CSS                  |

> `chart.js` is registered in `vite.config.js` `optimizeDeps.include` so the dev
> server pre-bundles it. The menu "Analityka" group is gated by
> `feature:analytics` + `permission:analytics-read` in `useMenu.ts`.

---

## Auth: E-mail Verification, Password Reset & Change

Built on Laravel's native mechanisms (the `Password` broker, `MustVerifyEmail`,
the `Registered` event, signed URLs). All routes are tenant-scoped, so links/
e-mails point at the current subdomain; mail uses global SMTP and sends
synchronously.

- **Password reset (guest):** `password.request` / `password.email` /
  `password.reset` / `password.store` → `Password::sendResetLink()` +
  `Password::reset()`. Broker status strings localized in `lang/en/passwords.php`.
- **Password change (auth):** `PUT /settings/password` on the Profile page;
  `UpdatePasswordRequest` uses the `current_password` rule + `Password::defaults()`.
- **E-mail verification:** `User implements MustVerifyEmail`; the whole
  authenticated app is wrapped in a `verified` middleware group. New/admin-created
  users are **unverified** — `UserService::create()` fires `event(new Registered($user))`
  and Laravel's `SendEmailVerificationNotification` listener mails the link
  (the `UserFactory` still seeds verified accounts). The verify route
  (`verification.verify`) is **outside `auth`** — protected by the signed URL +
  `sha1(email)` check — so it works for not-logged-in / admin-created accounts,
  then **logs the user in**. Resend: self (`verification.send`) or admin
  (`tenant.users.resend-verification`, button on Users → Show when unverified).
- **Profile page:** `/settings/profile` is gated by `permission:setting-profile`
  (seeded for all roles in `SettingsPermissionsSeeder`) and lives **outside**
  `feature:settings` (account self-management isn't a paid feature). Thin
  `SettingController` → `SettingService` → `UserService`.

> **Full flows, route table, and the session-independent-verify rationale:**
> **`AUTH_VERIFICATION_AND_PASSWORDS.md`**.

---

## Routes

### Central

- `routes/web.php` — landing page + public invitation form (`GET/POST /invitation/{uuid}`, `['web', 'central']`)
- `routes/admin.php` — superadmin panel under `/panel/central/superadmin` (see **Superadmin Panel**)

### Tenant (`routes/tenant.php`)

All tenant routes are wrapped in:

```php
Route::middleware(['web', 'tenant', 'prevent-central'])
```

Authenticated routes add `auth`. Feature groups add `feature:{name}`; write actions add `permission:{module}-{entity}-{action}`.

| Prefix          | Middleware             | Module      |
| --------------- | ---------------------- | ----------- |
| `/inventory`    | `feature:inventory`    | Magazyn     |
| `/trading`      | `feature:trading`      | Giełdy      |
| `/analytics`    | `feature:analytics`    | Analityka   |
| `/integrations` | `feature:integrations` | Integracje  |
| `/users`        | `feature:users`        | Użytkownicy |
| `/settings`     | `feature:settings`     | Ustawienia (organization, billing) |

**Inventory records routes (reference):** `index`, `store`, `import`, `export-template`, `bulk-destroy` (collection, literal paths) then `show`, `update`, `destroy`, `cover`, `cover.destroy` (`{inventoryRecord}` model-bound). Names: `tenant.inventory.records.*`.

**Inventory movements routes (reference):** under `/inventory/movements`, `index` (GET, `inventory-movements-read`), `store` (POST, `inventory-movements-create`), `bulk-destroy` (POST, `inventory-movements-delete`), `destroy` (DELETE `{inventoryMovement}`, `inventory-movements-delete`). Names: `tenant.inventory.movements.*`. See **Inventory Movements** above.

**Inventory sales routes (reference):** under `/inventory/sales`, `index` (GET, `inventory-sales-read`) and `store` (POST, `inventory-sales-create`). Names: `tenant.inventory.sales.*`. See **Inventory Sales** above.

**Analytics routes (reference):** under `/analytics`, `overview`, `sales`, `artists`, `reports` (all GET), gated by `['feature:analytics', 'permission:analytics-read']`. Names: `tenant.analytics.*`. See **Analytics Module** above.

**Users routes:** `index`, `store`, `bulk-destroy` (collection) then `show`, `update`, `destroy` (`{user}` model-bound), `resend-verification` (POST, `permission:users-update`), and `toggle-active` (POST `{user}/toggle-active`, `permission:users-update`), under `feature:users`. Names: `tenant.users.*`. See **Users Module** above.

**Account routes (not feature-gated):** password reset is a `guest` flow
(`password.request|email|reset|store`); while authenticated, `verification.notice`
/ `verification.send`, `tenant.settings.password.update`, and
`tenant.settings.profile` (gated by `permission:setting-profile`, **not**
`feature:settings`) are reachable. `verification.verify` is `signed`-only and
sits outside the `auth` group. See **`AUTH_VERIFICATION_AND_PASSWORDS.md`**.

---

## Middleware

| Alias             | Class                             | Purpose                                                                       |
| ----------------- | --------------------------------- | ----------------------------------------------------------------------------- |
| `tenant`          | `InitializeTenancyByDomain`       | Bootstraps tenant context                                                     |
| `prevent-central` | `PreventAccessFromCentralDomains` | Blocks central domain from tenant routes                                      |
| `central`         | `EnsureCentralDomain`             | Blocks tenant subdomains from central routes                                  |
| `permission`      | Spatie `PermissionMiddleware`     | Guards by Spatie permission                                                   |
| `feature`         | `CheckFeature`                    | Guards by tenant feature flag                                                 |
| `tenant-active`   | `CheckIfTenantIsActive`           | Redirects to `tenant.inactive` if `Tenant::$is_active === false`              |
| `user-active`     | `CheckIfUserIsActive`             | Redirects to `tenant.user.inactive` if `User::$is_active === false`           |

**Redirect-loop prevention:** `tenant.inactive` and `tenant.user.inactive` routes are in a dedicated middleware group that omits `tenant-active` and `user-active` respectively, so the notice pages are always reachable.

---

## Development Workflow

```bash
make up           # Start containers
make down         # Stop containers
make dev          # npm run dev
make reset        # migrate:fresh --seed + tenants:seed + remove unused tenant DBs
make fresh        # migrate:fresh --seed only (no tenant cleanup)
make clear        # Full Docker cleanup + rebuild
make rebuild      # Down -v + rebuild (no network prune)
make cache-clear  # Artisan optimize/route/config/cache clear
```

### Reset order

```
migrate:fresh --seed   → central DB fresh (features + tenant created)
tenants:seed           → tenant DB seeded (roles, permissions, owner, inventory records)
tenant:remove-unused   → drops orphaned tenant databases
```

### Artisan commands

| Command                | Description                                           |
| ---------------------- | ----------------------------------------------------- |
| `tenant:remove-unused` | Drops tenant databases with no matching tenant record |
| `tenants:seed`         | Runs TenantDatabaseSeeder for all tenants             |

---

## Inertia Shared Data

Every page receives via `HandleInertiaRequests::share()`:

```ts
auth: {
  user: { id, name, email }
  permissions: string[]   // e.g. ['inventory-records-read']
  roles: string[]         // e.g. ['owner']
  features: string[]      // e.g. ['inventory']
} | null
```

---

## Frontend Composables

```ts
// Auth-aware
const { hasFeature } = useFeatures(); // hasFeature('inventory')
const { hasPermission } = usePermissions(); // hasPermission('inventory-records-delete')
const { menu } = useMenu(); // filtered nav (feature + permission aware)

// Tables
const table = useTable({
    routeName,
    defaultSort,
    defaultDirection,
    defaultPerPage,
});
// → search, sortBy, direction, perPage, extraFilters,
//   onSearchInput, setFilter, clearFilters, toggleSort, goToPage, syncFromFilters

// UI helpers
const { success, error } = useToast(); // toast notifications (AppToast renders them)
const { formatPrice } = useMoney(); // "240,00 zł" (PLN, null-safe)
const { formatDate } = useDate(); // "04.06.2026" (pl-PL, null-safe)

// Multi-step wizard (see Step-by-Step Wizard)
const { currentIndex, completed, validating, isFirst, isLast, next, prev, goTo, clearStorage } =
    useStepByStep({ steps, data: form, storageKey, excludeFromStorage, onValidateStep });
```

---

## UI Components

- **Base inputs** (globally registered in `plugins/base-components.ts`): `BaseInput`, `BaseInputNumber`, `BaseTextArea`, `BaseDropdown`, `BaseCheckbox`, `BasePassword`, `BaseMaskedInput`, `BaseDialog`. See **`README-BASE-COMPONENTS.md`**.
- **`StepByStep`** — reusable multi-step wizard (stepper header + per-step slots + nav footer), paired with the `useStepByStep` composable. Imported explicitly (not globally registered). See **Step-by-Step Wizard**.
- **`BaseDialog`** — the modal shell for every dialog; width/columns are managed with Tailwind at the call site (`panel-class`, `align`, `mobile-fullscreen`).
- **`BaseTab`** — tabbed-panel wrapper (used on the Inventory Show page to split details from the stock-movement history).
- **Layouts** — `IndexLayout` (list pages: title + `#toolbar` slot), `ShowLayout` (detail pages: title + `#actions` slot), both built on `PageToolbar`.
- **PrimeVue** — Aura **dark** preset, `darkModeSelector: ".app-dark"` (the `<body>` carries `app-dark`), with a `cssLayer` so Tailwind utilities win over PrimeVue base styles.

---

## Building a New Module

See **`CRUD_GENERATOR_AGENT_INSTRUCTIONS.md`** for the complete, step-by-step recipe (modeled on the Inventory module) covering backend (Controller + Request + Service + BaseService + DataTableConfig + Transformer + Model/migration/factory/seeder + permissions + routes + feature/menu) and frontend (Base components, composables, layouts, DataTable, BaseDialog modals).

---

## Superadmin Panel (Central Admin)

A separate admin panel on the **central domain** (`localhost/panel/central/superadmin`) for managing tenants, features, and invitations. Fully isolated from tenant users — different guard, different model, different routes.

### Guard & Model

| Concern  | Detail                                           |
| -------- | ------------------------------------------------ |
| Guard    | `superadmin` (defined in `config/auth.php`)      |
| Model    | `App\Models\Central\Admin` (central `admins` table) |
| Provider | `admins` (Eloquent, `Admin::class`)              |

Authentication: `POST /panel/central/superadmin/login` → `Central\Auth\AuthController`. Session is the standard web session scoped to the `superadmin` guard. Routes are protected by `auth:superadmin`; guest routes by `guest:superadmin`.

### Routes (`routes/admin.php`)

All routes are prefixed `/panel/central/superadmin` and use `['web', 'central']` middleware.

| Route                                         | Name                              | Action                          |
| --------------------------------------------- | --------------------------------- | ------------------------------- |
| GET `/login`                                  | `central.login`                   | Login page                      |
| POST `/login`                                 | `central.login.store`             | Authenticate                    |
| POST `/logout`                                | `central.logout`                  | Logout                          |
| GET `/`                                        | `central.dashboard`               | Dashboard                       |
| GET `/tenants`                                | `central.tenants.index`           | Tenant list                     |
| POST `/tenants`                               | `central.tenants.store`           | Create tenant                   |
| POST `/tenants/bulk-destroy`                  | `central.tenants.bulk-destroy`    | Bulk delete                     |
| GET `/tenants/{tenant}`                       | `central.tenants.show`            | Tenant detail                   |
| PUT `/tenants/{tenant}`                       | `central.tenants.update`          | Update tenant                   |
| DELETE `/tenants/{tenant}`                    | `central.tenants.destroy`         | Delete tenant                   |
| POST `/tenants/{tenant}/toggle-active`        | `central.tenants.toggle-active`   | Activate / deactivate           |
| GET `/invitations`                            | `central.invitations.index`       | Invitation list                 |
| POST `/invitations`                           | `central.invitations.store`       | Send invitation e-mail          |
| GET `/invitations/{invitation}`               | `central.invitations.show`        | Invitation detail               |
| POST `/invitations/{invitation}/resend`       | `central.invitations.resend`      | Resend e-mail (PENDING only)    |
| POST `/invitations/{invitation}/accept`       | `central.invitations.accept`      | Dispatch `ProvisionTenantJob`   |
| DELETE `/invitations/{invitation}`            | `central.invitations.destroy`     | Delete invitation               |
| GET `/features`                               | `central.features.index`          | Feature flags                   |
| POST `/features/toggle`                       | `central.features.toggle`         | Toggle feature for a tenant     |

### Backend pieces

| Concern               | File                                                                    |
| --------------------- | ----------------------------------------------------------------------- |
| Tenant controller     | `app/Http/Controllers/Central/TenantController.php`                     |
| Tenant service        | `app/Services/Central/TenantService.php`                                |
| Tenant transformer    | `app/Transformers/Central/TenantTransformer.php`                        |
| Invitation controller | `app/Http/Controllers/Central/InvitationController.php`                 |
| Invitation service    | `app/Services/Central/InvitationService.php`                            |
| Invitation transformer| `app/Transformers/Central/TenantInvitationTransformer.php`              |
| Feature controller    | `app/Http/Controllers/Central/FeatureController.php`                    |
| Feature service       | `app/Services/Central/FeatureService.php`                               |

### Frontend

`resources/js/Pages/Central/` — same Inertia/Vue 3 stack as the tenant side. Navigation is in `resources/js/layout/Central/AppLayout.vue`. Pages:

| Page                              | Route                          |
| --------------------------------- | ------------------------------ |
| `Central/Auth/Login.vue`          | `central.login`                |
| `Central/Dashboard.vue`           | `central.dashboard`            |
| `Central/Tenants/Index.vue`       | `central.tenants.index`        |
| `Central/Tenants/Show.vue`        | `central.tenants.show`         |
| `Central/Invitations/Index.vue`   | `central.invitations.index`    |
| `Central/Invitations/Show.vue`    | `central.invitations.show`     |
| `Central/Features/Index.vue`      | `central.features.index`       |

**Tenant activate / deactivate:** same pattern as user toggle — `POST /{tenant}/toggle-active` flips `is_active` on the `Tenant` model. Inactive tenants are blocked by `CheckIfTenantIsActive` (`tenant-active` middleware) and shown `TenantInactivePage.vue`.

---

## Tenant Invitations

A superadmin-driven onboarding flow: the superadmin sends an e-mail invitation, the recipient fills a public web form, and upon confirmation a fully provisioned tenant is created in the background.

### Lifecycle

```
PENDING  →  (recipient fills form)  →  FILLED
FILLED   →  (superadmin confirms)   →  ACCEPTED  (ProvisionTenantJob runs)
PENDING  →  (expires_at in past)    →  EXPIRED   (lazily marked on next visit)
```

### Database

`tenant_invitations` (central DB):

| Column         | Type              | Notes                                  |
| -------------- | ----------------- | -------------------------------------- |
| `id`           | bigint PK         |                                        |
| `uuid`         | char(36) unique   | Used in the public URL                 |
| `email`        | string            | Recipient                              |
| `status`       | string            | `TenantInvitationStatusEnum`           |
| `company_data` | JSON nullable     | Stored after the form is submitted     |
| `owner_data`   | JSON nullable     | Stored after the form is submitted     |
| `tenant_id`    | FK → `tenants.id` | Populated after `ProvisionTenantJob`   |
| `expires_at`   | datetime          | `now() + 1 week` on creation           |

### `TenantInvitation` model

`App\Models\Central\TenantInvitation` — central model (no tenancy scope). Helpers: `isPending()`, `isFilled()`, `isAccepted()`, `isExpired()` (expired = `expires_at` in the past **and** still PENDING). Casts: `status → TenantInvitationStatusEnum`, `company_data / owner_data → array`, timestamps → `datetime`. Has `belongsTo(Tenant::class)`.

### `InvitationService`

| Method                              | What it does                                                                   |
| ----------------------------------- | ------------------------------------------------------------------------------ |
| `index(Request)`                    | `fetchForDataTable(TenantInvitationDataTable)` for the superadmin list         |
| `showData(TenantInvitation)`        | Transformer → array for Show page props                                        |
| `create(string $email)`             | Creates record (uuid + expires_at), sends `TenantInvitationMail`               |
| `findByUuid(string)`                | Lookup by uuid (public controller)                                             |
| `fill($inv, $companyData, $owner)`  | Updates `company_data`, `owner_data`, sets status → FILLED                     |
| `resend(TenantInvitation)`          | Re-sends the same e-mail with the same URL (no status change)                  |
| `accept(TenantInvitation)`          | Dispatches `ProvisionTenantJob`                                                |

### Mail

`App\Mail\TenantInvitationMail` — styled dark HTML e-mail (`resources/views/emails/tenant-invitation.blade.php`) with purple branding, invitation link button, and expiry notice.

### `ProvisionTenantJob` (ShouldQueue)

`App\Jobs\ProvisionTenantJob` — receives a `TenantInvitation` and runs the full provisioning:

1. Takes the user-chosen `company_data['domain']` as the slug (fallback to `company_name`); `uniqueSlug()` checks the `domains` table and appends `-2`, `-3`, … on collision as a race-condition safety net.
2. `Tenant::create(['id' => Str::uuid(), ...])` — the tenant **id is a random UUID** (decoupled from the name); the `TenantCreated` event (wired in `TenancyServiceProvider`) synchronously runs the pipeline: `CreateDatabase → MigrateDatabase → CreateTenantStorageLink`.
3. `$tenant->domains()->create(['domain' => "$slug.$baseDomain"])`.
4. `$tenant->features()->sync([Inventory, Users, Settings])` (default feature set).
5. `tenancy()->initialize($tenant)` — enters tenant context.
6. Runs `RolesSeeder` + `AllPermissionsSeeder` (new DB has no roles/permissions yet).
7. `User::create(...)` with owner data, `syncRoles([RoleEnum::Owner])`, fires `Registered` event (triggers e-mail verification).
8. `tenancy()->end()` in a `finally` block.
9. Updates `invitation.status → ACCEPTED`, `invitation.tenant_id → $tenant->id`.

> **Why seed roles inside the job?** `MigrateDatabase` runs migrations only — not seeders. Without the roles seeder, `syncRoles(['owner'])` would throw "There is no role named 'owner' for guard 'web'".

### Public form routes (`routes/web.php`)

Under `['web', 'central']` (central domain only, no auth):

| Route                                | Name                       | Controller                   |
| ------------------------------------ | -------------------------- | ---------------------------- |
| GET `/invitation/{uuid}`             | `invitation.show`          | `PublicInvitationController` |
| POST `/invitation/{uuid}/validate-step` | `invitation.validate-step` | `PublicInvitationController` |
| POST `/invitation/{uuid}`            | `invitation.submit`        | `PublicInvitationController` |

`show()` validates the invitation state (expired / already filled / not found → `Public/InvitationExpired`) and passes `baseDomain` (`config('app.base_domain')`) to the page for the live domain preview. `validateStep()` validates **only the requested wizard step's rules** and returns JSON (`422` with errors, or `{valid: true}`) — it's hit per-step via `window.axios`. `submit()` splits validated data into `companyData` (incl. `domain`) and `ownerData`, calls `InvitationService::fill()`, then renders `Public/InvitationSuccess`.

### `FillInvitationRequest`

Validation rules are **grouped per wizard step** in a static `stepRules()` (`domain`, `company`, `address`, `owner`, `security`); the full `rules()` merges all groups, so one definition powers both the per-step async endpoint and the final submit.

- **`domain`** (the user-chosen subdomain label): `required`, `regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/`, `max:40`, plus a closure (`uniqueDomainRule()`) that checks `{slug}.{baseDomain}` is free in the `domains` table. **This — not `company_name` — drives the tenant domain.**
- **`company`**: `company_name` required (legal name, no longer unique-checked) + optional `tax_id`/`regon`/`krs_number`/`company_email`/`company_phone`/`website`.
- **`owner`**: `first_name`/`last_name` required, `phone` optional, `pesel` nullable via `new PeselRule(checkUniqueness: false)`.
- **`security`**: `password` required + `confirmed`, min 8.

The `checkUniqueness: false` flag skips the blind-index PESEL uniqueness check because the public form runs in the central context where the tenant DB doesn't exist yet.

### `PeselRule` — `checkUniqueness` flag

`app/Rules/Tenant/PeselRule.php` gained `private readonly bool $checkUniqueness = true` constructor parameter. The DB uniqueness check (`whereBlind`) is skipped when `false`. Used in `FillInvitationRequest` for the public invitation form.

### Frontend pages

| Page                          | Route             | Description                                              |
| ----------------------------- | ----------------- | -------------------------------------------------------- |
| `Public/Invitation.vue`       | `invitation.show` | 5-step wizard (`StepByStep` + `useStepByStep`) — see below; phone/PESEL use `BaseMaskedInput` |
| `Public/InvitationSuccess.vue`| (after submit)    | Green check, shows email, "Aktywacja może zająć do 24 godzin" |
| `Public/InvitationExpired.vue`| (guard fail)      | Red clock, dynamic message from `reason` prop (expired/FILLED/ACCEPTED/not_found) |

The invitation form is a multi-step wizard built on the reusable **`StepByStep` + `useStepByStep`** stack (see **Step-by-Step Wizard** below). Steps: **Adres** (domain label + live `{domain}.{baseDomain}` preview, sanitized client-side to a valid slug) → **Dane firmy** → **Adres siedziby** → **Właściciel** → **Hasło**. Each "Dalej" validates that step on the backend; on final-submit error the wizard jumps back to the step holding the first error.

---

# Roadmap / Upcoming Tasks

- _(done)_ Inventory: sales module (`tenant.inventory.sales.*`) — see **Inventory Sales**.
- _(done)_ Analytics module (`tenant.analytics.*`, chart.js dashboards) — see **Analytics Module**.
- _(done)_ Dashboard (`tenant.dashboard` — stat tiles, active modules, recent-movements table) — see **Dashboard**.
