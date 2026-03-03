<script setup lang="ts">
import { Link, useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { useMenu } from "@/composables/Tenant/useMenu";

defineProps<{ open: boolean }>();

const { menu } = useMenu();

const logoutForm = useForm({});

function logout() {
    logoutForm.post(route("logout"));
}
</script>

<template>
    <aside
        class="transition-all duration-300 flex flex-col"
        :class="open ? 'w-64' : 'w-0 overflow-hidden'"
        style="
            background: var(--surface-card);
            border-right: 1px solid var(--surface-border);
        "
    >
        <div class="p-6 text-lg font-semibold">SoundBase</div>

        <nav class="flex-1 flex flex-col space-y-1 px-3">
            <template v-for="item in menu" :key="item.label">
                <!-- Single link -->
                <Link
                    v-if="!item.children"
                    :href="item.url"
                    class="px-4 py-2 rounded-md text-sm transition-all"
                    :class="
                        route().current(item.name)
                            ? 'bg-yellow-400/10 text-yellow-400'
                            : 'text-white/60 hover:text-white hover:bg-white/5'
                    "
                >
                    {{ item.label }}
                </Link>

                <!-- Group -->
                <div v-else class="mt-2">
                    <div class="px-4 py-2 text-xs uppercase text-white/40">
                        {{ item.label }}
                    </div>

                    <Link
                        v-for="child in item.children"
                        :key="child.label"
                        :href="child.url"
                        class="block px-6 py-2 text-sm rounded-md transition"
                        :class="
                            route().current(child.name)
                                ? 'bg-yellow-400/10 text-yellow-400'
                                : 'text-white/60 hover:text-white hover:bg-white/5'
                        "
                    >
                        {{ child.label }}
                    </Link>
                </div>
            </template>
        </nav>

        <div class="p-4">
            <Button
                @click="logout"
                class="w-full py-2 rounded-md text-sm"
                style="background: var(--surface-hover)"
            >
                Wyloguj się
            </Button>
        </div>
    </aside>
</template>
