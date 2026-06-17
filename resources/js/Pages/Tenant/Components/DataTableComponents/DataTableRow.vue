<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import type { ColumnDef } from "@/types/datatable";

defineOptions({ name: "DataTableRow" });

const props = withDefaults(
    defineProps<{
        row: Record<string, unknown>;
        columns: ColumnDef[];
        selected: boolean;
        href?: string;
        canEdit?: boolean;
        canDelete?: boolean;
    }>(),
    {
        canEdit: true,
        canDelete: true,
    },
);

const emit = defineEmits<{
    (e: "toggle", id: unknown): void;
    (e: "edit", row: Record<string, unknown>): void;
    (e: "delete", row: Record<string, unknown>): void;
}>();

function onRowClick(): void {
    if (props.href) {
        router.visit(props.href);
    }
}
</script>

<template>
    <tr
        class="dt-row"
        :class="{ 'dt-row-selected': selected, 'dt-row-clickable': href }"
        @click="onRowClick"
    >
        <td class="dt-td dt-td-check" @click.stop>
            <BaseCheckbox
                :model-value="selected"
                @update:model-value="emit('toggle', row.id)"
            />
        </td>

        <td
            v-for="col in columns"
            :key="col.key"
            class="dt-td"
            :class="`text-${col.align ?? 'left'}`"
        >
            <!-- Custom slot per column -->
            <slot :name="`cell-${col.key}`" :value="row[col.key]" :row="row">
                {{ row[col.key] ?? "—" }}
            </slot>
        </td>

        <td class="dt-td dt-td-actions" @click.stop>
            <div class="dt-actions">
                <slot name="row-actions" :row="row" />
                <button
                    v-if="canEdit"
                    class="dt-action-btn dt-action-edit"
                    title="Edytuj"
                    @click="emit('edit', row)"
                >
                    <i class="pi pi-pencil" />
                </button>
                <button
                    v-if="canDelete"
                    class="dt-action-btn dt-action-delete"
                    title="Usuń"
                    @click="emit('delete', row)"
                >
                    <i class="pi pi-trash" />
                </button>
            </div>
        </td>
    </tr>
</template>

<style scoped>
.dt-row {
    border-bottom: 1px solid rgba(255, 255, 255, 0.028);
    transition: background 0.12s;
}

.dt-row:hover {
    background: rgba(56, 189, 248, 0.04);
}

.dt-row-selected {
    background: rgba(56, 189, 248, 0.06);
}

.dt-row-clickable {
    cursor: pointer;
}

/* ── TD ── */
.dt-td {
    padding: 0.7rem 1rem;
    vertical-align: middle;
    color: #cbd5e1;
}

/* Vertical separators between columns (not after the actions column) */
.dt-td:not(:last-child) {
    border-right: 1px solid rgba(56, 189, 248, 0.05);
}

.dt-td-check {
    padding-left: 1.25rem;
}

.dt-td-actions {
    padding-right: 1.25rem;
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

/* ── Actions ── */
.dt-actions {
    display: flex;
    gap: 0.35rem;
    justify-content: flex-end;
}

.dt-action-btn {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    border: 1px solid transparent;
    background: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    transition:
        background 0.15s,
        border-color 0.15s,
        color 0.15s;
    color: rgba(148, 163, 184, 0.45);
}

.dt-action-edit:hover {
    background: rgba(56, 189, 248, 0.1);
    border-color: rgba(56, 189, 248, 0.3);
    color: #38bdf8;
}

.dt-action-delete:hover {
    background: rgba(248, 113, 113, 0.1);
    border-color: rgba(248, 113, 113, 0.3);
    color: #f87171;
}
</style>
