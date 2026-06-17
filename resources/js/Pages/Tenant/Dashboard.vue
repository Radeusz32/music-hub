<script setup lang="ts">
import AppLayout from "@/layout/Tenant/AppLayout.vue";
import { computed, watch } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { useTable } from "@/composables/Tenant/useTable";
import { useMoney } from "@/composables/useMoney";
import { useDate } from "@/composables/useDate";
import DataTable, {
    type Pagination,
} from "@/Pages/Tenant/Components/DataTable.vue";
import {
    buildMovementColumns,
    type FilterOption,
    type InventoryMovement,
} from "@/Pages/Tenant/Inventory/movements.resource";

interface DashboardStats {
    revenue: number;
    todayUnits: number;
    records: number;
    stockUnits: number;
    stockValue: number;
    users: number;
    lowStock: number;
}

const props = defineProps<{
    stats: DashboardStats;
    movements: Pagination<InventoryMovement>;
    typeOptions: FilterOption[];
}>();

const page = usePage();
const userName = computed(() => page.props.auth?.user?.name ?? "");

const { formatPrice } = useMoney();
const { formatDate } = useDate();

/* ── Active modules ── */
interface ModuleMeta {
    label: string;
    icon: string;
    color: string;
    route: string;
}

const MODULE_META: Record<string, ModuleMeta> = {
    inventory: {
        label: "Magazyn",
        icon: "pi pi-box",
        color: "#f97316",
        route: "tenant.inventory.records.index",
    },
    trading: {
        label: "Giełdy",
        icon: "pi pi-shop",
        color: "#4ade80",
        route: "tenant.trading.events.index",
    },
    analytics: {
        label: "Analityka",
        icon: "pi pi-chart-bar",
        color: "#a78bfa",
        route: "tenant.analytics.overview",
    },
    integrations: {
        label: "Integracje",
        icon: "pi pi-link",
        color: "#60a5fa",
        route: "tenant.integrations.allegro",
    },
    users: {
        label: "Użytkownicy",
        icon: "pi pi-users",
        color: "#f472b6",
        route: "tenant.users.index",
    },
    settings: {
        label: "Ustawienia",
        icon: "pi pi-cog",
        color: "#94a3b8",
        route: "tenant.settings.profile",
    },
};

const activeModules = computed(() =>
    (page.props.auth?.features ?? [])
        .map((feature) => ({ key: feature, ...MODULE_META[feature] }))
        .filter((module): module is { key: string } & ModuleMeta =>
            Boolean(module.label),
        ),
);

/* ── Stat tiles ── */
const tiles = computed(() => [
    {
        label: "Przychód",
        value: formatPrice(props.stats.revenue),
        icon: "pi pi-wallet",
        color: "#4ade80",
    },
    {
        label: "Sprzedaż dziś",
        value: `${props.stats.todayUnits} szt.`,
        icon: "pi pi-shopping-cart",
        color: "#a78bfa",
    },
    {
        label: "Płyty",
        value: String(props.stats.records),
        icon: "pi pi-database",
        color: "#60a5fa",
    },
    {
        label: "Stan magazynu",
        value: `${props.stats.stockUnits} szt.`,
        icon: "pi pi-box",
        color: "#f97316",
    },
    {
        label: "Wartość magazynu",
        value: formatPrice(props.stats.stockValue),
        icon: "pi pi-dollar",
        color: "#fbbf24",
    },
    {
        label: "Użytkownicy",
        value: String(props.stats.users),
        icon: "pi pi-users",
        color: "#f472b6",
    },
    {
        label: "Niski stan",
        value: String(props.stats.lowStock),
        icon: "pi pi-exclamation-triangle",
        color: "#f87171",
    },
]);

/* ── Recent movements table ── */
const table = useTable({
    routeName: "tenant.dashboard",
    initialFilters: props.movements.filters,
    defaultSort: "created_at",
    defaultDirection: "desc",
    defaultPerPage: 5,
});

