# Filterable Columns Guide

How server-side table filtering works end-to-end: a `DataTableConfig` declares
**which** columns are filterable and **how**, `BaseService` resolves the request
params into query constraints, and the frontend `DataTable` renders the matching
input for each column.

> There is **no** legacy `filterMapping()` / `dateRangeColumns()` anymore.
> `filterableColumns()` is the single source of truth (it is `abstract` on
> `DataTableConfig`, so every config must implement it — return `[]` for none).

---

## Filter Types

The backend `type` is a **`App\Enums\FilterTypeEnum`** case (not a raw string).
The enum's backing values are the strings below — they still flow through
request params and the frontend `filter.type`, but inside `filterableColumns()`
and `BaseService` you always work with the typed enum case.

| Enum case (backing value)         | Use case                | SQL                                         | Frontend input             |
| --------------------------------- | ----------------------- | ------------------------------------------- | -------------------------- |
| `Select` (`select`)               | Dropdown, exact match   | `WHERE col = value`                         | `BaseDropdown`             |
| `Text` (`text`)                   | Partial text match      | `WHERE col LIKE '%value%'`                   | `BaseInput`                |
| `Number` (`number`)               | Number exact match      | `WHERE col = value`                         | `BaseInputNumber`          |
| `DateRange` (`date-range`)        | Date from/to            | `WHERE col >= from AND col <= to` (`whereDate`) | `DatePicker` ×2        |
| `NumberRange` (`number-range`)    | Numeric from/to (year, qty, price) | `WHERE col >= from AND col <= to` | `BaseInputNumber` ×2       |
| `Boolean` (`boolean`)             | True/false toggle (e.g. `is_active`) | `WHERE col = true/false`           | `boolean` (clickable ✓/✗)  |
| `NullStatus` (`null-status`)      | Nullable column presence (e.g. `email_verified_at`) | `WHERE col IS [NOT] NULL` | `boolean` (clickable ✓/✗)  |
| `Relation` (`relation`)           | Exact match on a related model's column (e.g. Spatie `roles.name`) | `WHERE EXISTS (relation WHERE col = value)` (`whereHas`) | `select` |

`FilterTypeEnum::isRange()` returns `true` for `DateRange` / `NumberRange` —
`BaseService::resolveFilters()` uses it to pick the `{key}_from` / `{key}_to`
reading strategy.

**Range convention:** range types read two request params named
`{key}_from` and `{key}_to`, where `{key}` is the array key in
`filterableColumns()`. So a config key `sale_price` of type `number-range`
reads `?sale_price_from=...&sale_price_to=...`.

**Backend type ≠ frontend type.** The backend `type` (a `FilterTypeEnum` case in
`filterableColumns()`) picks the SQL strategy; the frontend `filter.type` (a
string in the `ColumnDef`) picks the input. They are usually the same, but don't
have to be — e.g. `role` is `FilterTypeEnum::Relation` on the backend but
`select` on the frontend, and `email_verified_at` is `FilterTypeEnum::NullStatus`
on the backend but `boolean` (✓/✗) on the frontend. Both `Boolean` and
`NullStatus` are driven by the same `1` / `0` request value.

---

## Backend: declare the filters

`filterableColumns()` returns a map of **request key → config**. Each config has
a `column` (the DB column) and a `type`. `select` may also carry `options`
(only used to ship choices to the frontend — the backend ignores them).

```php
// app/Http/Resources/Tenant/Inventory/InventoryRecordDataTable.php
use App\Enums\FilterTypeEnum;

public static function filterableColumns(): array
{
    return [
        'format' => [
            'column' => 'format',
            'type' => FilterTypeEnum::Select,
            'options' => DiscFormatEnum::options(),     // value/label/color
        ],
        'condition' => [
            'column' => 'condition',
            'type' => FilterTypeEnum::Select,
            'options' => DiscConditionEnum::options(),
        ],
        'genre'      => ['column' => 'genre',      'type' => FilterTypeEnum::Select],
        'year'       => ['column' => 'year',       'type' => FilterTypeEnum::NumberRange],
        'quantity'   => ['column' => 'quantity',   'type' => FilterTypeEnum::NumberRange],
        'sale_price' => ['column' => 'sale_price', 'type' => FilterTypeEnum::NumberRange],
        'created_at' => ['column' => 'created_at', 'type' => FilterTypeEnum::DateRange],
        'updated_at' => ['column' => 'updated_at', 'type' => FilterTypeEnum::DateRange],
    ];
}
```

