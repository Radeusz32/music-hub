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
│       └── DiscConditionEnum.php        # Inventory: M, NM, VG+, ... (+ label()/color()/options())
├── Http/
│   ├── Controllers/Tenant/
│   │   ├── Auth/AuthController.php
│   │   └── Inventory/InventoryRecordController.php
│   ├── Middleware/
│   │   ├── CheckFeature.php             # Blocks routes by tenant feature
│   │   └── HandleInertiaRequests.php
│   ├── Requests/Tenant/Inventory/       # One FormRequest per write action
│   │   ├── StoreInventoryRecordRequest.php
│   │   ├── UpdateInventoryRecordRequest.php
│   │   ├── BulkDestroyInventoryRecordsRequest.php
│   │   ├── UploadCoverRequest.php
│   │   └── ImportInventoryRecordsRequest.php
│   └── Resources/
│       ├── DataTableConfig.php          # Abstract base for server-side table queries
│       └── Tenant/Inventory/InventoryRecordDataTable.php
├── Models/
│   ├── Central/{Domain,Feature,Tenant}.php
│   └── Tenant/
│       ├── User.php                     # HasRoles (Spatie), HasFactory
│       └── InventoryRecord.php          # SoftDeletes, HasMedia, casts(), relations
├── Providers/{AppServiceProvider,TenancyServiceProvider}.php
├── Services/
│   ├── BaseService.php                  # fetchForDataTable() + filter strategies
│   ├── Tenant/Auth/LoginService.php
│   └── Tenant/Inventory/InventoryRecordService.php
├── Transformers/
│   ├── Transformer.php                  # Abstract: transform() + eagerLoads() + includes
│   ├── Tenant/UserTransformer.php
│   └── Tenant/Inventory/InventoryRecordTransformer.php
├── Traits/ManagesFiles.php              # Media upload/destroy + hooks
├── Imports/Tenant/Inventory/InventoryRecordImport.php   # maatwebsite/excel
├── Exports/Tenant/Inventory/InventoryRecordExport.php   # template export
├── Jobs/CreateTenantStorageLink.php
└── MediaLibrary/TenantUrlGenerator.php  # Tenant-aware media URLs

database/
├── migrations/                          # Central migrations
├── migrations/tenant/                   # Per-tenant (users, permissions, inventory_records, media)
├── factories/Tenant/{UserFactory,InventoryRecordFactory}.php
└── seeders/
    ├── DatabaseSeeder.php               # FeaturesSeeder → CentralTenantSeeder
    ├── FeaturesSeeder.php
    ├── CentralTenantSeeder.php
    ├── TenantDatabaseSeeder.php         # Roles → AllPermissions → Owner → InventoryRecords
    └── Tenant/
        ├── RolesSeeder.php
        ├── AllPermissionsSeeder.php             # Calls every *PermissionsSeeder
        ├── PermissionsBaseSeeder.php            # Abstract: role groups + setPermissions()
        ├── InventoryPermissionsSeeder.php       # extends PermissionsBaseSeeder
        ├── OwnerSeeder.php
        └── InventoryRecordsSeeder.php

