<script setup lang="ts">
import AppLayout from "@/layout/Tenant/AppLayout.vue";
import ShowLayout from "@/layout/Tenant/ShowLayout.vue";
import InventoryRecordModal from "./InventoryRecordModal.vue";
import InventoryHeroCard from "./Show/InventoryHeroCard.vue";
import InventoryDetailsCard from "./Show/InventoryDetailsCard.vue";
import InventoryMetaCard from "./Show/InventoryMetaCard.vue";
import InventoryHistoryCard from "./Show/InventoryHistoryCard.vue";
import InventoryDeleteDialog from "./Show/InventoryDeleteDialog.vue";
import { Link, useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { computed, ref } from "vue";
import { useToast } from "@/composables/useToast";
import { usePermissions } from "@/composables/Tenant/usePermissions";
import { useFeatures } from "@/composables/Tenant/useFeatures";
import {
    defaultInventoryForm,
    type FilterOption,
    type InventoryFormData,
    type InventoryRecord,
} from "./inventory.resource";

const props = defineProps<{
    record: InventoryRecord;
    formatOptions: FilterOption[];
    conditionOptions: FilterOption[];
    movementTypeOptions: FilterOption[];
}>();

const toast = useToast();

/* ── CRUD capabilities (feature + permission gated) ── */
const { hasPermission } = usePermissions();
const { hasFeature } = useFeatures();

const canUpdate = computed(
    () => hasFeature("inventory") && hasPermission("inventory-records-update"),
);
const canDelete = computed(
    () => hasFeature("inventory") && hasPermission("inventory-records-delete"),
);

/* ── Tabs ── */
const tabs = computed(() => [
    { value: "details", label: "Szczegóły", icon: "pi pi-info-circle" },
    {
        value: "history",
        label: "Historia ruchów",
        icon: "pi pi-history",
        badge: props.record.movements?.length ?? 0,
    },
]);

/* ── Edit modal ── */
const showEditModal = ref(false);
const form = useForm<InventoryFormData>({ ...defaultInventoryForm });

function openEdit(): void {
    const r = props.record;
    Object.assign(form, {
        name: r.name,
        artist: r.artist,
        genre: r.genre,
        format: r.format,
        condition: r.condition,
        label: r.label ?? "",
        catalog_number: r.catalog_number ?? "",
        barcode: r.barcode ?? "",
        country: r.country ?? "",
        year: r.year ?? "",
        quantity: r.quantity,
        purchase_price: r.purchase_price ?? "",
        sale_price: r.sale_price ?? "",
        notes: r.notes ?? "",
    });
    showEditModal.value = true;
}

function closeEdit(): void {
    showEditModal.value = false;
    form.reset();
    form.clearErrors();
}

function submitEdit(): void {
    form.put(
        route("tenant.inventory.records.update", {
            inventoryRecord: props.record.id,
        }),
        {
            onSuccess: () => {
                closeEdit();
                toast.success("Płyta została zaktualizowana");
            },
        },
    );
}

/* ── Delete ── */
const showDeleteDialog = ref(false);
const deleteForm = useForm({});

function confirmDelete(): void {
    deleteForm.delete(
        route("tenant.inventory.records.destroy", {
            inventoryRecord: props.record.id,
        }),
        {
            onSuccess: () => toast.success("Płyta została usunięta"),
        },
    );
}
</script>

<template>
    <AppLayout>
        <ShowLayout :title="record.name">
            <template #actions>
                <Link
                    :href="route('tenant.inventory.records.index')"
                    class="action-btn action-back"
                >
                    <i class="pi pi-arrow-left" />
                    Powrót do listy
                </Link>
                <button
                    v-if="canUpdate"
                    type="button"
                    class="action-btn action-edit"
                    @click="openEdit"
                >
                    <i class="pi pi-pencil" />
                    Edytuj
                </button>
                <button
                    v-if="canDelete"
                    type="button"
                    class="action-btn action-delete"
                    @click="showDeleteDialog = true"
                >
                    <i class="pi pi-trash" />
                    Usuń
                </button>
            </template>

            <BaseTab :tabs="tabs">
                <template #details>
                    <InventoryHeroCard
                        :record="record"
                        :format-options="formatOptions"
                        :condition-options="conditionOptions"
                    />

                    <InventoryDetailsCard :record="record" />

                    <InventoryMetaCard :record="record" />
                </template>

                <template #history>
                    <InventoryHistoryCard
                        :movements="record.movements ?? []"
                        :type-options="movementTypeOptions"
                    />
                </template>
            </BaseTab>
        </ShowLayout>

        <!-- ── Edit modal ── -->
        <InventoryRecordModal
            :show="showEditModal"
            title="Edytuj płytę"
            :form="form"
            :format-options="formatOptions"
            :condition-options="conditionOptions"
            @close="closeEdit"
            @submit="submitEdit"
        />

        <!-- ── Delete dialog ── -->
        <InventoryDeleteDialog
            :show="showDeleteDialog"
            :record-name="record.name"
            :processing="deleteForm.processing"
            @close="showDeleteDialog = false"
            @confirm="confirmDelete"
        />
    </AppLayout>
</template>

<style scoped>
/* ── Action buttons ── */
.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.48rem 1rem;
    border-radius: 8px;
    border: 1px solid;
    font-size: 0.8rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.18s;
}

.action-back {
    background: rgba(148, 163, 184, 0.07);
    border-color: rgba(148, 163, 184, 0.18);
    color: #94a3b8;
    text-decoration: none;
}
.action-back:hover {
    background: rgba(148, 163, 184, 0.14);
    color: #cbd5e1;
}

.action-edit {
    background: rgba(56, 189, 248, 0.1);
    border-color: rgba(56, 189, 248, 0.28);
    color: #38bdf8;
}
.action-edit:hover {
    background: rgba(56, 189, 248, 0.18);
    box-shadow: 0 0 14px rgba(56, 189, 248, 0.18);
}

.action-delete {
    background: rgba(248, 113, 113, 0.08);
    border-color: rgba(248, 113, 113, 0.22);
    color: #f87171;
}
.action-delete:hover {
    background: rgba(248, 113, 113, 0.16);
    box-shadow: 0 0 14px rgba(248, 113, 113, 0.15);
}
</style>
