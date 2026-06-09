<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import AuthLayout from "@/layout/Tenant/AuthLayout.vue";

interface Props {
    email: string;
    token: string;
}

const props = defineProps<Props>();

interface ResetForm {
    token: string;
    email: string;
    password: string;
    password_confirmation: string;
}

const form = useForm<ResetForm>({
    token: props.token,
    email: props.email,
    password: "",
    password_confirmation: "",
});

const submit = (): void => {
    form.post(route("password.store"), {
        preserveScroll: true,
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};

const labelClass = "mb-2 block text-xs font-semibold uppercase tracking-wider";
</script>

<template>
    <AuthLayout
        title="Ustaw nowe hasło"
        subtitle="Wprowadź nowe hasło do swojego konta."
    >
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
                    :error="!!form.errors.email"
                />
                <p v-if="form.errors.email" class="mt-1.5 text-xs text-red-400">
                    {{ form.errors.email }}
                </p>
            </div>

            <div>
                <label
                    for="password"
                    :class="labelClass"
                    style="color: var(--text-color-secondary)"
                >
                    Nowe hasło
                </label>
                <BasePassword
                    id="password"
                    v-model="form.password"
                    placeholder="••••••••"
                    prefix-icon="pi pi-lock"
                    show-strength
                    :error="!!form.errors.password"
                />
                <p
                    v-if="form.errors.password"
                    class="mt-1.5 text-xs text-red-400"
                >
                    {{ form.errors.password }}
                </p>
            </div>

            <div>
                <label
                    for="password_confirmation"
                    :class="labelClass"
                    style="color: var(--text-color-secondary)"
                >
                    Powtórz hasło
                </label>
                <BasePassword
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    placeholder="••••••••"
                    prefix-icon="pi pi-lock"
                    :error="!!form.errors.password_confirmation"
                />
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
                {{ form.processing ? "Zapisywanie..." : "Zmień hasło" }}
            </button>
        </form>
    </AuthLayout>
</template>
