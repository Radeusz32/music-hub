<script setup lang="ts">
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";

interface Props {
    title: string;
    subtitle?: string;
}

defineProps<Props>();

const companyName = computed<string>(
    () => (usePage().props.tenant as any)?.company_name ?? "MusicHub",
);
</script>

<template>
    <div
        class="flex min-h-screen items-center justify-center px-4"
        style="background: var(--surface-ground); color: var(--text-color)"
    >
        <!-- Ambient glow -->
        <div
            class="pointer-events-none fixed inset-0 overflow-hidden"
            aria-hidden="true"
        >
            <div
                class="absolute -left-64 -top-64 h-[600px] w-[600px] rounded-full opacity-10"
                style="
                    background: radial-gradient(
                        circle,
                        #0ea5e9 0%,
                        transparent 70%
                    );
                "
            />
            <div
                class="absolute -bottom-64 -right-64 h-[500px] w-[500px] rounded-full opacity-8"
                style="
                    background: radial-gradient(
                        circle,
                        #6366f1 0%,
                        transparent 70%
                    );
                "
            />
        </div>

        <div
            class="relative w-full max-w-md overflow-hidden rounded-2xl px-6 py-10 sm:px-10"
            style="
                background: var(--surface-card);
                border: 1px solid rgba(56, 189, 248, 0.12);
                box-shadow:
                    0 30px 80px rgba(0, 0, 0, 0.6),
                    0 0 0 1px rgba(56, 189, 248, 0.06),
                    0 0 60px rgba(56, 189, 248, 0.04);
            "
        >
            <p
                class="mb-1 text-xs font-semibold uppercase tracking-wider"
                style="color: #38bdf8"
            >
                {{ companyName }}
            </p>
            <h1
                class="mb-2 text-2xl font-bold tracking-tight"
                style="color: var(--text-color)"
            >
                {{ title }}
            </h1>
            <p
                v-if="subtitle"
                class="mb-8 text-sm"
                style="color: var(--text-color-secondary)"
            >
                {{ subtitle }}
            </p>

            <slot />
        </div>
    </div>
</template>
