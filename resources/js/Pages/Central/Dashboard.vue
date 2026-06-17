<script setup lang="ts">
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import AppLayout from "@/layout/Central/AppLayout.vue";

const adminName = computed<string>(
    () => usePage().props?.auth?.user?.name ?? "Administratorze",
);
</script>

<template>
    <AppLayout>
        <div class="flex flex-col gap-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-100">
                    Witaj, {{ adminName }}
                </h1>
                <p class="mt-1 text-sm text-slate-400">
                    Zarządzaj sklepami i danymi platformy z jednego miejsca.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    :href="route('central.tenants.index')"
                    class="group flex flex-col gap-3 rounded-xl border border-fuchsia-400/10 bg-slate-950/40 p-5 transition-all duration-150 hover:border-fuchsia-400/30"
                >
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-lg"
                        style="
                            background: rgba(168, 85, 247, 0.12);
                            color: #d946ef;
                        "
                    >
                        <i class="pi pi-building text-lg" />
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-slate-100">
                            Sklepy
                        </h2>
                        <p class="mt-0.5 text-sm text-slate-400">
                            Przeglądaj i zarządzaj wszystkimi sklepami
                            (tenantami).
                        </p>
                    </div>
                    <span
                        class="mt-auto inline-flex items-center gap-1 text-sm font-medium text-fuchsia-300"
                    >
                        Przejdź
                        <i
                            class="pi pi-arrow-right text-xs transition-transform group-hover:translate-x-0.5"
                        />
                    </span>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
