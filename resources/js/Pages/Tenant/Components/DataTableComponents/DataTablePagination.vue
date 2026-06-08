<script setup lang="ts">
import type { Pagination } from "@/types/datatable";

defineOptions({ name: "DataTablePagination" });

const props = defineProps<{
    pagination: Pagination;
}>();

const emit = defineEmits<{
    (e: "page", n: number): void;
}>();

function visiblePages(): number[] {
    const total = props.pagination.last_page;
    const current = props.pagination.current_page;
    const pages: number[] = [];

    for (let p = 1; p <= total; p++) {
        if (p === 1 || p === total || Math.abs(p - current) <= 2) {
            pages.push(p);
        }
    }

    return pages;
}

function showEllipsisBefore(
    page: number,
    index: number,
    pages: number[],
): boolean {
    return index > 0 && page - pages[index - 1] > 1;
}
</script>

<template>
    <div class="dt-pagination">
        <button
            class="dt-page-btn"
            :disabled="pagination.current_page === 1"
            @click="emit('page', pagination.current_page - 1)"
        >
            <i class="pi pi-chevron-left" />
        </button>

        <template v-for="(page, idx) in visiblePages()" :key="page">
            <span
                v-if="showEllipsisBefore(page, idx, visiblePages())"
                class="dt-page-ellipsis"
            >
                …
            </span>
            <button
                class="dt-page-btn"
                :class="{ 'dt-page-active': page === pagination.current_page }"
                @click="emit('page', page)"
            >
                {{ page }}
            </button>
        </template>

        <button
            class="dt-page-btn"
            :disabled="pagination.current_page === pagination.last_page"
            @click="emit('page', pagination.current_page + 1)"
        >
            <i class="pi pi-chevron-right" />
        </button>

        <span class="dt-page-info">
            {{ pagination.from }}–{{ pagination.to }} z {{ pagination.total }}
        </span>
    </div>
</template>

<style scoped>
.dt-pagination {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.9rem 1.5rem;
    border-top: 1px solid rgba(56, 189, 248, 0.07);
    flex-wrap: wrap;
}

.dt-page-btn {
    min-width: 30px;
    height: 30px;
    padding: 0 0.45rem;
    background: rgba(15, 23, 42, 0.6);
    border: 1px solid rgba(56, 189, 248, 0.1);
    border-radius: 6px;
    color: #94a3b8;
    font-size: 0.78rem;
    cursor: pointer;
    transition: all 0.15s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.dt-page-btn:hover:not(:disabled) {
    background: rgba(56, 189, 248, 0.1);
    border-color: rgba(56, 189, 248, 0.3);
    color: #38bdf8;
}

.dt-page-active {
    background: rgba(56, 189, 248, 0.15) !important;
    border-color: rgba(56, 189, 248, 0.5) !important;
    color: #38bdf8 !important;
    font-weight: 600;
}

.dt-page-btn:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

.dt-page-ellipsis {
    color: rgba(148, 163, 184, 0.35);
    font-size: 0.78rem;
    padding: 0 0.2rem;
}

.dt-page-info {
    margin-left: auto;
    font-size: 0.72rem;
    color: rgba(148, 163, 184, 0.4);
}
</style>
