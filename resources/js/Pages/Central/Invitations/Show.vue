<script setup lang="ts">
import AppLayout from "@/layout/Central/AppLayout.vue";
import { Link, useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { useToast } from "@/composables/useToast";

interface InvitationData {
    id: number;
    uuid: string;
    email: string;
    status: string;
    status_label: string;
    status_color: string;
    company_data: Record<string, string | null> | null;
    owner_data: Record<string, string | null> | null;
    tenant_id: string | null;
    expires_at: string;
    created_at: string;
}

const props = defineProps<{ invitation: InvitationData }>();
const toast = useToast();

/* ── Resend ── */
const resendForm = useForm({});

function resend(): void {
    resendForm.post(
        route("central.invitations.resend", {
            invitation: props.invitation.id,
        }),
        {
            preserveScroll: true,
            onSuccess: () => toast.success("Zaproszenie zostało wysłane ponownie."),
        },
    );
}

/* ── Accept ── */
const acceptForm = useForm({});

function accept(): void {
    acceptForm.post(
        route("central.invitations.accept", { invitation: props.invitation.id }),
        {
            preserveScroll: true,
            onSuccess: () =>
                toast.success(
                    "Organizacja jest tworzona. Może to zająć chwilę.",
                ),
        },
    );
}

/* ── Delete ── */
const deleteForm = useForm({});

function destroy(): void {
    deleteForm.delete(
        route("central.invitations.destroy", {
            invitation: props.invitation.id,
        }),
        { onSuccess: () => toast.success("Zaproszenie usunięte.") },
    );
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
        <div class="mx-auto flex w-full max-w-4xl flex-col gap-6 px-4 py-6">
            <!-- Header -->
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('central.invitations.index')"
                        class="back-btn"
                    >
                        <i class="pi pi-arrow-left" />
                        Powrót
                    </Link>
                    <h1
                        class="text-xl font-bold tracking-tight"
                        style="color: var(--text-color)"
                    >
                        {{ invitation.email }}
                    </h1>
                    <span
                        class="status-badge"
                        :style="{
                            color: invitation.status_color,
                            borderColor: `${invitation.status_color}40`,
                            background: `${invitation.status_color}12`,
                        }"
                    >
                        {{ invitation.status_label }}
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        v-if="invitation.status === 'PENDING'"
                        type="button"
                        class="action-btn action-resend"
                        :disabled="resendForm.processing"
                        @click="resend"
                    >
                        <i
                            :class="
                                resendForm.processing
                                    ? 'pi pi-spin pi-spinner'
                                    : 'pi pi-send'
                            "
                        />
                        {{ resendForm.processing ? "Wysyłanie..." : "Wyślij ponownie" }}
                    </button>
                    <button
                        v-if="invitation.status === 'FILLED'"
                        type="button"
                        class="action-btn action-accept"
                        :disabled="acceptForm.processing"
                        @click="accept"
                    >
                        <i
                            :class="
                                acceptForm.processing
                                    ? 'pi pi-spin pi-spinner'
                                    : 'pi pi-check-circle'
                            "
                        />
                        {{
                            acceptForm.processing
                                ? "Tworzenie..."
                                : "Utwórz organizację"
                        }}
                    </button>
                    <button
                        v-if="invitation.status !== 'ACCEPTED'"
                        type="button"
                        class="action-btn action-delete"
                        :disabled="deleteForm.processing"
                        @click="destroy"
                    >
                        <i class="pi pi-trash" />
                        Usuń
                    </button>
                </div>
            </div>

            <!-- Meta card -->
            <div class="card">
                <h3 class="card-title">Informacje</h3>
                <dl class="meta-grid">
                    <div class="meta-item">
                        <dt>E-mail</dt>
                        <dd>{{ invitation.email }}</dd>
                    </div>
                    <div class="meta-item">
                        <dt>Status</dt>
                        <dd>{{ invitation.status_label }}</dd>
                    </div>
                    <div class="meta-item">
                        <dt>Wysłano</dt>
                        <dd>{{ formatDate(invitation.created_at) }}</dd>
                    </div>
                    <div class="meta-item">
                        <dt>Wygasa</dt>
                        <dd>{{ formatDate(invitation.expires_at) }}</dd>
                    </div>
                    <div v-if="invitation.tenant_id" class="meta-item">
                        <dt>Organizacja</dt>
                        <dd>
                            <Link
                                :href="
                                    route('central.tenants.show', {
                                        tenant: invitation.tenant_id,
                                    })
                                "
                                class="tenant-link"
                            >
                                {{ invitation.tenant_id }}
                                <i class="pi pi-arrow-right text-xs" />
                            </Link>
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Filled data -->
            <template v-if="invitation.company_data || invitation.owner_data">
                <div class="flex gap-5 flex-wrap md:flex-nowrap">
                    <!-- Company data -->
                    <div v-if="invitation.company_data" class="card flex-1">
                        <h3 class="card-title">Dane firmy</h3>
                        <dl class="details-grid">
                            <div
                                v-for="(val, key) in invitation.company_data"
                                :key="key"
                                class="detail"
                            >
                                <dt>{{ key }}</dt>
                                <dd>{{ val || "—" }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Owner data -->
                    <div v-if="invitation.owner_data" class="card flex-1">
                        <h3 class="card-title">Dane właściciela</h3>
                        <dl class="details-grid">
                            <div
                                v-for="(val, key) in invitation.owner_data"
                                :key="key"
                                class="detail"
                            >
                                <dt>{{ key }}</dt>
                                <dd>{{ key === 'password' ? '••••••••' : (val || '—') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </template>

            <div
                v-else
                class="empty-state"
            >
                <i class="pi pi-hourglass text-2xl" style="color: #fb923c" />
                <p>Formularz nie został jeszcze wypełniony.</p>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.back-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.4rem 0.8rem;
    background: rgba(148, 163, 184, 0.07);
    border: 1px solid rgba(148, 163, 184, 0.18);
    border-radius: 8px;
    color: #94a3b8;
    font-size: 0.8rem;
    text-decoration: none;
    transition: all 0.15s;
}
.back-btn:hover {
    color: #cbd5e1;
    background: rgba(148, 163, 184, 0.12);
}
.status-badge {
    padding: 0.22rem 0.6rem;
    border: 1px solid;
    border-radius: 5px;
    font-size: 0.72rem;
    font-weight: 600;
    white-space: nowrap;
}
.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.48rem 1rem;
    border-radius: 8px;
    border: 1px solid;
    font-size: 0.82rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.18s;
}
.action-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
.action-resend {
    background: rgba(217, 70, 239, 0.08);
    border-color: rgba(217, 70, 239, 0.22);
    color: #d946ef;
}
.action-resend:hover:not(:disabled) {
    background: rgba(217, 70, 239, 0.16);
    box-shadow: 0 0 14px rgba(217, 70, 239, 0.18);
}

.action-accept {
    background: rgba(74, 222, 128, 0.1);
    border-color: rgba(74, 222, 128, 0.3);
    color: #4ade80;
}
.action-accept:hover:not(:disabled) {
    background: rgba(74, 222, 128, 0.18);
    box-shadow: 0 0 14px rgba(74, 222, 128, 0.18);
}
.action-delete {
    background: rgba(248, 113, 113, 0.08);
    border-color: rgba(248, 113, 113, 0.22);
    color: #f87171;
}
.action-delete:hover:not(:disabled) {
    background: rgba(248, 113, 113, 0.16);
}
.card {
    background: var(--surface-card);
    border: 1px solid rgba(56, 189, 248, 0.1);
    border-radius: 16px;
    padding: 1.5rem;
}
.card-title {
    font-size: 0.72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: rgba(148, 163, 184, 0.5);
    margin: 0 0 1.1rem;
}
.meta-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 1rem;
    margin: 0;
}
.meta-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}
.meta-item dt {
    font-size: 0.68rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: rgba(148, 163, 184, 0.45);
}
.meta-item dd {
    font-size: 0.875rem;
    color: #e2e8f0;
    margin: 0;
}
.tenant-link {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    color: #38bdf8;
    text-decoration: none;
}
.tenant-link:hover {
    text-decoration: underline;
}
.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 0.9rem;
    margin: 0;
}
.detail {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}
.detail dt {
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: rgba(148, 163, 184, 0.4);
}
.detail dd {
    font-size: 0.82rem;
    color: #e2e8f0;
    margin: 0;
    word-break: break-word;
}
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
    padding: 3rem;
    background: var(--surface-card);
    border: 1px solid rgba(56, 189, 248, 0.08);
    border-radius: 16px;
    color: rgba(148, 163, 184, 0.5);
    font-size: 0.875rem;
}
</style>
