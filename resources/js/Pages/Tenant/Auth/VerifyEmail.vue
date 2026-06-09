<script setup lang="ts">
import { useForm, Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { computed } from "vue";
import AuthLayout from "@/layout/Tenant/AuthLayout.vue";

interface Props {
    status?: string | null;
}

const props = defineProps<Props>();

const justSent = computed<boolean>(
    () => props.status === "verification-link-sent",
);

const form = useForm({});

const submit = (): void => {
    form.post(route("verification.send"), { preserveScroll: true });
};
</script>

<template>
    <AuthLayout
        title="Zweryfikuj adres e-mail"
        subtitle="Dziękujemy za rejestrację! Zanim zaczniesz, potwierdź swój adres e-mail klikając w link, który właśnie do Ciebie wysłaliśmy."
    >
        <div
            v-if="justSent"
            class="mb-5 flex items-center gap-2 rounded-[var(--border-radius)] px-4 py-3 text-sm"
            style="
                background: rgba(74, 222, 128, 0.08);
                border: 1px solid rgba(74, 222, 128, 0.25);
                color: #4ade80;
            "
        >
            <i class="pi pi-check-circle text-sm" />
            Nowy link weryfikacyjny został wysłany na Twój adres e-mail.
        </div>

        <div class="flex flex-col gap-4">
            <button
                type="button"
                :disabled="form.processing"
                @click="submit"
                class="flex w-full items-center justify-center gap-2 rounded-[var(--border-radius)] px-4 py-3 text-sm font-semibold transition-all duration-150"
                style="
                    background: linear-gradient(
                        135deg,
                        #0ea5e9 0%,
                        #6366f1 100%
                    );
                    color: #fff;
                    border: none;
                    cursor: pointer;
                    box-shadow: 0 0 24px rgba(14, 165, 233, 0.25);
                "
            >
                <i
                    v-if="form.processing"
                    class="pi pi-spin pi-spinner text-sm"
                />
                {{ form.processing ? "Wysyłanie..." : "Wyślij ponownie link" }}
            </button>

            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="flex items-center justify-center gap-1.5 text-sm transition-colors"
                style="color: var(--text-color-secondary)"
            >
                <i class="pi pi-sign-out text-xs" />
                Wyloguj się
            </Link>
        </div>
    </AuthLayout>
</template>
