# SoundBased — Project Documentation

## Overview

Multi-tenant SaaS platform for music stores. Each tenant (store) runs on its own subdomain with an isolated database. The central database manages tenants, domains, and feature flags.

**App name:** SoundBased  
**Base domain:** `localhost` (dev) — tenants at `{slug}.localhost`  
**Default tenant (dev):** `music1.localhost`

---

## Tech Stack

### Backend
| Package | Version |
|---|---|
| PHP | 8.4 |
| Laravel | ^12 |
| stancl/tenancy | ^3.9 |
| spatie/laravel-permission | ^7.0 |
| laravel/pint | ^1 |
| pestphp/pest | ^4 |

### Frontend
| Package | Version |
|---|---|
| Vue | ^3.5 |
| Inertia.js | ^2.3 |
| PrimeVue | ^4.5 |
| Tailwind CSS | ^4 |
| TypeScript | — |
| Vite | — |

---

## Architecture

### Multi-tenancy

Uses `stancl/tenancy` with domain-based tenant identification. Each tenant has:
- Own MySQL database (`tenant{uuid}`)
- Own subdomain (`{slug}.{base_domain}`)

**Central database** (`laravel`): tenants, domains, features, feature_tenant, cache, jobs  
**Tenant databases**: users, roles, permissions, inventory (per module)

### Database Connections

| Connection | Database | Purpose |
|---|---|---|
| `mysql` (default) | `laravel` | Central — tenants, features |
| dynamic | `tenant{uuid}` | Per-tenant data |

---

## Directory Structure

```
app/
├── Console/Commands/Tenant/
│   └── RemoveUnusedTenants.php     # Drops orphaned tenant databases
├── Enums/
│   ├── FeatureEnum.php             # Available modules/features
│   ├── GuardEnum.php               # Auth guards
│   └── Tenant/
│       └── RoleEnum.php            # Tenant user roles
├── Http/
│   ├── Controllers/Tenant/
│   │   ├── Auth/AuthController.php
│   │   └── Inventory/InventoryController.php
│   ├── Middleware/
│   │   ├── CheckFeature.php        # Blocks routes by tenant feature
│   │   └── HandleInertiaRequests.php
│   └── Requests/Tenant/Auth/
│       └── LoginRequest.php
├── Models/
│   ├── Central/
│   │   ├── Domain.php
│   │   ├── Feature.php             # CentralConnection, BelongsToMany Tenant
│   │   └── Tenant.php              # BelongsToMany Feature
│   └── Tenant/
│       └── User.php                # HasRoles (Spatie), HasFactory
├── Providers/
│   ├── AppServiceProvider.php
│   └── TenancyServiceProvider.php
└── Services/
    ├── BaseService.php
    └── Tenant/Auth/LoginService.php

database/
├── migrations/                     # Central migrations
│   ├── create_tenants_table.php
│   ├── create_domains_table.php
│   ├── create_features_table.php
│   └── create_feature_tenant_table.php
├── migrations/tenant/              # Run per-tenant
│   ├── create_users_table.php
│   └── create_permission_tables.php
├── factories/Tenant/
│   └── UserFactory.php             # States: owner(), admin(), regularUser()
└── seeders/
    ├── DatabaseSeeder.php          # FeaturesSeeder → CentralTenantSeeder
    ├── FeaturesSeeder.php          # Seeds all FeatureEnum cases
    ├── CentralTenantSeeder.php     # Creates music1 tenant + assigns features
    ├── TenantDatabaseSeeder.php    # RolesSeeder → AllPermissionsSeeder → OwnerSeeder
    └── Tenant/
        ├── AllPermissionsSeeder.php        # Orchestrates permission seeders
        ├── PermissionsBaseSeeder.php       # Abstract base: role groups + setPermissions()
        ├── InventoryPermissionsSeeder.php  # extends PermissionsBaseSeeder
        ├── RolesSeeder.php                 # Creates roles from RoleEnum
        └── OwnerSeeder.php                 # User::factory()->owner()->create()

resources/js/
├── composables/Tenant/
│   ├── useFeatures.ts     # hasFeature('inventory') → bool
│   ├── usePermissions.ts  # hasPermission('inventory-records-read') → bool
│   ├── useMenu.ts         # Filtered nav menu (feature + permission aware)
│   └── useTable.ts
├── layout/Tenant/
│   ├── AppLayout.vue
│   ├── AppSidebar.vue
│   ├── AppTopbar.vue
│   └── AppFooter.vue
├── Pages/Tenant/
│   ├── Auth/Login.vue
│   └── Dashboard.vue
└── plugins/
    ├── primevue.ts
    └── prime-components.ts
```

---

## Enums

### `FeatureEnum` — available modules
| Case | Value |
|---|---|
| `Inventory` | `inventory` |

### `RoleEnum` — tenant user roles
| Case | Value |
|---|---|
| `Owner` | `owner` |
| `Admin` | `admin` |
| `User` | `user` |

### `GuardEnum`
| Case | Value |
|---|---|
| `Web` | `web` |

---

## Features / Modules System

Features are defined centrally and assigned per tenant via `feature_tenant` pivot.

**Flow:**
1. `FeatureEnum` — source of truth for available features
2. `FeaturesSeeder` — inserts all enum cases into `features` table
3. `CentralTenantSeeder` — assigns selected features to a tenant via `sync()`
4. `HandleInertiaRequests` — passes `auth.features[]` to frontend via Inertia
5. `CheckFeature` middleware — guards routes (`->middleware('feature:inventory')`)
6. `useFeatures` composable — `hasFeature('inventory')` in Vue components
7. `useMenu` — filters nav items by feature + permission

