<script setup lang="ts">
import AppLayout from "@/layout/Central/AppLayout.vue";
import IndexLayout from "@/layout/Tenant/IndexLayout.vue";
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { computed, ref } from "vue";
import { useToast } from "@/composables/useToast";

interface FeatureTenant {
    id: string;
    company_name: string | null;
    is_active: boolean;
    features: string[];
}

interface FeatureOption {
    value: string;
    label: string;
}

const props = defineProps<{
    tenants: FeatureTenant[];
    features: FeatureOption[];
}>();

const toast = useToast();

/* ── Client-side search ── */
const search = ref("");

const filteredTenants = computed<FeatureTenant[]>(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) {
        return props.tenants;
    }
    return props.tenants.filter((t) =>
        (t.company_name ?? "").toLowerCase().includes(q),
    );
});

/* ── In-flight guard (prevents double toggles) ── */
const busyKey = ref<string | null>(null);

function isEnabled(tenant: FeatureTenant, feature: string): boolean {
    return tenant.features.includes(feature);
}

function toggleFeature(tenant: FeatureTenant, feature: FeatureOption): void {
    const key = `${tenant.id}:${feature.value}`;
    if (busyKey.value) {
        return;
    }
    busyKey.value = key;

    const willEnable = !isEnabled(tenant, feature.value);
    const tenantName = tenant.company_name ?? "sklepu";

    router.post(
        route("central.features.toggle"),
        { tenant_id: tenant.id, feature: feature.value },
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                toast.success(
                    `Moduł „${feature.label}” ${willEnable ? "włączony" : "wyłączony"} dla ${tenantName}`,
                );
            },
            onFinish: () => {
                busyKey.value = null;
            },
        },
    );
}

function toggleActive(tenant: FeatureTenant): void {
    const key = `active:${tenant.id}`;
    if (busyKey.value) {
        return;
    }
    busyKey.value = key;

    const willActivate = !tenant.is_active;
    const tenantName = tenant.company_name ?? "";

    router.post(
        route("central.tenants.toggle-active", { tenant: tenant.id }),
        {},
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                toast.success(
                    `Sklep ${tenantName} ${willActivate ? "aktywowany" : "dezaktywowany"}`,
                );
            },
            onFinish: () => {
                busyKey.value = null;
            },
        },
    );
}
</script>

<template>
    <AppLayout>
        <IndexLayout
            title="Moduły"
            subtitle="Włączaj i wyłączaj moduły dla poszczególnych sklepów"
            icon="pi pi-sliders-h"
            icon-color="#d946ef"
        >
            <template #toolbar>
                <div class="w-full max-w-xs sm:w-72">
                    <BaseInput
                        v-model="search"
                        placeholder="Szukaj sklepu..."
                        prefix-icon="pi pi-search"
                    />
                </div>
            </template>

            <div class="flex flex-col gap-4">
                <article
                    v-for="tenant in filteredTenants"
                    :key="tenant.id"
                    class="tenant-card"
                    :class="{ inactive: !tenant.is_active }"
                >
                    <!-- Header -->
                    <header class="card-header">
                        <div class="flex items-center gap-3">
                            <span class="tenant-title">{{
                                tenant.company_name ?? "-"
                            }}</span>
                            <span
                                class="status-badge"
                                :class="
                                    tenant.is_active ? 'active' : 'deactivated'
                                "
                            >
                                <span class="status-dot" />
                                {{
                                    tenant.is_active ? "Aktywny" : "Nieaktywny"
                                }}
                            </span>
                        </div>

                        <button
                            type="button"
                            class="toggle-active-btn"
                            :class="
                                tenant.is_active ? 'deactivate' : 'activate'
                            "
                            :disabled="busyKey === `active:${tenant.id}`"
                            @click="toggleActive(tenant)"
                        >
                            <i
                                :class="
                                    tenant.is_active
                                        ? 'pi pi-ban'
                                        : 'pi pi-check-circle'
                                "
                            />
                            {{ tenant.is_active ? "Dezaktywuj" : "Aktywuj" }}
                        </button>
                    </header>

                    <!-- Module switches -->
                    <div class="module-grid">
                        <button
                            v-for="feature in features"
                            :key="feature.value"
                            type="button"
                            class="module-toggle"
                            :class="{
                                on: isEnabled(tenant, feature.value),
                            }"
                            :disabled="
                                busyKey === `${tenant.id}:${feature.value}`
                            "
                            @click="toggleFeature(tenant, feature)"
                        >
                            <span class="switch">
                                <span class="knob" />
                            </span>
                            <span class="module-label">{{
                                feature.label
                            }}</span>
                        </button>
                    </div>
                </article>

                <div
                    v-if="!filteredTenants.length"
                    class="py-12 text-center text-sm text-slate-500"
                >
                    Brak sklepów spełniających kryteria.
                </div>
            </div>
        </IndexLayout>
    </AppLayout>
</template>

<style scoped>
.tenant-card {
    display: flex;
    flex-direction: column;
    gap: 1.1rem;
    padding: 1.25rem 1.5rem;
    background: var(--surface-card);
    border: 1px solid rgba(168, 85, 247, 0.1);
    border-radius: 14px;
    transition: all 0.2s;
}

.tenant-card.inactive {
    opacity: 0.72;
    border-color: rgba(248, 113, 113, 0.18);
}

.card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    padding-bottom: 1rem;
    border-bottom: 1px solid rgba(168, 85, 247, 0.08);
}

.tenant-title {
    font-size: 1.05rem;
    font-weight: 600;
    color: #e2e8f0;
}

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

.toggle-active-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.45rem 0.95rem;
    border-radius: 8px;
    font-size: 0.82rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.toggle-active-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.toggle-active-btn.deactivate {
    background: rgba(248, 113, 113, 0.08);
    border: 1px solid rgba(248, 113, 113, 0.25);
    color: #f87171;
}
.toggle-active-btn.deactivate:hover:not(:disabled) {
    background: rgba(248, 113, 113, 0.16);
}

.toggle-active-btn.activate {
    background: rgba(74, 222, 128, 0.1);
    border: 1px solid rgba(74, 222, 128, 0.25);
    color: #4ade80;
}
.toggle-active-btn.activate:hover:not(:disabled) {
    background: rgba(74, 222, 128, 0.18);
}

.module-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 0.65rem;
}

.module-toggle {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    padding: 0.6rem 0.85rem;
    background: rgba(148, 163, 184, 0.04);
    border: 1px solid rgba(148, 163, 184, 0.12);
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.18s;
    text-align: left;
}

.module-toggle:hover:not(:disabled) {
    border-color: rgba(168, 85, 247, 0.3);
}

.module-toggle:disabled {
    opacity: 0.6;
    cursor: wait;
}

.module-toggle.on {
    background: rgba(168, 85, 247, 0.1);
    border-color: rgba(168, 85, 247, 0.35);
}

.module-label {
    font-size: 0.85rem;
    font-weight: 500;
    color: #cbd5e1;
}

.switch {
    position: relative;
    display: inline-flex;
    flex-shrink: 0;
    width: 36px;
    height: 20px;
    border-radius: 999px;
    background: rgba(148, 163, 184, 0.25);
    transition: background 0.18s;
}

.module-toggle.on .switch {
    background: linear-gradient(135deg, #a855f7, #6366f1);
}

.knob {
    position: absolute;
    top: 2px;
    left: 2px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #fff;
    transition: transform 0.18s;
}

.module-toggle.on .knob {
    transform: translateX(16px);
}
</style>
