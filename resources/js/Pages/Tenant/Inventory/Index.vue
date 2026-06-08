<script setup lang="ts">
import AppLayout from "@/layout/Tenant/AppLayout.vue";
import IndexLayout from "@/layout/Tenant/IndexLayout.vue";
import { useInventoryTable } from "@/composables/Tenant/useInventoryTable";
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { computed, ref, watch } from "vue";
import { useToast } from "@/composables/useToast";
import { usePermissions } from "@/composables/Tenant/usePermissions";
import { useFeatures } from "@/composables/Tenant/useFeatures";
import DataTable, {
    type Pagination,
} from "@/Pages/Tenant/Components/DataTable.vue";
import InventoryRecordModal from "./InventoryRecordModal.vue";
import InventoryImportModal from "./InventoryImportModal.vue";
import {
    defaultInventoryForm,
    type FilterOption,
    type InventoryRecord,
} from "./inventory.resource";

/* ── Props ── */
const props = defineProps<{
    records: Pagination<InventoryRecord>;
    formatOptions: FilterOption[];
    conditionOptions: FilterOption[];
}>();

/* ── Table composable (state + display helpers + column config) ── */
const table = useInventoryTable({
    initialFilters: props.records.filters,
    formatOptions: props.formatOptions,
    conditionOptions: props.conditionOptions,
});

watch(
    () => props.records.filters,
    (f) => table.syncFromFilters(f),
);

/* ── Modal state ── */
const showModal = ref(false);
const editingRecord = ref<InventoryRecord | null>(null);
const modalTitle = computed(() =>
    editingRecord.value ? "Edytuj płytę" : "Dodaj płytę",
);

/* ── Toast ── */
const toast = useToast();

/* ── CRUD capabilities (feature + permission gated) ── */
const { hasPermission } = usePermissions();
const { hasFeature } = useFeatures();

const canCreate = computed(
    () => hasFeature("inventory") && hasPermission("inventory-records-create"),
);
const canUpdate = computed(
    () => hasFeature("inventory") && hasPermission("inventory-records-update"),
);
const canDelete = computed(
    () => hasFeature("inventory") && hasPermission("inventory-records-delete"),
);

/* ── Form ── */
const form = useForm({ ...defaultInventoryForm });

function openAdd(): void {
    editingRecord.value = null;
    form.reset();
    showModal.value = true;
}

function openEdit(row: Record<string, unknown>): void {
    const record = row as unknown as InventoryRecord;
    editingRecord.value = record;
    Object.assign(form, table.formFromRecord(record));
    showModal.value = true;
}

function closeModal(): void {
    showModal.value = false;
    editingRecord.value = null;
    form.reset();
    form.clearErrors();
}

function submitForm(): void {
    if (editingRecord.value) {
        form.put(
            route("tenant.inventory.records.update", {
                inventoryRecord: editingRecord.value.id,
            }),
            {
                onSuccess: () => {
                    closeModal();
                    toast.success("Płyta została zaktualizowana");
                },
            },
        );
    } else {
        form.post(route("tenant.inventory.records.store"), {
            onSuccess: () => {
                closeModal();
                toast.success("Płyta została dodana");
            },
        });
    }
}

/* ── Delete ── */
const deleteForm = useForm({});

function handleDelete(row: Record<string, unknown>): void {
    deleteForm.delete(
        route("tenant.inventory.records.destroy", { inventoryRecord: row.id }),
        {
            onSuccess: () => toast.success("Płyta została usunięta"),
        },
    );
}

/* ── Bulk delete ── */
const bulkDeleteForm = useForm<{ ids: Array<number | string> }>({ ids: [] });

function handleBulkDelete(ids: unknown[]): void {
    bulkDeleteForm.ids = ids as Array<number | string>;
    bulkDeleteForm.post(route("tenant.inventory.records.bulk-destroy"), {
        preserveScroll: true,
        onSuccess: () =>
            toast.success(`Usunięto zaznaczone płyty (${ids.length})`),
    });
}

/* ── Import ── */
const showImportModal = ref(false);
const importForm = useForm<{ file: File | null }>({ file: null });

function openImport(): void {
    importForm.reset();
    importForm.clearErrors();
    showImportModal.value = true;
}

function closeImport(): void {
    showImportModal.value = false;
    importForm.reset();
    importForm.clearErrors();
}

function submitImport(): void {
    importForm.post(route("tenant.inventory.records.import"), {
        forceFormData: true,
        onSuccess: () => closeImport(),
        onError: (errors) => {
            const msg = Object.values(errors)[0];
            if (msg) toast.error(msg);
        },
    });
}
</script>