That is all the backend needs — no controller changes, no manual `where`s.

For the relation / nullable / boolean strategies (from the Users module):

```php
// app/Http/Resources/Tenant/Users/UserDataTable.php
use App\Enums\FilterTypeEnum;

public static function filterableColumns(): array
{
    return [
        // Spatie role lives on the `roles` relation, not a column:
        'role'              => ['column' => 'name', 'type' => FilterTypeEnum::Relation, 'relation' => 'roles'],
        // Nullable timestamp → "verified / unverified":
        'email_verified_at' => ['column' => 'email_verified_at', 'type' => FilterTypeEnum::NullStatus],
        // Real boolean column:
        'is_active'         => ['column' => 'is_active', 'type' => FilterTypeEnum::Boolean],
    ];
}
```

- `relation` requires an extra `relation` key (the relationship method); `column` is the column on the **related** table.
- `null-status` and `boolean` need no `options` — the frontend renders fixed ✓/✗ toggles.

---

## Resolution flow

```
1. User edits a filter input in the table header
   ↓
2. useTable pushes an Inertia GET, e.g.
   ?format=LP&year_from=1970&year_to=1985&sale_price_from=100
   ↓
3. Service::index() → BaseService::fetchForDataTable(ConfigClass, $request)
   ↓
4. BaseService::resolveFilters()
   - iterates filterableColumns()
   - range types read {key}_from / {key}_to
   - other types read {key}
   - builds resolved filters (skips empty values) + records all keys
     so they are preserved in the paginator's query string
   ↓
5. BaseService::applyFilters() — match on the FilterTypeEnum case:
     Select       → where(col, value)
     Text         → where(col, 'like', "%value%")
     Number       → where(col, value)
     DateRange    → whereDate(col, '>=', from) / whereDate(col, '<=', to)
     NumberRange  → where(col, '>=', from)     / where(col, '<=', to)
     Boolean      → where(col, value === '1')
     NullStatus   → '1' → whereNotNull(col) / '0' → whereNull(col)
     Relation     → whereHas(relation, fn ($q) => $q->where(col, value))
   ↓
6. Paginates, maps rows through the Transformer, returns
   paginator->toArray() merged with the active `filters` key.
```

The `match` in `applyFilters()` is **exhaustive over `FilterTypeEnum`** (no
`default` arm), so adding a new strategy is: add a `case` to the enum, add the
matching `case` in `BaseService::applyFilters()`, and write a small
`applyXxxFilter()` method — every config that uses that case then works
automatically.

---

## Frontend: render the inputs

