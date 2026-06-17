<script setup lang="ts">
import { computed } from "vue";
import type { ColumnDef } from "@/types/datatable";
import DataTableColumnFilter from "./DataTableColumnFilter.vue";

defineOptions({ name: "DataTableHeader" });

const props = defineProps<{
    columns: ColumnDef[];
    sortBy: string;
    direction: "asc" | "desc";
    filterValues: Record<string, string>;
    isAllSelected: boolean;
}>();

const emit = defineEmits<{
    (e: "sort", key: string): void;
    (e: "filter", key: string, value: string): void;
    (e: "toggle-all"): void;
}>();

const hasColumnFilters = computed(() => props.columns.some((c) => c.filter));
</script>

<template>
    <thead>
        <!-- ── Row 1: Filters ── -->
        <tr v-if="hasColumnFilters" class="dt-thead-filters">
            <!-- Checkbox placeholder -->
            <th class="dt-th dt-th-check dt-th-filter-cell" />

            <!-- Filter cell per column -->
            <th
                v-for="col in columns"
                :key="`filter-${col.key}`"
                class="dt-th dt-th-filter-cell"
                :class="`text-${col.align ?? 'left'}`"
                :style="col.width ? { width: col.width } : {}"
            >
                <DataTableColumnFilter
                    v-if="col.filter"
                    :column="col"
                    :filter-values="filterValues"
                    @filter="(key, value) => emit('filter', key, value)"
                />
            </th>

            <!-- Actions placeholder -->
            <th class="dt-th dt-th-actions dt-th-filter-cell" />
        </tr>

        <!-- ── Row 2: Column labels ── -->
        <tr class="dt-thead-labels">
            <!-- Checkbox -->
            <th class="dt-th dt-th-check">
                <BaseCheckbox
                    :model-value="isAllSelected"
                    @update:model-value="emit('toggle-all')"
                />
            </th>

            <!-- Column labels + sort -->
            <th
                v-for="col in columns"
                :key="col.key"
                class="dt-th"
                :class="`text-${col.align ?? 'left'}`"
                :style="col.width ? { width: col.width } : {}"
            >
                <span
                    class="dt-th-inner"
                    :class="{ 'dt-th-sortable': col.sortable }"
                    @click="col.sortable ? emit('sort', col.key) : null"
                >
                    {{ col.label }}
                    <span
                        v-if="col.sortable"
                        class="dt-sort"
                        :class="{
                            'dt-sort-active': sortBy === col.key,
                            'dt-sort-desc':
                                sortBy === col.key && direction === 'desc',
                        }"
                    >
                        <i class="pi pi-chevron-up" />
                        <i class="pi pi-chevron-down" />
                    </span>
                </span>
            </th>

            <!-- Actions -->
            <th class="dt-th dt-th-actions">Akcje</th>
        </tr>
    </thead>
</template>

<style scoped>
/* ── Filter row ── */
.dt-thead-filters {
    background: rgba(56, 189, 248, 0.022);
}

.dt-thead-filters .dt-th-filter-cell {
    padding: 0.6rem 1rem;
    vertical-align: middle;
    /* no border-bottom here - separator comes after the whole filter row */
}

/* Thin separator between filter row and label row */
.dt-thead-filters td,
.dt-thead-filters th {
    border-bottom: 1px solid rgba(56, 189, 248, 0.07);
}

/* ── Label row ── */
.dt-thead-labels {
    border-bottom: 1px solid rgba(56, 189, 248, 0.08);
}

/* ── Shared th ── */
.dt-th {
    padding: 0.7rem 1rem;
    text-align: left;
    color: rgba(148, 163, 184, 0.55);
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.07em;
    white-space: nowrap;
    user-select: none;
}

/* Vertical separators between columns (not after the actions column) */
.dt-thead-filters .dt-th:not(:last-child),
.dt-thead-labels .dt-th:not(:last-child) {
    border-right: 1px solid rgba(56, 189, 248, 0.06);
}

.dt-th-check {
    width: 44px;
    padding-left: 1.25rem;
}

.dt-th-actions {
    text-align: right;
    padding-right: 1.25rem;
}

/* ── Sort ── */
.dt-th-inner {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.dt-sort {
    display: inline-flex;
    flex-direction: column;
    gap: 1px;
    opacity: 0.3;
    transition: opacity 0.15s;
    line-height: 1;
}

.dt-sort i {
    font-size: 7px;
    display: block;
}

.dt-sort-active {
    opacity: 1;
    color: #38bdf8;
}

.dt-sort-active.dt-sort-desc .pi-chevron-up {
    opacity: 0.25;
}

.dt-sort-active:not(.dt-sort-desc) .pi-chevron-down {
    opacity: 0.25;
}

.dt-th-sortable {
    cursor: pointer;
    transition: color 0.15s;
}

.dt-th-sortable:hover {
    color: #38bdf8;
}

/* ── Text alignment helpers ── */
.text-left {
    text-align: left;
}
.text-right {
    text-align: right;
}
.text-center {
    text-align: center;
}
</style>