<template>
    <AppLayout>
        <IndexLayout
            title="Magazyn płyt"
            subtitle="Zarządzaj kolekcją płyt w swoim sklepie"
            icon="pi pi-box"
            icon-color="#f97316"
        >
            <!-- Toolbar -->
            <template #toolbar>
                <div class="table-toolbar">
                    <button
                        v-if="canCreate"
                        type="button"
                        class="btn-import"
                        @click="openImport"
                    >
                        <i class="pi pi-upload" />
                        Importuj
                    </button>
                    <button
                        v-if="canCreate"
                        type="button"
                        class="btn-add"
                        @click="openAdd"
                    >
                        <i class="pi pi-plus" />
                        Dodaj płytę
                    </button>
                </div>
            </template>

            <!-- DataTable -->
            <DataTable
                v-model:search="table.search.value"
                :columns="table.columns"
                :rows="records.data as unknown as Record<string, unknown>[]"
                :pagination="records as unknown as Pagination"
                :sort-by="table.sortBy.value"
                :direction="table.direction.value"
                :filter-values="table.extraFilters.value"
                searchable
                search-placeholder="Szukaj płyt, artystów..."
                row-route="tenant.inventory.records.show"
                empty-message="Brak płyt w magazynie"
                :can-edit="canUpdate"
                :can-delete="canDelete"
                @search="table.onSearchInput"
                @sort="table.toggleSort"
                @page="table.goToPage"
                @filter="table.setFilter"
                @clear-filters="table.clearFilters"
                @edit="openEdit"
                @delete="handleDelete"
                @bulk-delete="handleBulkDelete"
            >
                <!-- name + catalog_number -->
                <template #cell-name="{ value, row }">
                    <span class="record-name">{{ value }}</span>
                    <span v-if="row.catalog_number" class="catalog-num">
                        {{ row.catalog_number }}
                    </span>
                </template>

                <!-- genre -->
                <template #cell-genre="{ value }">
                    <span class="genre-badge">{{ value }}</span>
                </template>

                <!-- format colored badge -->
                <template #cell-format="{ value }">
                    <span
                        class="format-badge"
                        :style="table.formatBadgeStyle(String(value))"
                    >
                        {{ table.formatLabel(String(value)) }}
                    </span>
                </template>

                <!-- condition dot + label -->
                <template #cell-condition="{ value }">
                    <span class="condition-cell">
                        <span
                            class="condition-dot"
                            :style="table.conditionDotStyle(String(value))"
                        />
                        {{ table.conditionLabel(String(value)) }}
                    </span>
                </template>

                <!-- quantity colored -->
                <template #cell-quantity="{ value }">
                    <span :class="table.quantityClass(Number(value))">
                        {{ value }}
                    </span>
                </template>

                <!-- price formatted -->
                <template #cell-sale_price="{ value }">
                    {{ table.formatPrice(value as string | null) }}
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
                    <span v-else class="text-slate-600">—</span>
                </template>

                <!-- created_at / updated_at -->
                <template #cell-created_at="{ value }">
                    {{ table.formatDate(value as string) }}
                </template>

                <template #cell-updated_at="{ value }">
                    {{ table.formatDate(value as string) }}
                </template>

                <!-- delete confirm text -->
                <template #delete-confirm-text="{ row }">
                    Czy na pewno chcesz usunąć
                    <strong>{{ row?.name }}</strong>
                    z magazynu?
                </template>
            </DataTable>
        </IndexLayout>

        <!-- Import Modal -->
        <InventoryImportModal
            :show="showImportModal"
            :form="importForm"
            @close="closeImport"
            @submit="submitImport"
        />

        <!-- Add / Edit Modal -->
        <InventoryRecordModal
            :show="showModal"
            :title="modalTitle"
            :form="form"
            :format-options="formatOptions"
            :condition-options="conditionOptions"
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

.btn-import {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.5rem 1rem;
    background: linear-gradient(
        135deg,
        rgba(74, 222, 128, 0.12),
        rgba(74, 222, 128, 0.06)
    );
    border: 1px solid rgba(74, 222, 128, 0.25);
    border-radius: 8px;
    color: #4ade80;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}

.btn-import:hover {
    background: linear-gradient(
        135deg,
        rgba(74, 222, 128, 0.22),
        rgba(74, 222, 128, 0.12)
    );
    box-shadow: 0 0 16px rgba(74, 222, 128, 0.15);
    border-color: rgba(74, 222, 128, 0.45);
}

.btn-ghost {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.5rem 0.85rem;
    background: transparent;
    border: 1px solid rgba(148, 163, 184, 0.15);
    border-radius: 8px;
    color: rgba(148, 163, 184, 0.6);
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    white-space: nowrap;
}

.btn-ghost:hover {
    border-color: rgba(148, 163, 184, 0.3);
    color: #94a3b8;
}

/* ── Cell renderers ── */
.record-name {
    display: block;
    color: #e2e8f0;
    font-weight: 500;
}
.catalog-num {
    display: block;
    font-size: 0.7rem;
    color: rgba(148, 163, 184, 0.4);
    margin-top: 1px;
}

.genre-badge {
    padding: 0.18rem 0.5rem;
    background: rgba(148, 163, 184, 0.07);
    border: 1px solid rgba(148, 163, 184, 0.11);
    border-radius: 5px;
    font-size: 0.74rem;
    color: #94a3b8;
}

.format-badge {
    padding: 0.18rem 0.5rem;
    border: 1px solid;
    border-radius: 5px;
    font-size: 0.74rem;
    font-weight: 600;
    white-space: nowrap;
}

.condition-cell {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.8rem;
    font-weight: 500;
}
.condition-dot {
    display: inline-block;
    width: 7px;
    height: 7px;
    border-radius: 50%;
    flex-shrink: 0;
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
