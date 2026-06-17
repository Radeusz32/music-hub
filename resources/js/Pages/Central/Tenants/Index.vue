<script setup lang="ts">
import AppLayout from "@/layout/Central/AppLayout.vue";
import IndexLayout from "@/layout/Tenant/IndexLayout.vue";
import { useTenantTable } from "@/composables/Central/useTenantTable";
import { router, useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { computed, ref, watch } from "vue";
import { useToast } from "@/composables/useToast";
import DataTable, {
    type Pagination,
} from "@/Pages/Tenant/Components/DataTable.vue";
import TenantModal from "./TenantModal.vue";
import {
    defaultTenantForm,
    formFromTenant,
    type Tenant,
} from "./tenants.resource";

/* ── Props ── */
const props = defineProps<{
    records: Pagination<Tenant>;
}>();

/* ── Table composable ── */
const table = useTenantTable({
    initialFilters: props.records.filters,
});

watch(
    () => props.records.filters,
    (f) => table.syncFromFilters(f),
);

/* ── Modal state ── */
const showModal = ref(false);
const editingTenant = ref<Tenant | null>(null);
const modalTitle = computed(() =>
    editingTenant.value ? "Edytuj sklep" : "Dodaj sklep",
);

/* ── Toast ── */
const toast = useToast();

/* ── Form ── */
const form = useForm({ ...defaultTenantForm });

function openEdit(row: Record<string, unknown>): void {
    const tenant = row as unknown as Tenant;
    editingTenant.value = tenant;
    Object.assign(form, formFromTenant(tenant));
    showModal.value = true;
}

function closeModal(): void {
    showModal.value = false;
    editingTenant.value = null;
    form.reset();
    form.clearErrors();
}

function submitForm(): void {
    if (editingTenant.value) {
        form.put(
            route("central.tenants.update", {
                tenant: editingTenant.value.id,
            }),
            {
                onSuccess: () => {
                    closeModal();
                    toast.success("Dane sklepu zostały zaktualizowane");
                },
            },
        );
    } else {
        form.post(route("central.tenants.store"), {
            onSuccess: () => {
                closeModal();
                toast.success("Sklep został dodany");
            },
        });
    }
}

/* ── Delete ── */
const deleteForm = useForm({});

function handleDelete(row: Record<string, unknown>): void {
    deleteForm.delete(route("central.tenants.destroy", { tenant: row.id }), {
        onSuccess: () => toast.success("Sklep został usunięty"),
    });
}

/* ── Activate / deactivate ── */
const togglingId = ref<string | null>(null);

function toggleActive(row: Record<string, unknown>): void {
    const tenant = row as unknown as Tenant;
    if (togglingId.value) {
        return;
    }
    togglingId.value = tenant.id;

    const willActivate = !tenant.is_active;

    router.post(
        route("central.tenants.toggle-active", { tenant: tenant.id }),
        {},
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () =>
                toast.success(
                    `Sklep ${tenant.company_name ?? ""} ${willActivate ? "aktywowany" : "dezaktywowany"}`,
                ),
            onFinish: () => {
                togglingId.value = null;
            },
        },
    );
}

/* ── Bulk delete ── */
const bulkDeleteForm = useForm<{ ids: Array<number | string> }>({ ids: [] });

function handleBulkDelete(ids: unknown[]): void {
    bulkDeleteForm.ids = ids as Array<number | string>;
    bulkDeleteForm.post(route("central.tenants.bulk-destroy"), {
        preserveScroll: true,
        onSuccess: () =>
            toast.success(`Usunięto zaznaczone sklepy (${ids.length})`),
    });
}
</script>

