<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    useSlots,
    watch,
} from "vue";
import type { ColumnDef, Pagination } from "@/types/datatable";
import DataTableHeader from "./DataTableComponents/DataTableHeader.vue";
import DataTableRow from "./DataTableComponents/DataTableRow.vue";
import DataTablePagination from "./DataTableComponents/DataTablePagination.vue";
import DataTableDeleteDialog from "./DataTableComponents/DataTableDeleteDialog.vue";

export type { Pagination };

defineOptions({ name: "DataTable" });

const props = withDefaults(
    defineProps<{
        columns: ColumnDef[];
        rows: Record<string, unknown>[];
        pagination: Pagination;
        sortBy: string;
        direction: "asc" | "desc";
        filterValues?: Record<string, string>;
        loading?: boolean;
        emptyMessage?: string;
        searchable?: boolean;
        search?: string;
        searchPlaceholder?: string;
        rowRoute?: string;
        canEdit?: boolean;
        canDelete?: boolean;
    }>(),
    {
        filterValues: () => ({}),
        loading: false,
        emptyMessage: "Brak danych",
        searchable: false,
        search: "",
        searchPlaceholder: "Szukaj...",
        canEdit: true,
        canDelete: true,
    },
);

function rowHref(row: Record<string, unknown>): string | undefined {
    if (!props.rowRoute) {
        return undefined;
    }
    return route(props.rowRoute, row.id as string | number);
}

const emit = defineEmits<{
    (e: "sort", key: string): void;
    (e: "page", n: number): void;
    (e: "filter", key: string, value: string): void;
    (e: "add"): void;
    (e: "edit", row: Record<string, unknown>): void;
    (e: "delete", row: Record<string, unknown>): void;
    (e: "update:search", value: string): void;
    (e: "search", value: string): void;
    (e: "clear-filters"): void;
    (e: "bulk-delete", ids: unknown[]): void;
}>();

function onSearch(value: string): void {
    emit("update:search", value);
    emit("search", value);
}

const slots = useSlots();
const cellSlotNames = computed(() =>
    Object.keys(slots).filter((name) => name.startsWith("cell-")),
);

/* ── Active filters ── */
const hasActiveFilters = computed(() =>
    Object.values(props.filterValues).some((v) => v !== "" && v != null),
);

/* ── Row selection ── */
const selectedIds = ref<unknown[]>([]);

const isAllSelected = computed(
    () =>
        props.rows.length > 0 &&
        props.rows.every((r) => selectedIds.value.includes(r.id)),
);

function toggleAll(): void {
    if (isAllSelected.value) {
        selectedIds.value = [];
    } else {
        selectedIds.value = props.rows.map((r) => r.id);
    }
}

function toggleRow(id: unknown): void {
    const idx = selectedIds.value.indexOf(id);
    if (idx === -1) {
        selectedIds.value.push(id);
    } else {
        selectedIds.value.splice(idx, 1);
    }
}

const selectedCount = computed(() => selectedIds.value.length);

/* ── Bulk actions ── */
const showBulkDeleteDialog = ref(false);

function requestBulkDelete(): void {
    showBulkDeleteDialog.value = true;
}

function cancelBulkDelete(): void {
    showBulkDeleteDialog.value = false;
}

function confirmBulkDelete(): void {
    emit("bulk-delete", [...selectedIds.value]);
    showBulkDeleteDialog.value = false;
    selectedIds.value = [];
}

/* ── Delete confirmation ── */
const deleteTarget = ref<Record<string, unknown> | null>(null);
const showDeleteDialog = ref(false);
const deleteForm = useForm({});

function requestDelete(row: Record<string, unknown>): void {
    deleteTarget.value = row;
    showDeleteDialog.value = true;
}

function cancelDelete(): void {
    deleteTarget.value = null;
    showDeleteDialog.value = false;
}

function confirmDelete(): void {
    if (!deleteTarget.value) {
        return;
    }
    emit("delete", deleteTarget.value);
    showDeleteDialog.value = false;
    deleteTarget.value = null;
}

