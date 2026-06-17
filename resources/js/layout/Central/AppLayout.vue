<script setup lang="ts">
import { computed, ref } from "vue";
import { Link, useForm, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import AppToast from "@/Components/AppToast.vue";

const sidebarOpen = ref(true);

function toggleSidebar(): void {
    sidebarOpen.value = !sidebarOpen.value;
}

const page = usePage();

const admin = computed(() => page.props?.auth?.user ?? null);

const adminInitials = computed<string>(() => {
    const name: string = admin.value?.name ?? "";
    return (
        name
            .split(" ")
            .slice(0, 2)
            .map((w: string) => w.charAt(0).toUpperCase())
            .join("") || "A"
    );
});

const menu = [
    { label: "Pulpit", name: "central.dashboard", icon: "pi-th-large" },
    { label: "Sklepy", name: "central.tenants.index", icon: "pi-building" },
    { label: "Zaproszenia", name: "central.invitations.index", icon: "pi-send" },
    { label: "Moduły", name: "central.features.index", icon: "pi-sliders-h" },
];

const logoutForm = useForm({});

function logout(): void {
    logoutForm.post(route("central.logout"));
}
</script>

<template>
    <div
        class="flex min-h-screen font-sans"
        style="background: var(--surface-ground); color: var(--text-color)"
    >
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-30 bg-black/50 lg:hidden"
            @click="toggleSidebar"
        />

        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-40 flex flex-col overflow-hidden transition-all duration-300 lg:relative lg:inset-auto lg:z-auto"
            :class="sidebarOpen ? 'w-64' : 'w-0'"
            style="
                background: linear-gradient(180deg, #1a0d24 0%, #0c0818 100%);
                border-right: 1px solid rgba(168, 85, 247, 0.1);
                min-height: 100vh;
            "
        >
            <!-- Brand -->
            <div
                class="flex items-center gap-2 px-5 py-5"
                style="border-bottom: 1px solid rgba(168, 85, 247, 0.08)"
            >
                <i class="pi pi-shield text-fuchsia-400" />
                <span
                    class="text-sm font-bold tracking-wide"
                    style="color: var(--text-color)"
                >
                    Panel administracyjny
                </span>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-3 py-4">
                <div class="flex flex-col gap-0.5">
                    <Link
                        v-for="item in menu"
                        :key="item.name"
                        :href="route(item.name)"
                        class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition-all duration-150"
                        :class="
                            route().current(item.name)
                                ? 'bg-fuchsia-400/10 text-fuchsia-300 shadow-[inset_3px_0_0_#d946ef]'
                                : 'text-slate-400/80 hover:bg-fuchsia-400/5 hover:text-slate-300'
                        "
                    >
                        <i
                            :class="[
                                'pi',
                                item.icon,
                                'text-base w-4 text-center',
                                route().current(item.name)
                                    ? 'text-fuchsia-300'
                                    : 'text-slate-400/60 group-hover:text-slate-300',
                            ]"
                        />
                        <span class="font-medium">{{ item.label }}</span>
                    </Link>
                </div>
            </nav>

            <!-- Logout -->
            <div
                class="p-3"
                style="border-top: 1px solid rgba(168, 85, 247, 0.08)"
            >
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
                        logoutForm.processing
                            ? "Wylogowywanie..."
                            : "Wyloguj się"
                    }}</span>
                </button>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <!-- Topbar -->
            <header
                class="flex h-16 shrink-0 items-center justify-between gap-4 px-5"
                style="
                    background: linear-gradient(
                        90deg,
                        #1a0d24 0%,
                        #0c0818 100%
                    );
                    border-bottom: 1px solid rgba(168, 85, 247, 0.1);
                "
            >
                <button
                    class="flex h-8 w-8 items-center justify-center rounded-lg text-base"
                    style="
                        color: rgba(148, 163, 184, 0.7);
                        background: none;
                        border: none;
                        cursor: pointer;
                    "
                    @click="toggleSidebar"
                >
                    <i class="pi pi-bars" />
                </button>

                <div class="flex items-center gap-3">
                    <div class="hidden flex-col items-end sm:flex">
                        <span class="text-sm font-medium text-slate-200">{{
                            admin?.name ?? "Administrator"
                        }}</span>
                        <span class="text-xs text-slate-500">{{
                            admin?.email
                        }}</span>
                    </div>
                    <div
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-xs font-bold uppercase select-none"
                        style="
                            background: linear-gradient(
                                135deg,
                                #a855f7 0%,
                                #6366f1 100%
                            );
                            color: #fff;
                            box-shadow: 0 0 14px rgba(168, 85, 247, 0.25);
                        "
                        :title="admin?.name ?? 'Administrator'"
                    >
                        {{ adminInitials }}
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-auto p-6">
                <slot />
            </main>
        </div>

        <Toast position="top-right" />
        <ConfirmDialog />
        <AppToast />
    </div>
</template>
