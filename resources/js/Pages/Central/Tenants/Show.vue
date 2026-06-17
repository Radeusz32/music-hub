<script setup lang="ts">
import AppLayout from "@/layout/Central/AppLayout.vue";
import ShowLayout from "@/layout/Tenant/ShowLayout.vue";
import { Link, router, useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { ref } from "vue";
import { useDate } from "@/composables/useDate";
import { useToast } from "@/composables/useToast";
import TenantModal from "./TenantModal.vue";
import { formFromTenant, type Tenant } from "./tenants.resource";

const props = defineProps<{
    record: Tenant;
}>();

const { formatDate } = useDate();
const toast = useToast();

/* ── Edit modal ── */
const showModal = ref(false);
const form = useForm({ ...formFromTenant(props.record) });

function openEdit(): void {
    Object.assign(form, formFromTenant(props.record));
    form.clearErrors();
    showModal.value = true;
}

function closeModal(): void {
    showModal.value = false;
    form.clearErrors();
}

function submitForm(): void {
    form.put(route("central.tenants.update", { tenant: props.record.id }), {
        onSuccess: () => {
            closeModal();
            toast.success("Dane sklepu zostały zaktualizowane");
        },
    });
}

/* ── Activate / deactivate ── */
const toggleForm = useForm({});

function toggleActive(): void {
    const willActivate = !props.record.is_active;
    toggleForm.post(
        route("central.tenants.toggle-active", { tenant: props.record.id }),
        {
            preserveScroll: true,
            onSuccess: () =>
                toast.success(
                    willActivate
                        ? "Sklep został aktywowany"
                        : "Sklep został dezaktywowany",
                ),
        },
    );
}

/* ── Delete ── */
const deleteForm = useForm({});

function handleDelete(): void {
    if (!confirm("Czy na pewno chcesz usunąć ten sklep?")) {
        return;
    }
    deleteForm.delete(
        route("central.tenants.destroy", { tenant: props.record.id }),
        {
            onSuccess: () => router.visit(route("central.tenants.index")),
        },
    );
}

interface Field {
    label: string;
    value: string | null;
}

const companyFields: Field[] = [
    { label: "Nazwa firmy", value: props.record.company_name },
    { label: "NIP", value: props.record.tax_id },
    { label: "REGON", value: props.record.regon },
    { label: "KRS", value: props.record.krs_number },
    { label: "E-mail", value: props.record.company_email },
    { label: "Telefon", value: props.record.company_phone },
    { label: "Strona WWW", value: props.record.website },
];

const addressFields: Field[] = [
    { label: "Ulica", value: props.record.street },
    { label: "Nr budynku", value: props.record.building_number },
    { label: "Nr lokalu", value: props.record.apartment_number },
    { label: "Kod pocztowy", value: props.record.postal_code },
    { label: "Miasto", value: props.record.city },
    { label: "Kraj", value: props.record.country },
];
</script>

<template>
    <AppLayout>
        <ShowLayout :title="record.company_name ?? 'Sklep'">
            <template #actions>
                <Link
                    :href="route('central.tenants.index')"
                    class="action-btn ghost"
                >
                    <i class="pi pi-arrow-left" />
                    Wróć
                </Link>
                <button
                    type="button"
                    class="action-btn"
                    :class="record.is_active ? 'deactivate' : 'activate'"
                    :disabled="toggleForm.processing"
                    @click="toggleActive"
                >
                    <i
                        :class="
                            record.is_active
                                ? 'pi pi-ban'
                                : 'pi pi-check-circle'
                        "
                    />
                    {{ record.is_active ? "Dezaktywuj" : "Aktywuj" }}
                </button>
                <button type="button" class="action-btn edit" @click="openEdit">
                    <i class="pi pi-pencil" />
                    Edytuj
                </button>
                <button
                    type="button"
                    class="action-btn delete"
                    @click="handleDelete"
                >
                    <i class="pi pi-trash" />
                    Usuń
                </button>
            </template>

            <div class="grid gap-5 lg:grid-cols-2">
                <!-- Dane firmy -->
                <section class="card">
                    <h2 class="card-heading">Dane firmy</h2>
                    <dl class="field-list">
                        <div
                            v-for="f in companyFields"
                            :key="f.label"
                            class="field-row"
                        >
                            <dt>{{ f.label }}</dt>
                            <dd>{{ f.value || "-" }}</dd>
                        </div>
                    </dl>
                </section>

                <!-- Adres -->
                <section class="card">
                    <h2 class="card-heading">Adres</h2>
                    <dl class="field-list">
                        <div
                            v-for="f in addressFields"
                            :key="f.label"
                            class="field-row"
                        >
                            <dt>{{ f.label }}</dt>
                            <dd>{{ f.value || "-" }}</dd>
                        </div>
                    </dl>
                </section>

                <!-- Domeny -->
                <section class="card">
                    <h2 class="card-heading">Domeny</h2>
                    <div class="flex flex-wrap gap-2">
                        <span
                            v-for="domain in record.domains"
                            :key="domain"
                            class="chip"
                        >
                            <i class="pi pi-globe text-xs" />
                            {{ domain }}
                        </span>
                        <span
                            v-if="!record.domains.length"
                            class="text-sm text-slate-500"
                            >Brak domen</span
                        >
                    </div>
                </section>

                <!-- Funkcje -->
                <section class="card">
                    <h2 class="card-heading">Aktywne funkcje</h2>
                    <div class="flex flex-wrap gap-2">
                        <span
                            v-for="feature in record.features"
                            :key="feature.value"
                            class="chip feature"
                        >
                            {{ feature.label }}
                        </span>
                        <span
                            v-if="!record.features.length"
                            class="text-sm text-slate-500"
                            >Brak aktywnych funkcji</span
                        >
                    </div>
                </section>

                <!-- Metadane -->
                <section class="card lg:col-span-2">
                    <h2 class="card-heading">Metadane</h2>
                    <dl class="field-list">
                        <div class="field-row">
                            <dt>Status</dt>
                            <dd>
                                <span
                                    class="status-badge"
                                    :class="
                                        record.is_active
                                            ? 'active'
                                            : 'deactivated'
                                    "
                                >
                                    <span class="status-dot" />
                                    {{
                                        record.is_active
                                            ? "Aktywny"
                                            : "Nieaktywny"
                                    }}
                                </span>
                            </dd>
                        </div>
                        <div class="field-row">
                            <dt>Identyfikator</dt>
                            <dd class="mono">{{ record.id }}</dd>
                        </div>
                        <div class="field-row">
                            <dt>Utworzono</dt>
                            <dd>{{ formatDate(record.created_at) }}</dd>
                        </div>
                        <div class="field-row">
                            <dt>Zaktualizowano</dt>
                            <dd>{{ formatDate(record.updated_at) }}</dd>
                        </div>
                    </dl>
                </section>
            </div>
        </ShowLayout>

        <TenantModal
            :show="showModal"
            title="Edytuj sklep"
            :form="form"
            @close="closeModal"
            @submit="submitForm"
        />
    </AppLayout>
</template>

<style scoped>
.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    white-space: nowrap;
}