/* ── Ambient glow ── */
const tableRef = ref<HTMLElement | null>(null);
const glowPos = ref({ x: 50, y: 50 });

function onMouseMove(e: MouseEvent): void {
    if (!tableRef.value) {
        return;
    }
    const rect = tableRef.value.getBoundingClientRect();
    glowPos.value = {
        x: ((e.clientX - rect.left) / rect.width) * 100,
        y: ((e.clientY - rect.top) / rect.height) * 100,
    };
}

/* ── Mirror horizontal scrollbar (top) ── */
const scrollRef = ref<HTMLElement | null>(null);
const topScrollRef = ref<HTMLElement | null>(null);
const tableEl = ref<HTMLElement | null>(null);
const scrollContentWidth = ref(0);
const scrollClientWidth = ref(0);
let resizeObserver: ResizeObserver | null = null;

const isOverflowing = computed(
    () => scrollContentWidth.value > scrollClientWidth.value + 1,
);

function updateScrollWidth(): void {
    scrollContentWidth.value = scrollRef.value?.scrollWidth ?? 0;
    scrollClientWidth.value = scrollRef.value?.clientWidth ?? 0;
}

function onTopScroll(): void {
    if (scrollRef.value && topScrollRef.value) {
        scrollRef.value.scrollLeft = topScrollRef.value.scrollLeft;
    }
}

function onBottomScroll(): void {
    if (scrollRef.value && topScrollRef.value) {
        topScrollRef.value.scrollLeft = scrollRef.value.scrollLeft;
    }
}

watch(
    () => [props.rows.length, props.columns.length],
    () => nextTick(updateScrollWidth),
);

onMounted(() => {
    tableRef.value?.addEventListener("mousemove", onMouseMove);

    updateScrollWidth();
    if (typeof ResizeObserver !== "undefined" && tableEl.value) {
        resizeObserver = new ResizeObserver(() => updateScrollWidth());
        resizeObserver.observe(tableEl.value);
    }
    window.addEventListener("resize", updateScrollWidth);
});

onBeforeUnmount(() => {
    tableRef.value?.removeEventListener("mousemove", onMouseMove);
    resizeObserver?.disconnect();
    window.removeEventListener("resize", updateScrollWidth);
});
</script>

