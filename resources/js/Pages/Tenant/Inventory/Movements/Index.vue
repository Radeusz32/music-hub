<script setup lang="ts">
import AppLayout from "@/layout/Tenant/AppLayout.vue";
import IndexLayout from "@/layout/Tenant/IndexLayout.vue";
import { useMovementsTable } from "@/composables/Tenant/useMovementsTable";
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { computed, ref, watch } from "vue";
import { useToast } from "@/composables/useToast";
import { usePermissions } from "@/composables/Tenant/usePermissions";
import { useFeatures } from "@/composables/Tenant/useFeatures";
import DataTable, {
    type Pagination,
} from "@/Pages/Tenant/Components/DataTable.vue";
import InventoryMovementModal from "../InventoryMovementModal.vue";
import {
    defaultMovementForm,
    type FilterOption,
    type InventoryMovement,
    type RecordOption,
} from "../movements.resource";

/* ── Props ── */
const props = defineProps<{
    movements: Pagination<InventoryMovement>;
    typeOptions: FilterOption[];
    manualTypeOptions: FilterOption[];
    recordOptions: RecordOption[];
}>();

/* ── Table composable (state + display helpers + column config) ── */
const table = useMovementsTable({
    initialFilters: props.movements.filters,
    typeOptions: props.typeOptions,
});

watch(
    () => props.movements.filters,
    (f) => table.syncFromFilters(f),
);

/* ── Toast ── */
const toast = useToast();

/* ── CRUD capabilities (feature + permission gated) ── */
const { hasPermission } = usePermissions();
const { hasFeature } = useFeatures();

const canCreate = computed(
    () =>
        hasFeature("inventory") && hasPermission("inventory-movements-create"),
);
const canDelete = computed(
    () =>
        hasFeature("inventory") && hasPermission("inventory-movements-delete"),
);

/* ── Create modal ── */
const showModal = ref(false);
const form = useForm({ ...defaultMovementForm });

function openAdd(): void {
    form.reset();
    form.clearErrors();
    showModal.value = true;
}

function closeModal(): void {
    showModal.value = false;
    form.reset();
    form.clearErrors();
}

function submitForm(): void {
    form.post(route("tenant.inventory.movements.store"), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            toast.success("Ruch magazynowy został zapisany");
        },
        onError: (errors) => {
            const msg = Object.values(errors)[0];
            if (msg) toast.error(msg);
        },
    });
}

/* ── Delete ── */
const deleteForm = useForm({});

function handleDelete(row: Record<string, unknown>): void {
    deleteForm.delete(
        route("tenant.inventory.movements.destroy", {
            inventoryMovement: row.id,
        }),
        {
            preserveScroll: true,
            onSuccess: () => toast.success("Ruch magazynowy został usunięty"),
        },
    );
}

/* ── Bulk delete ── */
const bulkDeleteForm = useForm<{ ids: Array<number | string> }>({ ids: [] });

function handleBulkDelete(ids: unknown[]): void {
    bulkDeleteForm.ids = ids as Array<number | string>;
    bulkDeleteForm.post(route("tenant.inventory.movements.bulk-destroy"), {
        preserveScroll: true,
        onSuccess: () =>
            toast.success(`Usunięto zaznaczone ruchy (${ids.length})`),
    });
}
</script>

<template>
    <AppLayout>
        <IndexLayout
            title="Ruchy magazynowe"
            subtitle="Historia przyjęć, wydań i korekt stanu magazynowego"
            icon="pi pi-sort-alt"
            icon-color="#38bdf8"
        >
            <!-- Toolbar -->
            <template #toolbar>
                <div class="table-toolbar">
                    <button
                        v-if="canCreate"
                        type="button"
                        class="btn-add"
                        @click="openAdd"
                    >
                        <i class="pi pi-plus" />
                        Dodaj ruch
                    </button>
                </div>
            </template>

            <!-- DataTable -->
            <DataTable
                v-model:search="table.search.value"
                :columns="table.columns"
                :rows="movements.data as unknown as Record<string, unknown>[]"
                :pagination="movements as unknown as Pagination"
                :sort-by="table.sortBy.value"
                :direction="table.direction.value"
                :filter-values="table.extraFilters.value"
                searchable
                search-placeholder="Szukaj po powodzie..."
                empty-message="Brak ruchów magazynowych"
                :can-edit="false"
                :can-delete="canDelete"
                @search="table.onSearchInput"
                @sort="table.toggleSort"
                @page="table.goToPage"
                @filter="table.setFilter"
                @clear-filters="table.clearFilters"
                @delete="handleDelete"
                @bulk-delete="handleBulkDelete"
            >
                <!-- date -->
                <template #cell-created_at="{ value }">
                    {{ table.formatDate(value as string) }}
                </template>

                <!-- record -->
                <template #cell-inventoryRecord="{ row }">
                    <template
                        v-if="
                            (row.inventoryRecord as { name?: string } | null)
                                ?.name
                        "
                    >
                        <span class="record-name">
                            {{ (row.inventoryRecord as { name: string }).name }}
                        </span>
                        <span class="record-artist">
                            {{
                                (row.inventoryRecord as { artist: string })
                                    .artist
                            }}
                        </span>
                    </template>
                    <span v-else class="text-slate-600">-</span>
                </template>

                <!-- type colored badge -->
                <template #cell-type="{ value }">
                    <span
                        class="type-badge"
                        :style="table.typeBadgeStyle(String(value))"
                    >
                        {{ table.typeLabel(String(value)) }}
                    </span>
                </template>

                <!-- delta colored -->
                <template #cell-delta="{ value }">
                    <span :class="table.deltaClass(Number(value))">
                        {{ table.formatDelta(Number(value)) }}
                    </span>
                </template>

                <!-- note -->
                <template #cell-note="{ value }">
                    <span v-if="value" class="note-text">{{ value }}</span>
                    <span v-else class="text-slate-600">-</span>
                </template>

                <!-- user -->
                <template #cell-user="{ row }">
                    <span
                        v-if="(row.user as { name?: string } | null)?.name"
                        class="user-chip"
                    >
                        <i class="pi pi-user" />
                        {{ (row.user as { name: string }).name }}
                    </span>
                    <span v-else class="text-slate-600">-</span>
                </template>

                <!-- delete confirm text -->
                <template #delete-confirm-text="{ row }">
                    Czy na pewno chcesz usunąć ten ruch? Stan magazynowy płyty
                    <strong>{{
                        (row?.inventoryRecord as { name?: string } | null)?.name
                    }}</strong>
                    zostanie skorygowany.
                </template>
            </DataTable>
        </IndexLayout>

        <!-- Add Modal -->
        <InventoryMovementModal
            :show="showModal"
            :form="form"
            :record-options="recordOptions"
            :type-options="manualTypeOptions"
            @close="closeModal"
            @submit="submitForm"
        />
    </AppLayout>
</template>

<style scoped>
/* ── Toolbar ── */
.table-toolbar {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.7rem;
    flex-wrap: wrap;
}

.btn-add {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.5rem 1rem;
    background: linear-gradient(
        135deg,
        rgba(56, 189, 248, 0.18),
        rgba(56, 189, 248, 0.08)
    );
    border: 1px solid rgba(56, 189, 248, 0.3);
    border-radius: 8px;
    color: #38bdf8;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}

.btn-add:hover {
    background: linear-gradient(
        135deg,
        rgba(56, 189, 248, 0.28),
        rgba(56, 189, 248, 0.14)
    );
    box-shadow: 0 0 16px rgba(56, 189, 248, 0.2);
    border-color: rgba(56, 189, 248, 0.5);
}

/* ── Cell renderers ── */
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