resources/js/
├── Components/                          # Globally-registered UI primitives
│   ├── BaseInput.vue  BaseInputNumber.vue  BaseTextArea.vue  BaseDropdown.vue
│   ├── BaseCheckbox.vue  BasePassword.vue  BaseMaskedInput.vue
│   ├── BaseDialog.vue                   # Modal shell (size + columns via Tailwind)
│   ├── FileUpload.vue  DatePicker.vue  Tooltip.vue  AppToast.vue
├── composables/
│   ├── useToast.ts  useMoney.ts  useDate.ts
│   └── Tenant/
│       ├── useFeatures.ts  usePermissions.ts  useMenu.ts
│       ├── useTable.ts                  # Generic server-side table state
│       └── useInventoryTable.ts         # Module wrapper (columns + display helpers)
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
│       ├── Index.vue   Show.vue   inventory.resource.ts
│       ├── InventoryRecordModal.vue   InventoryImportModal.vue
│       └── Show/*                       # HeroCard, Cover, DetailsCard, MetaCard, Lightbox, VinylDisc
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

### `RoleEnum` — tenant user roles

| Case    | Value   |
| ------- | ------- |
| `Owner` | `owner` |
| `Admin` | `admin` |
| `User`  | `user`  |

### Inventory enums (backed string enums with `label()`, `color()`, `options()`)

- `DiscFormatEnum` — `LP, EP, Single, Double LP, CD, CD Single, DVD, Blu-Ray, Cassette, VHS, Digital, Box Set`
- `DiscConditionEnum` — `M, NM, VG+, VG, G+, G, F, P`

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

**Frontend:** `resources/js/Pages/Tenant/Users/` (`Index.vue`, `Show.vue`,
`UserModal.vue`, `users.resource.ts`) + `composables/Tenant/useUserTable.ts`.
CRUD buttons are feature/permission-gated per the §2.7 pattern in
`CRUD_GENERATOR_AGENT_INSTRUCTIONS.md`.

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

- `routes/web.php` — landing page

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

**Users routes:** `index`, `store`, `bulk-destroy` (collection) then `show`, `update`, `destroy` (`{user}` model-bound) and `resend-verification` (POST `{user}`, `permission:users-update`), under `feature:users`. Names: `tenant.users.*`. See **Users Module** above.

**Account routes (not feature-gated):** password reset is a `guest` flow
(`password.request|email|reset|store`); while authenticated, `verification.notice`
/ `verification.send`, `tenant.settings.password.update`, and
`tenant.settings.profile` (gated by `permission:setting-profile`, **not**
`feature:settings`) are reachable. `verification.verify` is `signed`-only and
sits outside the `auth` group. See **`AUTH_VERIFICATION_AND_PASSWORDS.md`**.

---

## Middleware

| Alias             | Class                             | Purpose                                  |
| ----------------- | --------------------------------- | ---------------------------------------- |
| `tenant`          | `InitializeTenancyByDomain`       | Bootstraps tenant context                |
| `prevent-central` | `PreventAccessFromCentralDomains` | Blocks central domain from tenant routes |
| `permission`      | Spatie `PermissionMiddleware`     | Guards by Spatie permission              |
| `feature`         | `CheckFeature`                    | Guards by tenant feature flag            |

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
```

---

## UI Components

- **Base inputs** (globally registered in `plugins/base-components.ts`): `BaseInput`, `BaseInputNumber`, `BaseTextArea`, `BaseDropdown`, `BaseCheckbox`, `BasePassword`, `BaseMaskedInput`, `BaseDialog`. See **`README-BASE-COMPONENTS.md`**.
- **`BaseDialog`** — the modal shell for every dialog; width/columns are managed with Tailwind at the call site (`panel-class`, `align`, `mobile-fullscreen`).
- **Layouts** — `IndexLayout` (list pages: title + `#toolbar` slot), `ShowLayout` (detail pages: title + `#actions` slot), both built on `PageToolbar`.
- **PrimeVue** — Aura **dark** preset, `darkModeSelector: ".app-dark"` (the `<body>` carries `app-dark`), with a `cssLayer` so Tailwind utilities win over PrimeVue base styles.

---

## Building a New Module

See **`CRUD_GENERATOR_AGENT_INSTRUCTIONS.md`** for the complete, step-by-step recipe (modeled on the Inventory module) covering backend (Controller + Request + Service + BaseService + DataTableConfig + Transformer + Model/migration/factory/seeder + permissions + routes + feature/menu) and frontend (Base components, composables, layouts, DataTable, BaseDialog modals).

---

# Roadmap / Upcoming Tasks

- Inventory: stock movements (`inventory-movements-*` permissions already seeded; UI pending)