---

## Permissions System

Uses `spatie/laravel-permission`. Permissions are seeded per module.

**Permission naming convention:** `{module}-{entity}-{action}`  
Examples: `inventory-records-read`, `inventory-movements-delete`

**Role groups in `PermissionsBaseSeeder`:**
| Property | Roles |
|---|---|
| `$all` | owner, admin, user |
| `$admins` | owner, admin |
| `$owners` | owner |

**Adding a new module:**
1. Create `XyzPermissionsSeeder extends PermissionsBaseSeeder`
2. Add it to `AllPermissionsSeeder::run()`
3. Done — `TenantDatabaseSeeder` requires no changes

---

## Routes

### Central
- `routes/web.php` — landing page

### Tenant (`routes/tenant.php`)
All tenant routes are wrapped in:
```php
Route::middleware(['web', 'tenant', 'prevent-central'])
```

Authenticated routes additionally use `auth`. Feature-gated groups use `feature:{name}`.

| Prefix | Middleware | Module |
|---|---|---|
| `/inventory` | `feature:inventory` | Inventory |
| `/trading` | — | Trading (placeholder) |
| `/analytics` | — | Analytics (placeholder) |
| `/integrations` | — | Integrations (placeholder) |

---

## Middleware

| Alias | Class | Purpose |
|---|---|---|
| `tenant` | `InitializeTenancyByDomain` | Bootstraps tenant context |
| `prevent-central` | `PreventAccessFromCentralDomains` | Blocks central domain from tenant routes |
| `permission` | Spatie `PermissionMiddleware` | Guards by Spatie permission |
| `feature` | `CheckFeature` | Guards by tenant feature flag |

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
migrate:fresh --seed        → central DB fresh (features + tenant created)
tenants:seed                → tenant DB seeded (roles, permissions, owner user)
tenant:remove-unused        → drops orphaned tenant databases
```

### Artisan commands
| Command | Description |
|---|---|
| `tenant:remove-unused` | Drops tenant databases with no matching tenant record |
| `tenants:seed` | Runs TenantDatabaseSeeder for all tenants |

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
const { hasFeature } = useFeatures()
hasFeature('inventory')  // → true/false

const { hasPermission } = usePermissions()
hasPermission('inventory-records-delete')  // → true/false
```

`useMenu` uses both internally to filter navigation items.

---

---

# Roadmap / Upcoming Tasks

## Inventory Module — User-Configurable (next)

### Architectural decision

Music inventory has an inherent problem: physical formats vary enormously (Vinyl, CD, Cassette, Reel-to-Reel, MiniDisc, Flexi Disc...) and tenants need to configure their own dictionaries. Hard-coding PHP enums or SQL ENUMs will not scale.

**Chosen approach: hybrid — hard columns + JSON attributes + configurable dictionaries**

### Core entities

#### `releases`
Represents a musical release (the product/catalogue entry).

```
id, uuid, title, artist_id, label_id, release_date,
barcode, catalog_number, format_definition_id,
genre_definition_id, metadata json, timestamps, deleted_at
```

#### `release_variants`
One release can have multiple variants (black vinyl / red vinyl / deluxe edition).

```
id, uuid, release_id, sku, ean,
media_type_id, condition_profile_id,
rpm, disc_count, packaging_type_id, variant_name,
attributes json, timestamps
```

`attributes` example:
```json
{ "color": "Red", "weight": "180g", "limited": true }
```

#### `inventory_items`
Stock — separated from the product.

```
id, uuid, release_variant_id, location_id,
quantity, reserved_quantity, available_quantity,
buy_price, sell_price, currency,
supplier_id, condition_grade_id, ownership_type,
received_at, metadata json, timestamps
```

### Configurable dictionaries

Each tenant configures their own enums via:

**`attribute_definitions`**
```
id, tenant_id, type (select|text|number|boolean),
code, name, entity_type, is_required, is_system, sort_order
```

**`attribute_options`**
```
id, attribute_definition_id, value, label, color, metadata json, sort_order
```

Examples tenants can configure:
- `media_type`: Vinyl, CD, Cassette, Reel-to-Reel, MiniDisc
- `vinyl_color`: Black, Red, Clear, Picture Disc
- `condition`: Mint, NM, VG+, VG, G

### Condition / grading

Separate configurable grading profiles:

**`grading_profiles`** → `id, tenant_id, name, type`  
**`grading_profile_items`** → `id, profile_id, code, label, description, score`

### What to avoid
- Giant nullable columns table (`vinyl_color`, `cassette_type`, `cd_format`...)
- SQL `ENUM` (migration hell)
- Mixing product with stock
- Full EAV (too heavy for this use case)

### Implementation plan

1. Migrations — `attribute_definitions`, `attribute_options`, `releases`, `release_variants`, `inventory_items`, `grading_profiles`, `grading_profile_items`
2. Models with typed DTO casts for JSON `attributes` columns (Spatie Data or manual cast)
3. Permission seeder — `InventoryPermissionsSeeder` expansion
4. API Resources for clean frontend contracts
5. Vue pages: index, create, edit for releases + inventory items
6. Dictionary management UI — tenant configures their own formats/grades/genres
7. Search — Meilisearch when JSON attribute queries become slow
