<script setup lang="ts">
import AppLayout from "@/layout/Central/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { ref } from "vue";
import { useToast } from "@/composables/useToast";
import type { Pagination } from "@/Pages/Tenant/Components/DataTable.vue";

interface StatusOption {
    value: string;
    label: string;
    color: string;
}

interface InvitationRow {
    id: number;
    uuid: string;
    email: string;
    status: string;
    status_label: string;
    status_color: string;
    expires_at: string;
    tenant_id: string | null;
    created_at: string;
}

const props = defineProps<{
    invitations: Pagination<InvitationRow>;
    statusOptions: StatusOption[];
}>();

const toast = useToast();

/* ── Send invitation modal ── */
const showModal = ref(false);
const sendForm = useForm({ email: "" });

function openModal(): void {
    sendForm.reset();
    sendForm.clearErrors();
    showModal.value = true;
}

function sendInvitation(): void {
    sendForm.post(route("central.invitations.store"), {
        preserveScroll: true,
        onSuccess: () => {
            showModal.value = false;
            sendForm.reset();
            toast.success("Zaproszenie zostało wysłane.");
        },
    });
}

/* ── Delete ── */
const deleteForm = useForm({});

function deleteInvitation(id: number): void {
    deleteForm.delete(route("central.invitations.destroy", { invitation: id }), {
        preserveScroll: true,
        onSuccess: () => toast.success("Zaproszenie zostało usunięte."),
    });
}

function formatDate(iso: string): string {
    return new Date(iso).toLocaleDateString("pl-PL", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
}
</script>

<template>
    <AppLayout>
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-6 px-4 py-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight" style="color: var(--text-color)">
                        Zaproszenia
                    </h1>
                    <p class="text-sm" style="color: var(--text-color-secondary)">
                        Zaproś organizacje do platformy SoundBased.
                    </p>
                </div>
                <button type="button" class="invite-btn" @click="openModal">
                    <i class="pi pi-send" />
                    Wyślij zaproszenie
                </button>
            </div>

            <!-- Table -->
            <div class="table-card">
                <table class="inv-table">
                    <thead>
                        <tr>
                            <th>E-mail</th>
                            <th>Status</th>
                            <th>Wygasa</th>
                            <th>Wysłano</th>
                            <th />
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="invitations.data.length === 0">
                            <td colspan="5" class="empty-row">Brak zaproszeń</td>
                        </tr>
                        <tr
                            v-for="inv in invitations.data"
                            :key="inv.id"
                            class="inv-row"
                            @click="$inertia.visit(route('central.invitations.show', { invitation: inv.id }))"
                        >
                            <td class="email-cell">{{ inv.email }}</td>
                            <td>
                                <span
                                    class="status-badge"
                                    :style="{
                                        color: inv.status_color,
                                        borderColor: `${inv.status_color}40`,
                                        background: `${inv.status_color}12`,
                                    }"
                                >
                                    {{ inv.status_label }}
                                </span>
                            </td>
                            <td class="date-cell">{{ formatDate(inv.expires_at) }}</td>
                            <td class="date-cell">{{ formatDate(inv.created_at) }}</td>
                            <td class="action-cell" @click.stop>
                                <button
                                    type="button"
                                    class="del-btn"
                                    :disabled="deleteForm.processing"
                                    @click="deleteInvitation(inv.id)"
                                >
                                    <i class="pi pi-trash" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Send invitation modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div v-if="showModal" class="modal-backdrop" @click.self="showModal = false">
                    <div class="modal-box">
                        <div class="modal-header">
                            <h2>Wyślij zaproszenie</h2>
                            <button type="button" class="modal-close" @click="showModal = false">
                                <i class="pi pi-times" />
                            </button>
                        </div>
                        <form @submit.prevent="sendInvitation">
                            <div class="modal-body">
                                <label class="field-label">Adres e-mail</label>
                                <BaseInput
                                    v-model="sendForm.email"
                                    type="email"
                                    placeholder="kontakt@firma.pl"
                                    prefix-icon="pi pi-envelope"
                                    :error="!!sendForm.errors.email"
                                />
                                <small v-if="sendForm.errors.email" class="field-error">
                                    {{ sendForm.errors.email }}
                                </small>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn-cancel" @click="showModal = false">
                                    Anuluj
                                </button>
                                <button
                                    type="submit"
                                    class="btn-send"
                                    :disabled="sendForm.processing"
                                >
                                    <i
                                        :class="
                                            sendForm.processing
                                                ? 'pi pi-spin pi-spinner'
                                                : 'pi pi-send'
                                        "
                                    />
                                    {{ sendForm.processing ? "Wysyłanie..." : "Wyślij" }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>

<style scoped>
.invite-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.55rem 1.1rem;
    background: linear-gradient(135deg, rgba(217, 70, 239, 0.18), rgba(217, 70, 239, 0.08));
    border: 1px solid rgba(217, 70, 239, 0.35);
    border-radius: 8px;
    color: #d946ef;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.18s;
}
.invite-btn:hover {
    background: linear-gradient(135deg, rgba(217, 70, 239, 0.28), rgba(217, 70, 239, 0.14));
    box-shadow: 0 0 16px rgba(217, 70, 239, 0.2);
}