watch(
    () => props.movements.filters,
    (filters) => table.syncFromFilters(filters),
);

const columns = buildMovementColumns({ typeOptions: props.typeOptions });

function typeLabel(value: string): string {
    return props.typeOptions.find((o) => o.value === value)?.label ?? value;
}

function typeBadgeStyle(value: string): Record<string, string> {
    const color =
        props.typeOptions.find((o) => o.value === value)?.color ?? "#94a3b8";
    return { color, borderColor: `${color}40`, background: `${color}12` };
}

function deltaClass(delta: number): string {
    if (delta > 0) return "text-emerald-400";
    if (delta < 0) return "text-red-400";
    return "text-slate-400";
}

function formatDelta(delta: number): string {
    return delta > 0 ? `+${delta}` : String(delta);
}
</script>

<template>
    <AppLayout>
        <div class="dashboard">
            <!-- Header -->
            <div class="page-header">
                <h1>Pulpit</h1>
                <p>Witaj ponownie, {{ userName }}</p>
            </div>

            <!-- Stat tiles -->
            <div class="stats-grid">
                <div v-for="tile in tiles" :key="tile.label" class="stat-card">
                    <div class="stat-head">
                        <span class="stat-label">{{ tile.label }}</span>
                        <span
                            class="stat-icon"
                            :style="{
                                color: tile.color,
                                background: `${tile.color}1f`,
                            }"
                        >
                            <i :class="tile.icon" />
                        </span>
                    </div>
                    <div class="stat-value">{{ tile.value }}</div>
                </div>
            </div>

            <!-- Active modules -->
            <section class="panel">
                <h2 class="panel-title">Aktywne moduły</h2>
                <div class="modules-grid">
                    <Link
                        v-for="module in activeModules"
                        :key="module.key"
                        :href="route(module.route)"
                        class="module-card"
                    >
                        <span
                            class="module-icon"
                            :style="{
                                color: module.color,
                                background: `${module.color}1f`,
                            }"
                        >
                            <i :class="module.icon" />
                        </span>
                        <span class="module-body">
                            <span class="module-name">{{ module.label }}</span>
                            <span class="module-status">
                                <span class="module-dot" />
                                Aktywny
                            </span>
                        </span>
                        <i class="pi pi-chevron-right module-chevron" />
                    </Link>
                </div>
            </section>

            <!-- Recent movements -->
            <section class="panel">
                <h2 class="panel-title">Ostatnie ruchy magazynowe</h2>
                <DataTable
                    :columns="columns"
                    :rows="
                        movements.data as unknown as Record<string, unknown>[]
                    "
                    :pagination="movements as unknown as Pagination"
                    :sort-by="table.sortBy.value"
                    :direction="table.direction.value"
                    :filter-values="table.extraFilters.value"
                    empty-message="Brak ruchów magazynowych"
                    :can-edit="false"
                    :can-delete="false"
                    @sort="table.toggleSort"
                    @page="table.goToPage"
                    @filter="table.setFilter"
                    @clear-filters="table.clearFilters"
                >
                    <template #cell-created_at="{ value }">
                        {{ formatDate(value as string) }}
                    </template>

                    <template #cell-inventoryRecord="{ row }">
                        <template
                            v-if="
                                (
                                    row.inventoryRecord as {
                                        name?: string;
                                    } | null
                                )?.name
                            "
                        >
                            <span class="record-name">
                                {{
                                    (row.inventoryRecord as { name: string })
                                        .name
                                }}
                            </span>
                            <span class="record-artist">
                                {{
                                    (row.inventoryRecord as { artist: string })
                                        .artist
                                }}
                            </span>
                        </template>
                        <span v-else class="text-slate-600">—</span>
                    </template>

                    <template #cell-type="{ value }">
                        <span
                            class="type-badge"
                            :style="typeBadgeStyle(String(value))"
                        >
                            {{ typeLabel(String(value)) }}
                        </span>
                    </template>

                    <template #cell-delta="{ value }">
                        <span :class="deltaClass(Number(value))">
                            {{ formatDelta(Number(value)) }}
                        </span>
                    </template>

                    <template #cell-note="{ value }">
                        <span v-if="value" class="note-text">{{ value }}</span>
                        <span v-else class="text-slate-600">—</span>
                    </template>

                    <template #cell-user="{ row }">
                        <span
                            v-if="(row.user as { name?: string } | null)?.name"
                            class="user-chip"
                        >
                            <i class="pi pi-user" />
                            {{ (row.user as { name: string }).name }}
                        </span>
                        <span v-else class="text-slate-600">—</span>
                    </template>
                </DataTable>
            </section>
        </div>
    </AppLayout>
