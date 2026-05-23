<script setup lang="ts">
import { ref, computed } from "vue";
import { Link, useForm, usePage } from "@inertiajs/vue3";

const companyName = computed<string>(
    () => (usePage().props.tenant as any)?.company_name ?? 'SoundBase',
);
import { route } from "ziggy-js";
import { useMenu } from "@/composables/Tenant/useMenu";

defineProps<{ open: boolean }>();

const { menu } = useMenu();

const logoutForm = useForm({});

function logout(): void {
    logoutForm.post(route("logout"));
}

/* ── Group expand/collapse ── */
const expandedGroups = ref<Record<string, boolean>>({});

function isGroupExpanded(label: string): boolean {
    return expandedGroups.value[label] !== false;
}

function toggleGroup(label: string): void {
    expandedGroups.value[label] = !isGroupExpanded(label);
}

</script>

<template>
    <aside
        class="flex flex-col overflow-hidden transition-all duration-300"
        :class="open ? 'w-64' : 'w-0'"
        style="
            background: linear-gradient(180deg, #0d1424 0%, #080c18 100%);
            border-right: 1px solid rgba(56, 189, 248, 0.08);
            min-height: 100vh;
        "
    >
        <!-- Brand -->
        <div
            class="flex items-center gap-3 px-5 py-5"
            style="border-bottom: 1px solid rgba(56, 189, 248, 0.07)"
        >
            <div
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl"
                style="
                    background: linear-gradient(
                        135deg,
                        #0ea5e9 0%,
                        #6366f1 100%
                    );
                    box-shadow: 0 0 20px rgba(14, 165, 233, 0.35);
                "
            >
                <i class="pi pi-music text-sm text-white" />
            </div>
            <div class="flex flex-col leading-tight">
                <span
                    class="text-sm font-bold tracking-wide"
                    style="color: var(--text-color)"
                    >{{ companyName }}</span
                >
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto px-3 py-4">
            <div class="flex flex-col gap-0.5">
                <template v-for="item in menu" :key="item.label">
                    <!-- Top-level single link (e.g. Panel) -->
                    <Link
                        v-if="!item.children"
                        :href="item.url!"
                        class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition-all duration-150"
                        :class="
                            route().current(item.name)
                                ? 'bg-sky-400/10 text-sky-400 shadow-[inset_3px_0_0_#38bdf8]'
                                : 'text-slate-400/80 hover:bg-sky-400/5 hover:text-slate-300'
                        "
                    >
                        <i
                            :class="[
                                'pi',
                                        item.icon ?? 'pi-circle',
                                'text-base w-4 text-center',
                                route().current(item.name)
                                    ? 'text-sky-400'
                                    : 'text-slate-400/60 group-hover:text-slate-300',
                            ]"
                        />
                        <span class="font-medium">{{ item.label }}</span>
                    </Link>

                    <!-- Group with children -->
                    <div v-else class="mt-3">
                        <!-- Group header -->
                        <button
                            class="flex w-full items-center justify-between px-3 py-1.5 text-xs font-semibold uppercase tracking-wider transition-colors duration-150 hover:opacity-100"
                            style="
                                color: rgba(148, 163, 184, 0.45);
                                background: none;
                                border: none;
                                cursor: pointer;
                            "
                            @click="toggleGroup(item.label)"
                        >
                            <div class="flex items-center gap-2">
                                <i
                                    :class="[
                                        'pi',
                                        item.icon ?? 'pi-folder',
                                        'text-xs',
                                    ]"
                                    :style="{
                                        color: item.color ?? '#94a3b8',
                                    }"
                                />
                                <span>{{ item.label }}</span>
                            </div>
                            <i
                                :class="[
                                    'pi text-[10px] transition-transform duration-200',
                                    isGroupExpanded(item.label)
                                        ? 'pi-chevron-down'
                                        : 'pi-chevron-right',
                                ]"
                            />
                        </button>

                        <!-- Children -->
                        <div
                            v-if="isGroupExpanded(item.label)"
                            class="mt-0.5 flex flex-col gap-0.5"
                        >
                            <Link
                                v-for="child in item.children"
                                :key="child.label"
                                :href="child.url!"
                                class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-all duration-150"
                                :class="
                                    route().current(child.name)
                                        ? 'bg-sky-400/10 text-sky-400 shadow-[inset_3px_0_0_#38bdf8]'
                                        : 'text-slate-400/70 hover:bg-sky-400/5 hover:text-slate-300'
                                "
                            >
                                <i
                                    :class="[
                                        'pi',
                                        child.icon ?? 'pi-circle',
                                        'text-sm w-4 text-center',
                                    ]"
                                    :style="
                                        route().current(child.name)
                                            ? 'color: #38bdf8;'
                                            : `color: ${item.color ?? '#94a3b8'}66;`
                                    "
                                />
                                <span>{{ child.label }}</span>
                            </Link>
                        </div>
                    </div>
                </template>
            </div>
        </nav>

        <!-- Logout -->
        <div class="p-3" style="border-top: 1px solid rgba(56, 189, 248, 0.07)">
            <button
                type="button"
                :disabled="logoutForm.processing"
                class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150"
                style="
                    color: rgba(148, 163, 184, 0.7);
                    background: none;
                    border: none;
                    cursor: pointer;
                "
                @click="logout"
                @mouseover="
                    ($event.currentTarget as HTMLElement).style.background =
                        'rgba(248,113,113,0.08)';
                    ($event.currentTarget as HTMLElement).style.color =
                        '#f87171';
                "
                @mouseleave="
                    ($event.currentTarget as HTMLElement).style.background =
                        'none';
                    ($event.currentTarget as HTMLElement).style.color =
                        'rgba(148,163,184,0.7)';
                "
            >
                <i class="pi pi-sign-out w-4 text-center text-base" />
                <span>{{
                    logoutForm.processing ? "Wylogowywanie..." : "Wyloguj się"
                }}</span>
            </button>
        </div>
    </aside>
</template>