.table-card {
    background: var(--surface-card);
    border: 1px solid rgba(56, 189, 248, 0.1);
    border-radius: 16px;
    overflow: hidden;
}

.inv-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
}
.inv-table thead tr {
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}
.inv-table th {
    padding: 0.85rem 1.25rem;
    text-align: left;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: rgba(148, 163, 184, 0.5);
}
.inv-table td {
    padding: 0.9rem 1.25rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
    color: var(--text-color);
}
.inv-row {
    cursor: pointer;
    transition: background 0.15s;
}
.inv-row:hover {
    background: rgba(56, 189, 248, 0.04);
}
.email-cell {
    font-weight: 500;
}
.date-cell {
    font-size: 0.8rem;
    color: #64748b;
}
.empty-row {
    text-align: center;
    padding: 3rem !important;
    color: rgba(148, 163, 184, 0.4);
}
.status-badge {
    padding: 0.2rem 0.55rem;
    border: 1px solid;
    border-radius: 5px;
    font-size: 0.72rem;
    font-weight: 600;
    white-space: nowrap;
}
.action-cell {
    text-align: right;
}
.del-btn {
    padding: 0.3rem 0.5rem;
    background: rgba(248, 113, 113, 0.07);
    border: 1px solid rgba(248, 113, 113, 0.2);
    border-radius: 6px;
    color: #f87171;
    cursor: pointer;
    transition: all 0.15s;
    font-size: 0.8rem;
}
.del-btn:hover:not(:disabled) {
    background: rgba(248, 113, 113, 0.16);
}
.del-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Modal */
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.65);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 1rem;
}
.modal-box {
    background: var(--surface-card);
    border: 1px solid rgba(217, 70, 239, 0.18);
    border-radius: 16px;
    width: 100%;
    max-width: 440px;
    box-shadow: 0 24px 64px rgba(0, 0, 0, 0.6);
}
.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1.5rem 0;
}
.modal-header h2 {
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--text-color);
    margin: 0;
}
.modal-close {
    background: none;
    border: none;
    color: #64748b;
    cursor: pointer;
    font-size: 0.9rem;
    padding: 0.25rem;
}
.modal-close:hover {
    color: #94a3b8;
}
.modal-body {
    padding: 1.25rem 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
.field-label {
    font-size: 0.72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
}
.field-error {
    font-size: 0.75rem;
    color: #f87171;
}
.modal-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.6rem;
    padding: 0.75rem 1.5rem 1.25rem;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
}
.btn-cancel {
    padding: 0.45rem 1rem;
    background: rgba(148, 163, 184, 0.08);
    border: 1px solid rgba(148, 163, 184, 0.18);
    border-radius: 8px;
    color: #94a3b8;
    font-size: 0.85rem;
    cursor: pointer;
}
.btn-send {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.45rem 1.1rem;
    background: linear-gradient(135deg, #d946ef, #818cf8);
    border: none;
    border-radius: 8px;
    color: #fff;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.15s;
}
.btn-send:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}

.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.2s ease;
}
.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
</style>