<template>
    <AppLayout>
        <IndexLayout
            title="Sklepy"
            subtitle="Zarządzaj wszystkimi sklepami na platformie"
            icon="pi pi-building"
            icon-color="#d946ef"
        >
            <!-- Toolbar -->
            <template #toolbar>
                <div class="table-toolbar"></div>
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
                search-placeholder="Szukaj sklepów, NIP, miasta..."
                row-route="central.tenants.show"
                empty-message="Brak sklepów"
                @search="table.onSearchInput"
                @sort="table.toggleSort"
                @page="table.goToPage"
                @filter="table.setFilter"
                @clear-filters="table.clearFilters"
                @edit="openEdit"
                @delete="handleDelete"
                @bulk-delete="handleBulkDelete"
            >
                <!-- company name -->
                <template #cell-company_name="{ value }">
                    <span class="tenant-name">{{ value ?? "—" }}</span>
                </template>

                <!-- status badge -->
                <template #cell-is_active="{ row }">
                    <span
                        class="status-badge"
                        :class="row.is_active ? 'active' : 'deactivated'"
                    >
                        <span class="status-dot" />
                        {{ row.is_active ? "Aktywny" : "Nieaktywny" }}
                    </span>
                </template>

                <!-- row actions: activate / deactivate -->
                <template #row-actions="{ row }">
                    <button
                        type="button"
                        class="status-toggle"
                        :class="row.is_active ? 'deactivate' : 'activate'"
                        :disabled="togglingId === row.id"
                        :title="row.is_active ? 'Dezaktywuj' : 'Aktywuj'"
                        @click.stop.prevent="toggleActive(row)"
                    >
                        <i
                            :class="
                                row.is_active
                                    ? 'pi pi-ban'
                                    : 'pi pi-check-circle'
                            "
                        />
                    </button>
                </template>

                <!-- nip -->
                <template #cell-tax_id="{ value }">
                    <span v-if="value" class="mono">{{ value }}</span>
                    <span v-else class="text-slate-600">—</span>
                </template>

                <!-- domains -->
                <template #cell-domains="{ row }">
                    <div class="flex flex-wrap gap-1">
                        <span
                            v-for="domain in (row.domains as string[]) ?? []"
                            :key="domain"
                            class="domain-chip"
                        >
                            {{ domain }}
                        </span>
                        <span
                            v-if="!((row.domains as string[]) ?? []).length"
                            class="text-slate-600"
                            >—</span
                        >
                    </div>
                </template>

                <!-- created_at -->
                <template #cell-created_at="{ value }">
                    {{ table.formatDate(value as string) }}
                </template>

                <!-- delete confirm text -->
                <template #delete-confirm-text="{ row }">
                    Czy na pewno chcesz usunąć sklep
                    <strong>{{ row?.company_name }}</strong>
                    ? Baza danych tenanta zostanie usunięta.
                </template>
            </DataTable>
        </IndexLayout>

        <!-- Add / Edit Modal -->
        <TenantModal
            :show="showModal"
            :title="modalTitle"
            :form="form"
            @close="closeModal"
            @submit="submitForm"
        />
    </AppLayout>
</template>

<style scoped>
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
        rgba(168, 85, 247, 0.18),
        rgba(168, 85, 247, 0.08)
    );
    border: 1px solid rgba(168, 85, 247, 0.3);
    border-radius: 8px;
    color: #d946ef;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}

.btn-add:hover {
    background: linear-gradient(
        135deg,
        rgba(168, 85, 247, 0.28),
        rgba(168, 85, 247, 0.14)
    );
    box-shadow: 0 0 16px rgba(168, 85, 247, 0.2);
    border-color: rgba(168, 85, 247, 0.5);
}

.tenant-name {
    display: block;
    color: #e2e8f0;
    font-weight: 500;
}

.mono {
    font-family: ui-monospace, monospace;
    font-size: 0.82rem;
    color: #94a3b8;
}

.domain-chip {
    padding: 0.16rem 0.5rem;
    background: rgba(168, 85, 247, 0.08);
    border: 1px solid rgba(168, 85, 247, 0.16);
    border-radius: 5px;
    font-size: 0.72rem;
    color: #d8b4fe;
}

/* ── Status badge ── */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.2rem 0.55rem;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 600;
    white-space: nowrap;
}

.status-badge .status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    flex-shrink: 0;
}

.status-badge.active {
    background: rgba(74, 222, 128, 0.1);
    color: #4ade80;
}
.status-badge.active .status-dot {
    background: #4ade80;
    box-shadow: 0 0 6px #4ade80aa;
}

.status-badge.deactivated {
    background: rgba(248, 113, 113, 0.1);
    color: #f87171;
}
.status-badge.deactivated .status-dot {
    background: #f87171;
}

.status-toggle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.18s;
    font-size: 0.75rem;
}

.status-toggle:disabled {
    opacity: 0.5;
    cursor: wait;
}

.status-toggle.deactivate {
    background: rgba(248, 113, 113, 0.08);
    border: 1px solid rgba(248, 113, 113, 0.22);
    color: #f87171;
}
.status-toggle.deactivate:hover:not(:disabled) {
    background: rgba(248, 113, 113, 0.18);
}

.status-toggle.activate {
    background: rgba(74, 222, 128, 0.1);
    border: 1px solid rgba(74, 222, 128, 0.22);
    color: #4ade80;
}
.status-toggle.activate:hover:not(:disabled) {
    background: rgba(74, 222, 128, 0.18);
}
</style>
