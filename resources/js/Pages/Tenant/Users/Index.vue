<script setup lang="ts">
import AppLayout from "@/layout/Tenant/AppLayout.vue";
import IndexLayout from "@/layout/Tenant/IndexLayout.vue";
import { useUserTable } from "@/composables/Tenant/useUserTable";
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { computed, ref, watch } from "vue";
import { useToast } from "@/composables/useToast";
import { usePermissions } from "@/composables/Tenant/usePermissions";
import { useFeatures } from "@/composables/Tenant/useFeatures";
import DataTable, {
    type Pagination,
} from "@/Pages/Tenant/Components/DataTable.vue";
import UserModal from "./UserModal.vue";
import {
    defaultUserForm,
    type FilterOption,
    type UserRow,
} from "./users.resource";

/* ── Props ── */
const props = defineProps<{
    users: Pagination<UserRow>;
    roleOptions: FilterOption[];
}>();

/* ── Table composable (state + display helpers + column config) ── */
const table = useUserTable({
    initialFilters: props.users.filters,
    roleOptions: props.roleOptions,
});

watch(
    () => props.users.filters,
    (f) => table.syncFromFilters(f),
);

/* ── Modal state ── */
const showModal = ref(false);
const editingUser = ref<UserRow | null>(null);
const modalTitle = computed(() =>
    editingUser.value ? "Edytuj użytkownika" : "Dodaj użytkownika",
);

/* ── Toast ── */
const toast = useToast();

/* ── CRUD capabilities (feature + permission gated) ── */
const { hasPermission } = usePermissions();
const { hasFeature } = useFeatures();

const canCreate = computed(
    () => hasFeature("users") && hasPermission("users-create"),
);
const canUpdate = computed(
    () => hasFeature("users") && hasPermission("users-update"),
);
const canDelete = computed(
    () => hasFeature("users") && hasPermission("users-delete"),
);

/* ── Form ── */
const form = useForm({ ...defaultUserForm });

function openAdd(): void {
    editingUser.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
}

function openEdit(row: Record<string, unknown>): void {
    const user = row as unknown as UserRow;
    editingUser.value = user;
    Object.assign(form, table.formFromUser(user));
    form.clearErrors();
    showModal.value = true;
}

function closeModal(): void {
    showModal.value = false;
    editingUser.value = null;
    form.reset();
    form.clearErrors();
}

function submitForm(): void {
    if (editingUser.value) {
        form.put(route("tenant.users.update", { user: editingUser.value.id }), {
            onSuccess: () => {
                closeModal();
                toast.success("Użytkownik został zaktualizowany");
            },
        });
    } else {
        form.post(route("tenant.users.store"), {
            onSuccess: () => {
                closeModal();
                toast.success("Użytkownik został dodany");
            },
        });
    }
}

/* ── Delete ── */
const deleteForm = useForm({});

function handleDelete(row: Record<string, unknown>): void {
    deleteForm.delete(route("tenant.users.destroy", { user: row.id }), {
        onSuccess: () => toast.success("Użytkownik został usunięty"),
    });
}

/* ── Bulk delete ── */
const bulkDeleteForm = useForm<{ ids: Array<number | string> }>({ ids: [] });

function handleBulkDelete(ids: unknown[]): void {
    bulkDeleteForm.ids = ids as Array<number | string>;
    bulkDeleteForm.post(route("tenant.users.bulk-destroy"), {
        preserveScroll: true,
        onSuccess: () =>
            toast.success(`Usunięto zaznaczonych użytkowników (${ids.length})`),
    });
}
</script>

<template>
    <AppLayout>
        <IndexLayout
            title="Użytkownicy"
            subtitle="Zarządzaj zespołem swojego sklepu"
            icon="pi pi-users"
            icon-color="#f472b6"
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
                        Dodaj użytkownika
                    </button>
                </div>
            </template>

            <!-- DataTable -->
            <DataTable
                v-model:search="table.search.value"
                :columns="table.columns"
                :rows="users.data as unknown as Record<string, unknown>[]"
                :pagination="users as unknown as Pagination"
                :sort-by="table.sortBy.value"
                :direction="table.direction.value"
                :filter-values="table.extraFilters.value"
                searchable
                search-placeholder="Szukaj po imieniu, nazwisku, e-mailu..."
                row-route="tenant.users.show"
                empty-message="Brak użytkowników"
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
                <!-- name + email subtitle -->
                <template #cell-name="{ value, row }">
                    <span class="user-name">{{ value }}</span>
                    <span class="user-sub">{{ row.email }}</span>
                </template>

                <!-- email -->
                <template #cell-email="{ value }">
                    <span class="user-email">{{ value }}</span>
                </template>

                <!-- phone -->
                <template #cell-phone="{ value }">
                    <span v-if="value" class="user-phone">
                        <i class="pi pi-phone" />
                        {{ value }}
                    </span>
                    <span v-else class="text-slate-600">—</span>
                </template>

                <!-- role colored badge -->
                <template #cell-role="{ value }">
                    <span
                        v-if="value"
                        class="role-badge"
                        :style="table.roleBadgeStyle(String(value))"
                    >
                        {{ table.roleLabel(String(value)) }}
                    </span>
                    <span v-else class="text-slate-600">—</span>
                </template>

                <!-- verification status -->
                <template #cell-email_verified_at="{ value }">
                    <span v-if="value" class="verify-badge verify-badge--ok">
                        <i class="pi pi-check-circle" />
                        Zweryfikowany
                    </span>
                    <span v-else class="verify-badge verify-badge--pending">
                        <i class="pi pi-clock" />
                        Oczekuje
                    </span>
                </template>

                <!-- active status badge -->
                <template #cell-is_active="{ value }">
                    <span
                        class="role-badge"
                        :style="table.activeBadgeStyle(!!value)"
                    >
                        {{ value ? "Aktywny" : "Nieaktywny" }}
                    </span>
                </template>

                <!-- created_at -->
                <template #cell-created_at="{ value }">
                    {{ table.formatDate(value as string) }}
                </template>

                <!-- delete confirm text -->
                <template #delete-confirm-text="{ row }">
                    Czy na pewno chcesz usunąć użytkownika
                    <strong>{{ row?.name }}</strong>
                    ?
                </template>
            </DataTable>
        </IndexLayout>

        <!-- Add / Edit Modal -->
        <UserModal
            :show="showModal"
            :title="modalTitle"
            :is-edit="!!editingUser"
            :form="form"
            :role-options="roleOptions"
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
.user-name {
    display: block;
    color: #e2e8f0;
    font-weight: 500;
}
.user-sub {
    display: block;
    font-size: 0.7rem;
    color: rgba(148, 163, 184, 0.4);
    margin-top: 1px;
}

.user-email {
    color: #94a3b8;
    font-size: 0.82rem;
}

.user-phone {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    color: #94a3b8;
    font-size: 0.82rem;
}
.user-phone .pi {
    font-size: 0.7rem;
    color: rgba(148, 163, 184, 0.45);
}

.role-badge {
    padding: 0.18rem 0.5rem;
    border: 1px solid;
    border-radius: 5px;
    font-size: 0.74rem;
    font-weight: 600;
    white-space: nowrap;
}

.verify-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.74rem;
    font-weight: 500;
    white-space: nowrap;
}
.verify-badge .pi {
    font-size: 0.7rem;
}
.verify-badge--ok {
    color: #4ade80;
}
.verify-badge--pending {
    color: #fb923c;
}
</style>
