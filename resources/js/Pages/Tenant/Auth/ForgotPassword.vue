<script setup lang="ts">
import { useForm, usePage, Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { computed } from "vue";
import AuthLayout from "@/layout/Tenant/AuthLayout.vue";

const status = computed<string | null>(
    () => usePage().props.flash?.success ?? null,
);

const form = useForm<{ email: string }>({ email: "" });

const submit = (): void => {
    form.post(route("password.email"), { preserveScroll: true });
};

const labelClass = "mb-2 block text-xs font-semibold uppercase tracking-wider";
</script>

<template>
    <AuthLayout
        title="Nie pamiętasz hasła?"
        subtitle="Podaj adres e-mail powiązany z kontem, a wyślemy Ci link do zresetowania hasła."
    >
        <div
            v-if="status"
            class="mb-5 flex items-center gap-2 rounded-[var(--border-radius)] px-4 py-3 text-sm"
            style="
                background: rgba(74, 222, 128, 0.08);
                border: 1px solid rgba(74, 222, 128, 0.25);
                color: #4ade80;
            "
        >
            <i class="pi pi-check-circle text-sm" />
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <label
                    for="email"
                    :class="labelClass"
                    style="color: var(--text-color-secondary)"
                >
                    Adres e-mail
                </label>
                <BaseInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    prefix-icon="pi pi-envelope"
                    placeholder="twoj@email.pl"
                    :error="!!form.errors.email"
                />
                <p v-if="form.errors.email" class="mt-1.5 text-xs text-red-400">
                    {{ form.errors.email }}
                </p>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
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
                {{
                    form.processing ? "Wysyłanie..." : "Wyślij link resetujący"
                }}
            </button>

            <Link
                :href="route('login')"
                class="flex items-center justify-center gap-1.5 text-sm transition-colors"
                style="color: var(--text-color-secondary)"
            >
                <i class="pi pi-arrow-left text-xs" />
                Powrót do logowania
            </Link>
        </form>
    </AuthLayout>
</template>
