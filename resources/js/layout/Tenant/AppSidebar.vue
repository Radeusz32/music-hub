<script setup lang="ts">
import { computed } from "vue";
import { usePage, Link, useForm } from "@inertiajs/vue3";

defineProps<{
    open: boolean;
}>();

const page = usePage();

const navigation = computed(() => {
    return (
        page.props.navigation ?? [
            { label: "Dashboard", route: "/" },
            { label: "Profile", route: "/profile" },
            { label: "Orders", route: "/orders" },
            { label: "Products", route: "/products" },
            { label: "Reports", route: "/reports" },
            { label: "Settings", route: "/settings" },
        ]
    );
});

const logoutForm = useForm({});

function logout() {
    logoutForm.post(route("logout"), {
        preserveScroll: true,
    });
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
        <!-- Logo -->
        <div class="p-6 text-lg font-semibold">SoundBase</div>

        <!-- Navigation -->
        <nav class="flex-1 flex flex-col space-y-1 px-3">
            <Link
                v-for="item in navigation"
                :key="item.label"
                :href="item.route"
                class="px-4 py-2 rounded-md text-sm transition-all"
                :class="
                    $page.url.startsWith(item.route)
                        ? 'bg-yellow-400/10 text-yellow-400'
                        : 'text-white/60 hover:text-white hover:bg-white/5'
                "
            >
                {{ item.label }}
            </Link>
        </nav>

        <!-- Logout -->
        <div class="p-4">
            <button
                @click="logout"
                :disabled="logoutForm.processing"
                class="w-full py-2 rounded-md text-sm transition"
                style="background: var(--surface-hover)"
            >
                Sign out
            </button>
        </div>
    </aside>
</template>