.action-btn.ghost {
    background: transparent;
    border: 1px solid rgba(148, 163, 184, 0.18);
    color: #94a3b8;
}
.action-btn.ghost:hover {
    border-color: rgba(148, 163, 184, 0.35);
    color: #e2e8f0;
}

.action-btn.edit {
    background: linear-gradient(
        135deg,
        rgba(168, 85, 247, 0.18),
        rgba(168, 85, 247, 0.08)
    );
    border: 1px solid rgba(168, 85, 247, 0.3);
    color: #d946ef;
}
.action-btn.edit:hover {
    box-shadow: 0 0 16px rgba(168, 85, 247, 0.2);
}

.action-btn.delete {
    background: rgba(248, 113, 113, 0.08);
    border: 1px solid rgba(248, 113, 113, 0.25);
    color: #f87171;
}
.action-btn.delete:hover {
    background: rgba(248, 113, 113, 0.16);
}

.action-btn.deactivate {
    background: rgba(248, 113, 113, 0.08);
    border: 1px solid rgba(248, 113, 113, 0.25);
    color: #f87171;
}
.action-btn.deactivate:hover:not(:disabled) {
    background: rgba(248, 113, 113, 0.16);
}

.action-btn.activate {
    background: rgba(74, 222, 128, 0.1);
    border: 1px solid rgba(74, 222, 128, 0.25);
    color: #4ade80;
}
.action-btn.activate:hover:not(:disabled) {
    background: rgba(74, 222, 128, 0.18);
}

.action-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* ── Status badge ── */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.2rem 0.6rem;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 600;
}
.status-badge .status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
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

.card {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding: 1.25rem;
    background: var(--surface-card);
    border: 1px solid rgba(168, 85, 247, 0.1);
    border-radius: 12px;
}

.card-heading {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #94a3b8;
    border-bottom: 1px solid rgba(168, 85, 247, 0.1);
    padding-bottom: 0.6rem;
}

.field-list {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}

.field-row {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    font-size: 0.875rem;
}

.field-row dt {
    color: rgba(148, 163, 184, 0.65);
}

.field-row dd {
    color: #e2e8f0;
    text-align: right;
    margin: 0;
}

.mono {
    font-family: ui-monospace, monospace;
    font-size: 0.8rem;
}

.chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.3rem 0.65rem;
    background: rgba(168, 85, 247, 0.08);
    border: 1px solid rgba(168, 85, 247, 0.16);
    border-radius: 6px;
    font-size: 0.8rem;
    color: #d8b4fe;
}

.chip.feature {
    background: rgba(99, 102, 241, 0.1);
    border-color: rgba(99, 102, 241, 0.2);
    color: #a5b4fc;
}
</style>