<template>
    <div ref="tableRef" class="dt-wrap">
        <!-- Ambient glow orb -->
        <div
            class="dt-glow"
            :style="{ left: `${glowPos.x}%`, top: `${glowPos.y}%` }"
        />

        <!-- Toolbar (built-in search + record count, plus optional slot) -->
        <div v-if="searchable || $slots.toolbar" class="dt-toolbar">
            <div class="dt-toolbar-left">
                <BaseInput
                    v-if="searchable"
                    class="dt-search"
                    :model-value="search"
                    :placeholder="searchPlaceholder"
                    prefix-icon="pi pi-search"
                    @update:model-value="onSearch"
                />
                <slot name="toolbar" />
            </div>

            <div v-if="searchable" class="dt-toolbar-right">
                <span class="dt-record-count">
                    {{ pagination.total }} pozycji
                </span>
            </div>
        </div>

        <!-- Sub-toolbar: filters + bulk actions -->
        <div
            v-if="hasActiveFilters || (canDelete && selectedCount >= 2)"
            class="dt-subbar"
        >
            <!-- Filters section -->
            <div v-if="hasActiveFilters" class="dt-section">
                <span class="dt-section-label">
                    <i class="pi pi-filter" />
                    Filtry
                </span>
                <button
                    type="button"
                    class="dt-section-btn dt-section-btn--ghost"
                    @click="emit('clear-filters')"
                >
                    <i class="pi pi-filter-slash" />
                    Wyczyść filtry
                </button>
            </div>

            <!-- Bulk actions section -->
            <div
                v-if="canDelete && selectedCount >= 2"
                class="dt-section dt-section--bulk"
            >
                <span class="dt-section-label">
                    <i class="pi pi-check-square" />
                    Akcje grupowe
                    <span class="dt-section-count">{{ selectedCount }}</span>
                </span>
                <button
                    type="button"
                    class="dt-section-btn dt-section-btn--danger"
                    @click="requestBulkDelete"
                >
                    <i class="pi pi-trash" />
                    Usuń zaznaczone
                </button>
            </div>
        </div>

        <!-- Mirror horizontal scrollbar (synced with the table below) -->
        <div
            v-show="isOverflowing"
            ref="topScrollRef"
            class="dt-topscroll"
            @scroll="onTopScroll"
        >
            <div
                class="dt-topscroll-spacer"
                :style="{ width: `${scrollContentWidth}px` }"
            />
        </div>

        <!-- Table -->
        <div ref="scrollRef" class="dt-scroll" @scroll="onBottomScroll">
            <table ref="tableEl" class="dt-table">
                <DataTableHeader
                    :columns="columns"
                    :sort-by="sortBy"
                    :direction="direction"
                    :filter-values="filterValues"
                    :is-all-selected="isAllSelected"
                    @sort="emit('sort', $event)"
                    @filter="(key, value) => emit('filter', key, value)"
                    @toggle-all="toggleAll"
                />

                <tbody>
                    <!-- Loading skeleton -->
                    <tr
                        v-if="loading"
                        v-for="n in 5"
                        :key="`sk-${n}`"
                        class="dt-row"
                    >
                        <td
                            :colspan="columns.length + 2"
                            class="dt-td-skeleton"
                        >
                            <div class="dt-skeleton" />
                        </td>
                    </tr>

                    <!-- Empty state -->
                    <tr v-else-if="rows.length === 0">
                        <td :colspan="columns.length + 2" class="dt-td-empty">
                            <i class="pi pi-inbox dt-empty-icon" />
                            <p>{{ emptyMessage }}</p>
                        </td>
                    </tr>

                    <!-- Data rows -->
                    <DataTableRow
                        v-for="row in rows"
                        v-else
                        :key="String(row.id)"
                        :row="row"
                        :columns="columns"
                        :selected="selectedIds.includes(row.id)"
                        :href="rowHref(row)"
                        :can-edit="canEdit"
                        :can-delete="canDelete"
                        @toggle="toggleRow"
                        @edit="emit('edit', $event)"
                        @delete="requestDelete"
                    >
                        <template
                            v-for="name in cellSlotNames"
                            :key="name"
                            #[name]="slotProps"
                        >
                            <slot :name="name" v-bind="slotProps" />
                        </template>

                        <template
                            v-if="$slots['row-actions']"
                            #row-actions="slotProps"
                        >
                            <slot name="row-actions" v-bind="slotProps" />
                        </template>
                    </DataTableRow>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <DataTablePagination
            v-if="pagination.last_page > 1"
            :pagination="pagination"
            @page="emit('page', $event)"
        />

        <!-- Delete confirmation -->
        <DataTableDeleteDialog
            :show="showDeleteDialog"
            :target="deleteTarget"
            :processing="deleteForm.processing"
            @cancel="cancelDelete"
            @confirm="confirmDelete"
        >
            <template v-if="$slots['delete-confirm-text']" #text="slotProps">
                <slot name="delete-confirm-text" v-bind="slotProps" />
            </template>
        </DataTableDeleteDialog>

        <!-- Bulk delete confirmation -->
        <DataTableDeleteDialog
            :show="showBulkDeleteDialog"
            :target="null"
            @cancel="cancelBulkDelete"
            @confirm="confirmBulkDelete"
        >
            <template #text>
                Na pewno chcesz usunąć
                <strong>{{ selectedCount }}</strong>
                zaznaczonych rekordów? Operacja jest nieodwracalna.
            </template>
        </DataTableDeleteDialog>
    </div>
</template>

