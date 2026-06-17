<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { computed } from "vue";

interface LoginForm {
    email: string;
    password: string;
    remember: boolean;
}

const isDev = import.meta.env.DEV;

const form = useForm<LoginForm>({
    email: isDev ? "superadmin@soundbased.pl" : "",
    password: isDev ? "password" : "",
    remember: false,
});

const submit = (): void => {
    form.post(route("central.login.store"), {
        preserveScroll: true,
    });
};

const hasGlobalError = computed<boolean>(() => {
    return (
        Object.keys(form.errors).length > 0 &&
        !form.errors.email &&
        !form.errors.password
    );
});

const labelClass = "mb-2 block text-xs font-semibold uppercase tracking-wider";
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
                        #a855f7 0%,
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
            class="relative w-full max-w-md overflow-hidden rounded-2xl"
            style="
                background: var(--surface-card);
                border: 1px solid rgba(168, 85, 247, 0.14);
                box-shadow:
                    0 30px 80px rgba(0, 0, 0, 0.6),
                    0 0 0 1px rgba(168, 85, 247, 0.06),
                    0 0 60px rgba(168, 85, 247, 0.05);
            "
        >
            <div class="px-6 py-10 sm:px-10">
                <div class="mb-8 flex flex-col items-center gap-3 text-center">
                    <div>
                        <h1
                            class="text-2xl font-bold tracking-tight"
                            style="color: var(--text-color)"
                        >
                            Panel administracyjny
                        </h1>
                        <p
                            class="mt-1 text-sm"
                            style="color: var(--text-color-secondary)"
                        >
                            Strefa superadministratora
                        </p>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Email -->
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
                            placeholder="admin@email.pl"
                            :error="!!form.errors.email"
                        />
                        <p
                            v-if="form.errors.email"
                            class="mt-1.5 text-xs text-red-400"
                        >
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label
                            for="password"
                            :class="labelClass"
                            style="color: var(--text-color-secondary)"
                        >
                            Hasło
                        </label>
                        <BasePassword
                            id="password"
                            v-model="form.password"
                            placeholder="••••••••"
                            prefix-icon="pi pi-lock"
                            :error="!!form.errors.password"
                        />
                        <p
                            v-if="form.errors.password"
                            class="mt-1.5 text-xs text-red-400"
                        >
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Remember me -->
                    <BaseCheckbox
                        v-model="form.remember"
                        label="Zapamiętaj mnie"
                    />

                    <!-- Submit -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex w-full items-center justify-center gap-2 rounded-[var(--border-radius)] px-4 py-3 text-sm font-semibold transition-all duration-150"
                        style="
                            background: linear-gradient(
                                135deg,
                                #a855f7 0%,
                                #6366f1 100%
                            );
                            color: #fff;
                            border: none;
                            cursor: pointer;
                            box-shadow: 0 0 24px rgba(168, 85, 247, 0.25);
                        "
                    >
                        <i
                            v-if="form.processing"
                            class="pi pi-spin pi-spinner text-sm"
                        />
                        {{ form.processing ? "Logowanie..." : "Zaloguj się" }}
                    </button>

                    <!-- Global error -->
                    <div
                        v-if="hasGlobalError"
                        class="flex items-center gap-2 rounded-[var(--border-radius)] px-4 py-3 text-sm"
                        style="
                            background: rgba(248, 113, 113, 0.08);
                            border: 1px solid rgba(248, 113, 113, 0.25);
                            color: #f87171;
                        "
                    >
                        <i class="pi pi-exclamation-circle text-sm" />
                        Nieprawidłowe dane logowania.
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
