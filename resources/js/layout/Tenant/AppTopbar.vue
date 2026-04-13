<script setup lang="ts">
import { computed } from "vue";
import { usePage, useForm } from "@inertiajs/vue3";

const emit = defineEmits<{
    (e: "toggle-sidebar"): void;
}>();

const page = usePage();

const user = computed(() => page.props?.auth?.user ?? null);

const searchForm = useForm({
    q: "",
});

function submitSearch() {
    if (!searchForm.q) return;

    searchForm.get(route("search"), {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
}
</script>

<template>
    <header
        class="h-16 flex items-center justify-between px-6"
        style="
            background: var(--surface-card);
            border-bottom: 1px solid var(--surface-border);
        "
    >
        <div class="flex items-center gap-4">
            <button @click="emit('toggle-sidebar')" class="text-xl">☰</button>

            <form @submit.prevent="submitSearch">
                <input
                    v-model="searchForm.q"
                    type="text"
                    placeholder="Wyszukaj..."
                    class="px-4 py-2 text-sm rounded-md focus:outline-none"
                    style="
                        background: var(--surface-ground);
                        border: 1px solid var(--surface-border);
                    "
                />
            </form>
        </div>

        <div
            class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-medium"
            style="background: var(--surface-hover)"
        >
            {{ user?.name?.charAt(0) ?? "U" }}
        </div>
    </header>
</template>