<style scoped>
/* ── Container ── */
.dt-wrap {
    position: relative;
    background: linear-gradient(135deg, #0a0f1e 0%, #070b16 60%, #0d1428 100%);
    border: 1px solid rgba(56, 189, 248, 0.1);
    border-radius: 16px;
    overflow: hidden;
    isolation: isolate;
}

/* ── Glow ── */
.dt-glow {
    position: absolute;
    width: 600px;
    height: 600px;
    border-radius: 50%;
    background: radial-gradient(
        circle,
        rgba(56, 189, 248, 0.045) 0%,
        transparent 65%
    );
    transform: translate(-50%, -50%);
    pointer-events: none;
    transition:
        left 0.12s ease,
        top 0.12s ease;
    z-index: 0;
}

/* ── Toolbar ── */
.dt-toolbar {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    padding: 1.1rem 1.5rem;
    border-bottom: 1px solid rgba(56, 189, 248, 0.07);
}

.dt-toolbar-left {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    flex-wrap: wrap;
    flex: 1;
}

.dt-toolbar-right {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.dt-record-count {
    font-size: 0.78rem;
    color: rgba(148, 163, 184, 0.45);
    white-space: nowrap;
}

/* ── Search ── */
.dt-search {
    min-width: 240px;
}

/* ── Sub-toolbar (filters + bulk actions) ── */
.dt-subbar {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    padding: 0.7rem 1.5rem;
    border-bottom: 1px solid rgba(56, 189, 248, 0.07);
    background: rgba(56, 189, 248, 0.025);
}

.dt-section {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    flex-wrap: wrap;
}

.dt-section--bulk {
    margin-left: auto;
}

.dt-section-label {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: rgba(148, 163, 184, 0.55);
}

.dt-section-label .pi {
    font-size: 0.75rem;
    color: rgba(56, 189, 248, 0.5);
}

.dt-section-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.25rem;
    height: 1.25rem;
    padding: 0 0.35rem;
    border-radius: 999px;
    background: rgba(56, 189, 248, 0.15);
    border: 1px solid rgba(56, 189, 248, 0.3);
    color: #38bdf8;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0;
}

.dt-section-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.85rem;
    border-radius: 8px;
    border: 1px solid;
    font-size: 0.78rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s;
    white-space: nowrap;
}

.dt-section-btn--ghost {
    background: rgba(148, 163, 184, 0.07);
    border-color: rgba(148, 163, 184, 0.16);
    color: rgba(148, 163, 184, 0.8);
}

.dt-section-btn--ghost:hover {
    background: rgba(148, 163, 184, 0.13);
    color: #cbd5e1;
}

.dt-section-btn--danger {
    background: rgba(248, 113, 113, 0.1);
    border-color: rgba(248, 113, 113, 0.28);
    color: #f87171;
}

.dt-section-btn--danger:hover {
    background: rgba(248, 113, 113, 0.18);
    box-shadow: 0 0 14px rgba(248, 113, 113, 0.15);
}

/* ── Top mirror scrollbar ── */
.dt-topscroll {
    position: relative;
    z-index: 1;
    overflow-x: auto;
    overflow-y: hidden;
    border-bottom: 1px solid rgba(56, 189, 248, 0.07);
}

.dt-topscroll-spacer {
    height: 1px;
}

/* ── Scroll container ── */
.dt-scroll {
    position: relative;
    z-index: 1;
    overflow-x: auto;
}

/* ── Table ── */
.dt-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
}

/* ── Skeleton rows ── */
.dt-row {
    border-bottom: 1px solid rgba(255, 255, 255, 0.028);
}

.dt-td-skeleton {
    padding: 0.7rem 1rem;
}

.dt-skeleton {
    height: 14px;
    border-radius: 4px;
    background: linear-gradient(
        90deg,
        rgba(56, 189, 248, 0.04) 25%,
        rgba(56, 189, 248, 0.08) 50%,
        rgba(56, 189, 248, 0.04) 75%
    );
    background-size: 200% 100%;
    animation: dt-shimmer 1.4s infinite;
}

@keyframes dt-shimmer {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}

/* ── Empty ── */
.dt-td-empty {
    text-align: center;
    padding: 4rem 2rem;
    color: rgba(148, 163, 184, 0.35);
}

.dt-empty-icon {
    font-size: 2rem;
    display: block;
    margin-bottom: 0.75rem;
}
</style>
