<script setup lang="ts">
import AppLayout from "@/layout/Tenant/AppLayout.vue";
import ShowLayout from "@/layout/Tenant/ShowLayout.vue";
import UserModal from "./UserModal.vue";
import DataTableDeleteDialog from "@/Pages/Tenant/Components/DataTableComponents/DataTableDeleteDialog.vue";
import { Link, useForm, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { computed, ref } from "vue";
import { useToast } from "@/composables/useToast";
import { useDate } from "@/composables/useDate";
import { usePermissions } from "@/composables/Tenant/usePermissions";
import { useFeatures } from "@/composables/Tenant/useFeatures";
import {
    defaultUserForm,
    resolveOptionColor,
    resolveOptionLabel,
    type FilterOption,
    type UserFormData,
    type UserRow,
} from "./users.resource";

const props = defineProps<{
    user: UserRow;
    roleOptions: FilterOption[];
}>();

const toast = useToast();
const { formatDate } = useDate();

/* ── CRUD capabilities (feature + permission gated) ── */
const { hasPermission } = usePermissions();
const { hasFeature } = useFeatures();

const canUpdate = computed(
    () => hasFeature("users") && hasPermission("users-update"),
);
const canDelete = computed(
    () => hasFeature("users") && hasPermission("users-delete"),
);

/* ── Current user (block self-delete) ── */
const page = usePage();
const isSelf = computed(
    () =>
        (page.props.auth as { user?: { id?: number } } | null)?.user?.id ===
        props.user.id,
);

/* ── Display helpers ── */
const initials = computed(() =>
    `${props.user.first_name.charAt(0)}${props.user.last_name.charAt(0)}`.toUpperCase(),
);

const roleLabel = computed(() =>
    props.user.role
        ? resolveOptionLabel(props.roleOptions, props.user.role)
        : "—",
);

const roleBadgeStyle = computed(() => {
    const color = props.user.role
        ? resolveOptionColor(props.roleOptions, props.user.role)
        : "#94a3b8";
    return {
        color,
        borderColor: `${color}40`,
        background: `${color}12`,
    };
});

const addressLine = computed(() => {
    const u = props.user;
    const buildingApartment = [u.building_number, u.apartment_number]
        .filter(Boolean)
        .join("/");
    const line1 = [u.street, buildingApartment].filter(Boolean).join(" ");
    const line2 = [u.postal_code, u.city].filter(Boolean).join(" ");
    return [line1, line2].filter(Boolean).join(", ") || "—";
});

/* ── Edit modal ── */
const showEditModal = ref(false);
const form = useForm<UserFormData>({ ...defaultUserForm });

function openEdit(): void {
    Object.assign(form, {
        first_name: props.user.first_name,
        last_name: props.user.last_name,
        email: props.user.email,
        phone: props.user.phone ?? "",
        street: props.user.street ?? "",
        building_number: props.user.building_number ?? "",
        apartment_number: props.user.apartment_number ?? "",
        postal_code: props.user.postal_code ?? "",
        city: props.user.city ?? "",
        pesel: props.user.pesel ?? "",
        is_active: props.user.is_active,
        password: "",
        role: props.user.role ?? "user",
    });
    form.clearErrors();
    showEditModal.value = true;
}

function closeEdit(): void {
    showEditModal.value = false;
    form.reset();
    form.clearErrors();
}

function submitEdit(): void {
    form.put(route("tenant.users.update", { user: props.user.id }), {
        onSuccess: () => {
            closeEdit();
            toast.success("Dane użytkownika zostały zaktualizowane");
        },
    });
}

/* ── Delete ── */
const showDeleteDialog = ref(false);
const deleteForm = useForm({});

function confirmDelete(): void {
    deleteForm.delete(route("tenant.users.destroy", { user: props.user.id }), {
        onSuccess: () => toast.success("Użytkownik został usunięty"),
    });
}
</script>

<template>
    <AppLayout>
        <ShowLayout :title="user.name">
            <template #actions>
                <Link
                    :href="route('tenant.users.index')"
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
                    v-if="canDelete && !isSelf"
                    type="button"
                    class="action-btn action-delete"
                    @click="showDeleteDialog = true"
                >
                    <i class="pi pi-trash" />
                    Usuń
                </button>
            </template>

            <!-- ── Hero card ── -->
            <div class="card hero">
                <div class="hero-avatar" :style="roleBadgeStyle">
                    {{ initials }}
                </div>
                <div class="hero-info">
                    <h2 class="hero-name">{{ user.name }}</h2>
                    <a :href="`mailto:${user.email}`" class="hero-email">
                        <i class="pi pi-envelope" />
                        {{ user.email }}
                    </a>
                    <div class="hero-badges">
                        <span class="role-badge" :style="roleBadgeStyle">
                            {{ roleLabel }}
                        </span>
                        <span
                            v-if="user.email_verified_at"
                            class="verify-badge verify-badge--ok"
                        >
                            <i class="pi pi-check-circle" />
                            Zweryfikowany
                        </span>
                        <span v-else class="verify-badge verify-badge--pending">
                            <i class="pi pi-clock" />
                            Oczekuje na weryfikację
                        </span>
                        <span
                            class="role-badge"
                            :style="{
                                color: user.is_active ? '#4ade80' : '#f87171',
                                borderColor: user.is_active
                                    ? '#4ade8040'
                                    : '#f8717140',
                                background: user.is_active
                                    ? '#4ade8012'
                                    : '#f8717112',
                            }"
                        >
                            {{ user.is_active ? "Aktywny" : "Nieaktywny" }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- ── Details card ── -->
            <div class="card">
                <h3 class="card-title">Szczegóły konta</h3>
                <dl class="details-grid">
                    <div class="detail">
                        <dt>Imię</dt>
                        <dd>{{ user.first_name }}</dd>
                    </div>
                    <div class="detail">
                        <dt>Nazwisko</dt>
                        <dd>{{ user.last_name }}</dd>
                    </div>
                    <div class="detail">
                        <dt>Telefon</dt>
                        <dd>{{ user.phone || "—" }}</dd>
                    </div>
                    <div class="detail">
                        <dt>PESEL</dt>
                        <dd>{{ user.pesel || "—" }}</dd>
                    </div>
                    <div class="detail">
                        <dt>Rola</dt>
                        <dd>{{ roleLabel }}</dd>
                    </div>
                    <div class="detail">
                        <dt>Status konta</dt>
                        <dd>{{ user.is_active ? "Aktywny" : "Nieaktywny" }}</dd>
                    </div>
                    <div class="detail">
                        <dt>Status weryfikacji</dt>
                        <dd>
                            {{
                                user.email_verified_at
                                    ? formatDate(user.email_verified_at)
                                    : "Niezweryfikowany"
                            }}
                        </dd>
                    </div>
                    <div class="detail detail--wide">
                        <dt>Adres</dt>
                        <dd>{{ addressLine }}</dd>
                    </div>
                    <div class="detail">
                        <dt>Dołączył</dt>
                        <dd>{{ formatDate(user.created_at) }}</dd>
                    </div>
                    <div class="detail">
                        <dt>Ostatnia aktualizacja</dt>
                        <dd>{{ formatDate(user.updated_at) }}</dd>
                    </div>
                </dl>
            </div>
        </ShowLayout>

        <!-- ── Edit modal ── -->
        <UserModal
            :show="showEditModal"
            title="Edytuj użytkownika"
            :is-edit="true"
            :form="form"
            :role-options="roleOptions"
            @close="closeEdit"
            @submit="submitEdit"
        />

        <!-- ── Delete dialog ── -->
        <DataTableDeleteDialog
            :show="showDeleteDialog"
            :target="user as unknown as Record<string, unknown>"
            :processing="deleteForm.processing"
            @cancel="showDeleteDialog = false"
            @confirm="confirmDelete"
        >
            <template #text>
                Czy na pewno chcesz usunąć użytkownika
                <strong>{{ user.name }}</strong>
                ? Operacja jest nieodwracalna.
            </template>
        </DataTableDeleteDialog>
    </AppLayout>
</template>

<style scoped>
/* ── Cards ── */
.card {
    background: linear-gradient(135deg, #0a0f1e 0%, #070b16 60%, #0d1428 100%);
    border: 1px solid rgba(56, 189, 248, 0.1);
    border-radius: 16px;
    padding: 1.5rem;
}

.card-title {
    font-size: 0.78rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: rgba(148, 163, 184, 0.55);
    margin: 0 0 1.25rem;
}

/* ── Hero ── */
.hero {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.hero-avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 72px;
    height: 72px;
    border-radius: 18px;
    border: 1px solid;
    font-size: 1.6rem;
    font-weight: 700;
    flex-shrink: 0;
}

.hero-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.hero-name {
    font-size: 1.35rem;
    font-weight: 700;
    color: #e2e8f0;
    margin: 0;
}

.hero-email {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.85rem;
    color: #94a3b8;
    text-decoration: none;
    width: fit-content;
}
.hero-email:hover {
    color: #38bdf8;
}
.hero-email .pi {
    font-size: 0.75rem;
}

.hero-badges {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    flex-wrap: wrap;
    margin-top: 0.25rem;
}

.role-badge {
    padding: 0.22rem 0.6rem;
    border: 1px solid;
    border-radius: 6px;
    font-size: 0.76rem;
    font-weight: 600;
    white-space: nowrap;
}

.verify-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.76rem;
    font-weight: 500;
    white-space: nowrap;
}
.verify-badge .pi {
    font-size: 0.72rem;
}
.verify-badge--ok {
    color: #4ade80;
}
.verify-badge--pending {
    color: #fb923c;
}

/* ── Details ── */
.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.25rem;
    margin: 0;
}

.detail {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.detail--wide {
    grid-column: 1 / -1;
}

.detail dt {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: rgba(148, 163, 184, 0.5);
}

.detail dd {
    font-size: 0.9rem;
    color: #e2e8f0;
    margin: 0;
}

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
