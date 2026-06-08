<script setup lang="ts">
import { computed, ref } from "vue";
import { usePage, useForm } from "@inertiajs/vue3";

const emit = defineEmits<{
    (e: "toggle-sidebar"): void;
}>();

const page = usePage();

const user = computed(() => page.props?.auth?.user ?? null);

const userInitials = computed<string>(() => {
    const name: string = user.value?.name ?? "";
    return (
        name
            .split(" ")
            .slice(0, 2)
            .map((w: string) => w.charAt(0).toUpperCase())
            .join("") || "U"
    );
});

const searchForm = useForm({ q: "" });

function submitSearch(): void {
    if (!searchForm.q.trim()) return;
    searchForm.get(route("search"), {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
}

const searchFocused = ref(false);
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center justify-between px-5 gap-4"
        style="
            background: linear-gradient(90deg, #0d1424 0%, #080c18 100%);
            border-bottom: 1px solid rgba(56, 189, 248, 0.08);
        "
    >
        <!-- Left: toggle + search -->
        <div class="flex items-center gap-3">
            <!-- Hamburger -->
            <button
                class="flex h-8 w-8 items-center justify-center rounded-lg text-base transition-all duration-150"
                style="
                    color: rgba(148, 163, 184, 0.7);
                    background: none;
                    border: none;
                    cursor: pointer;
                "
                @click="emit('toggle-sidebar')"
                @mouseover="
                    ($event.currentTarget as HTMLElement).style.background =
                        'rgba(56,189,248,0.08)';
                    ($event.currentTarget as HTMLElement).style.color =
                        '#38bdf8';
                "
                @mouseleave="
                    ($event.currentTarget as HTMLElement).style.background =
                        'none';
                    ($event.currentTarget as HTMLElement).style.color =
                        'rgba(148,163,184,0.7)';
                "
            >
                <i class="pi pi-bars" />
            </button>

            <!-- Search -->
            <form @submit.prevent="submitSearch">
                <div
                    class="transition-all duration-200"
                    :style="searchFocused ? 'width: 280px;' : 'width: 220px;'"
                >
                    <BaseInput
                        v-model="searchForm.q"
                        class="w-full"
                        placeholder="Wyszukaj..."
                        prefix-icon="pi pi-search"
                        @focus="searchFocused = true"
                        @blur="searchFocused = false"
                    />
                </div>
            </form>
        </div>

        <!-- Right: notification bell + avatar -->
        <div class="flex items-center gap-2">
            <!-- Bell -->
            <button
                class="relative flex h-8 w-8 items-center justify-center rounded-lg text-base transition-all duration-150"
                style="
                    color: rgba(148, 163, 184, 0.7);
                    background: none;
                    border: none;
                    cursor: pointer;
                "
                @mouseover="
                    ($event.currentTarget as HTMLElement).style.background =
                        'rgba(56,189,248,0.08)';
                    ($event.currentTarget as HTMLElement).style.color =
                        '#38bdf8';
                "
                @mouseleave="
                    ($event.currentTarget as HTMLElement).style.background =
                        'none';
                    ($event.currentTarget as HTMLElement).style.color =
                        'rgba(148,163,184,0.7)';
                "
            >
                <i class="pi pi-bell" />
                <!-- notification dot -->
                <span
                    class="absolute right-1.5 top-1.5 h-1.5 w-1.5 rounded-full"
                    style="
                        background: #38bdf8;
                        box-shadow: 0 0 6px rgba(56, 189, 248, 0.8);
                    "
                />
            </button>

            <!-- Avatar -->
            <div
                class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-lg text-xs font-bold uppercase select-none transition-all duration-150"
                style="
                    background: linear-gradient(
                        135deg,
                        #0ea5e9 0%,
                        #6366f1 100%
                    );
                    color: #fff;
                    box-shadow: 0 0 14px rgba(14, 165, 233, 0.25);
                "
                :title="user?.name ?? 'Użytkownik'"
            >
                {{ userInitials }}
            </div>
        </div>
    </header>
</template>