The frontend does **not** read `filterableColumns()` directly. Instead each
column in the page's `ColumnDef[]` (built in `inventory.resource.ts`) carries a
`filter` descriptor, and `DataTableColumnFilter.vue` renders the right Base
component per type. **The two must stay in sync** (same keys, same range
`fromKey`/`toKey` names as the backend's `{key}_from`/`{key}_to`).

```ts
// resources/js/types/datatable.ts
export type ColumnFilter =
    | { type: "select"; options: FilterOption[] }
    | { type: "date-range"; fromKey: string; toKey: string }
    | { type: "number"; currency?: boolean; min?: number; max?: number; placeholder?: string }
    | { type: "number-range"; fromKey: string; toKey: string; currency?: boolean; min?: number; max?: number }
    | { type: "boolean"; trueLabel?: string; falseLabel?: string };
```

A `boolean` column filter renders two clickable icon toggles (✓ / ✗); pick it for
real boolean columns **and** for `null-status` backend filters:

```ts
// resources/js/Pages/Tenant/Users/users.resource.ts
{
    key: "is_active",
    label: "Status",
    sortable: true,
    width: "150px",
    filter: { type: "boolean", trueLabel: "Aktywny", falseLabel: "Nieaktywny" },
},
{
    key: "email_verified_at",     // backend type is `null-status`
    label: "Weryfikacja",
    width: "160px",
    filter: { type: "boolean", trueLabel: "Zweryfikowany", falseLabel: "Niezweryfikowany" },
},
{
    key: "role",                  // backend type is `relation`
    label: "Rola",
    width: "150px",
    filter: { type: "select", options: options.roleOptions },
},
```

```ts
// resources/js/Pages/Tenant/Inventory/inventory.resource.ts
{
    key: "format",
    label: "Format",
    sortable: true,
    width: "110px",
    filter: { type: "select", options: options.formatOptions },
},
{
    key: "year",
    label: "Rok",
    sortable: true,
    width: "120px",
    align: "right",
    filter: { type: "number-range", fromKey: "year_from", toKey: "year_to", min: 1900 },
},
{
    key: "sale_price",
    label: "Cena",
    sortable: true,
    width: "150px",
    align: "right",
    // `currency` → BaseInputNumber renders with currency format + "zł" suffix
    filter: { type: "number-range", fromKey: "sale_price_from", toKey: "sale_price_to", currency: true, min: 0 },
},
{
    key: "created_at",
    label: "Dodano",
    sortable: true,
    width: "180px",
    filter: { type: "date-range", fromKey: "created_at_from", toKey: "created_at_to" },
},
```

`DataTableColumnFilter.vue` notes:

- `select` / `date-range` commit immediately on change.
- `number` / `number-range` keep **local state** and emit a **debounced**
  `filter` event (~450 ms) so typing doesn't lag against the Inertia round-trip,
  and they re-sync from props (so the "Wyczyść filtry" clear-all works).
- `currency: true` → `BaseInputNumber` gets `format="currency"` + `suffix="zł"`.
- `boolean` renders two icon buttons (`pi-check` / `pi-times`) emitting `1` / `0`;
  clicking the active one clears the filter (`''`). The same value feeds either
  the backend `boolean` or `null-status` strategy.

The page wires the events to the table composable:

```vue
<DataTable
    :columns="table.columns"
    :rows="records.data"
    :pagination="records"
    :sort-by="table.sortBy.value"
    :direction="table.direction.value"
    :filter-values="table.extraFilters.value"
    searchable
    @search="table.onSearchInput"
    @sort="table.toggleSort"
    @page="table.goToPage"
    @filter="table.setFilter"
    @clear-filters="table.clearFilters"
/>
```

`useTable` keeps active filters in `extraFilters` (a `Record<string,string>`),
includes them in every Inertia visit, and `clearFilters()` resets them all in
one navigation.

---

## Checklist for adding a filter to a column

1. **Backend** — add an entry to `filterableColumns()` with the right `column` + `type` (a `FilterTypeEnum` case).
2. **Frontend** — add a `filter` descriptor to that column in `buildXxxColumns()`.
   For ranges, use `fromKey: "{key}_from"`, `toKey: "{key}_to"` matching the backend key.
3. Give numeric/price columns enough `width` so the stacked inputs fit; use
   `currency: true` for money columns.
4. Searchable free-text columns usually belong in `searchableColumns()` (global
   search) rather than a per-column `text` filter — pick whichever the UX needs.

---

## Summary

- `filterableColumns()` is the single backend source of truth (no legacy fallbacks).
- Backend types are `FilterTypeEnum` cases: `Select`, `Text`, `Number`, `DateRange`, `NumberRange`, `Boolean`, `NullStatus`, `Relation`.
- Ranges use `{key}_from` / `{key}_to` on both sides (`FilterTypeEnum::isRange()` flags them).
- `Relation` adds a `relation` key; `Boolean` / `NullStatus` are driven by `1` / `0` and need no `options`.
- Backend `type` (SQL strategy) and frontend `filter.type` (input string) are independent — e.g. `Relation`→`select`, `NullStatus`→`boolean`.
- `BaseService` resolves + applies automatically; add a new strategy by adding an enum case + a `case` in `applyFilters()`.
- The frontend renders inputs from each column's `filter` descriptor via `DataTableColumnFilter.vue`; keep the request **keys** in sync with the backend.
