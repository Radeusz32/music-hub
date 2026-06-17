<script setup lang="ts">
import { computed } from "vue";

const props = defineProps<{ reason: string }>();

const message = computed(() => {
    switch (props.reason) {
        case "expired":
            return "Termin ważności zaproszenia minął. Skontaktuj się z administratorem, aby otrzymać nowe.";
        case "FILLED":
            return "Formularz dla tego zaproszenia został już wypełniony. Oczekuj na aktywację konta.";
        case "ACCEPTED":
            return "To zaproszenie zostało już zrealizowane — organizacja jest aktywna.";
        default:
            return "Zaproszenie nie istnieje lub zostało usunięte.";
    }
});
</script>

<template>
    <div
        class="flex min-h-screen items-center justify-center px-4"
        style="background: var(--surface-ground)"
    >
        <div class="pointer-events-none fixed inset-0 overflow-hidden" aria-hidden="true">
            <div
                class="absolute -left-64 -top-64 h-[600px] w-[600px] rounded-full opacity-10"
                style="background: radial-gradient(circle, #f87171 0%, transparent 70%)"
            />
        </div>

        <div
            class="relative w-full max-w-lg rounded-2xl text-center"
            style="
                background: var(--surface-card);
                border: 1px solid rgba(248,113,113,0.18);
                box-shadow: 0 30px 80px rgba(0,0,0,0.6);
            "
        >
            <div class="flex flex-col items-center gap-5 px-8 py-12 sm:px-12">
                <div
                    class="flex h-16 w-16 items-center justify-center rounded-2xl"
                    style="background: rgba(248,113,113,0.1); border: 1px solid rgba(248,113,113,0.25)"
                >
                    <i class="pi pi-clock text-3xl" style="color: #f87171" />
                </div>

                <div class="flex flex-col gap-2">
                    <h1 class="text-2xl font-bold tracking-tight" style="color: var(--text-color)">
                        Zaproszenie niedostępne
                    </h1>
                    <p class="text-sm leading-7" style="color: var(--text-color-secondary)">
                        {{ message }}
                    </p>
                </div>

                <div
                    class="mt-2 flex items-center gap-2.5 rounded-lg px-4 py-3 text-sm"
                    style="
                        background: rgba(56,189,248,0.06);
                        border: 1px solid rgba(56,189,248,0.14);
                        color: rgba(148,163,184,0.85);
                    "
                >
                    <i class="pi pi-info-circle" style="color: #38bdf8" />
                    Skontaktuj się z administratorem platformy.
                </div>
            </div>
        </div>
    </div>
</template>
