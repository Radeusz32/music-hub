<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import AppLayout from "./AppLayout.vue";

interface Props {
    title: string;
    backHref?: string;
    backLabel?: string;
}

defineProps<Props>();

defineSlots<{
    default(): void;
    actions(): void;
}>();
</script>

<template>
    <AppLayout>
        <div class="flex flex-col gap-6">
            <!-- Page header -->
            <div class="flex items-start justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <Link
                        v-if="backHref"
                        :href="backHref"
                        class="mb-1 flex items-center gap-1.5 text-xs transition-colors duration-150"
                        style="color: rgba(148,163,184,0.6);"
                        @mouseover="($event.currentTarget as HTMLElement).style.color = '#38bdf8';"
                        @mouseleave="($event.currentTarget as HTMLElement).style.color = 'rgba(148,163,184,0.6)';"
                    >
                        <i class="pi pi-arrow-left text-[10px]" />
                        {{ backLabel ?? "Wróć" }}
                    </Link>

                    <h1
                        class="text-2xl font-semibold tracking-tight"
                        style="color: var(--text-color)"
                    >
                        {{ title }}
                    </h1>
                </div>

                <div class="flex shrink-0 items-center gap-2 pt-1">
                    <slot name="actions" />
                </div>
            </div>

            <!-- Content -->
            <slot />
        </div>
    </AppLayout>
</template>