</template>

<style scoped>
.dashboard {
    display: flex;
    flex-direction: column;
    gap: 1.75rem;
}
.page-header h1 {
    font-size: 1.6rem;
    font-weight: 700;
    color: #e2e8f0;
    margin: 0 0 0.25rem;
}
.page-header p {
    color: #94a3b8;
    margin: 0;
}

/* Stat tiles */
.stats-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 1.25rem;
}
.stat-card {
    flex: 1 1 200px;
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    background: rgba(2, 6, 23, 0.4);
    border: 1px solid rgba(56, 189, 248, 0.1);
    border-radius: 16px;
    padding: 1.25rem;
}
.stat-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.5rem;
}
.stat-label {
    font-size: 0.68rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: rgba(148, 163, 184, 0.6);
}
.stat-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 2rem;
    width: 2rem;
    border-radius: 9px;
    font-size: 0.85rem;
    flex-shrink: 0;
}
.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #e2e8f0;
    line-height: 1.1;
}

/* Panel */
.panel {
    background: rgba(2, 6, 23, 0.4);
    border: 1px solid rgba(56, 189, 248, 0.1);
    border-radius: 16px;
    padding: 1.5rem;
}
.panel-title {
    font-size: 0.72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: rgba(148, 163, 184, 0.55);
    margin: 0 0 1.25rem;
}

/* Active modules */
.modules-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 1rem;
}
.module-card {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding: 1rem;
    border: 1px solid rgba(148, 163, 184, 0.12);
    border-radius: 12px;
    background: rgba(148, 163, 184, 0.04);
    text-decoration: none;
    transition: all 0.18s;
}
.module-card:hover {
    border-color: rgba(56, 189, 248, 0.35);
    background: rgba(56, 189, 248, 0.06);
}
.module-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 2.4rem;
    width: 2.4rem;
    border-radius: 10px;
    font-size: 1rem;
    flex-shrink: 0;
}
.module-body {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    flex: 1;
    min-width: 0;
}
.module-name {
    font-size: 0.92rem;
    font-weight: 600;
    color: #e2e8f0;
}
.module-status {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.72rem;
    color: rgba(148, 163, 184, 0.6);
}
.module-dot {
    height: 0.45rem;
    width: 0.45rem;
    border-radius: 50%;
    background: #4ade80;
    box-shadow: 0 0 6px rgba(74, 222, 128, 0.6);
}
.module-chevron {
    font-size: 0.7rem;
    color: rgba(148, 163, 184, 0.4);
}

/* Cell renderers */
.record-name {
    display: block;
    color: #e2e8f0;
    font-weight: 500;
}
.record-artist {
    display: block;
    font-size: 0.72rem;
    color: rgba(148, 163, 184, 0.5);
    margin-top: 1px;
}
.type-badge {
    padding: 0.18rem 0.5rem;
    border: 1px solid;
    border-radius: 5px;
    font-size: 0.74rem;
    font-weight: 600;
    white-space: nowrap;
}
.note-text {
    font-size: 0.8rem;
    color: #94a3b8;
}
.user-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.78rem;
    color: #94a3b8;
}
.user-chip .pi {
    font-size: 0.65rem;
    color: rgba(148, 163, 184, 0.45);
}
</style>
