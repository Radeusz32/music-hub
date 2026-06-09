<script setup lang="ts">
import { useForm, usePage, Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { computed, ref } from "vue";

const companyName = computed<string>(
    () => (usePage().props.tenant as any)?.company_name ?? "SoundBase",
);

interface LoginForm {
    email: string;
    password: string;
    remember: boolean;
}

const isDev = import.meta.env.DEV;

const form = useForm<LoginForm>({
    email: isDev ? (import.meta.env.VITE_DEV_LOGIN_EMAIL ?? "") : "",
    password: isDev ? (import.meta.env.VITE_DEV_LOGIN_PASSWORD ?? "") : "",
    remember: isDev
        ? (import.meta.env.VITE_DEV_LOGIN_REMEMBER ?? "false") === "true"
        : false,
});

const submit = (): void => {
    form.post(route("login.store"), {
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
            class="relative w-full max-w-5xl overflow-hidden rounded-2xl"
            style="
                background: var(--surface-card);
                border: 1px solid rgba(56, 189, 248, 0.12);
                box-shadow:
                    0 30px 80px rgba(0, 0, 0, 0.6),
                    0 0 0 1px rgba(56, 189, 248, 0.06),
                    0 0 60px rgba(56, 189, 248, 0.04);
            "
        >
            <div class="grid min-h-[620px] md:grid-cols-2">
                <!-- Left panel -->
                <div
                    class="relative hidden flex-col items-center justify-center overflow-hidden px-12 py-16 text-center md:flex"
                    style="
                        background: linear-gradient(
                            135deg,
                            #0c1a35 0%,
                            #070b18 100%
                        );
                    "
                >
                    <!-- Grid lines decoration -->
                    <div
                        class="pointer-events-none absolute inset-0 opacity-5"
                        style="
                            background-image:
                                linear-gradient(
                                    rgba(56, 189, 248, 0.5) 1px,
                                    transparent 1px
                                ),
                                linear-gradient(
                                    90deg,
                                    rgba(56, 189, 248, 0.5) 1px,
                                    transparent 1px
                                );
                            background-size: 40px 40px;
                        "
                    />

                    <!-- Glow orb -->
                    <div
                        class="absolute top-1/2 left-1/2 h-80 w-80 -translate-x-1/2 -translate-y-1/2 rounded-full opacity-20 blur-3xl"
                        style="
                            background: radial-gradient(
                                circle,
                                #38bdf8 0%,
                                transparent 60%
                            );
                        "
                    />

                    <!-- Content -->
                    <div class="relative z-10 flex flex-col items-center gap-6">
                        <div>
                            <h2
                                class="mb-3 text-4xl font-bold tracking-tight"
                                style="color: var(--text-color)"
                            >
                                Witaj, {{ companyName }}
                            </h2>
                            <p
                                class="max-w-xs text-sm leading-7"
                                style="color: rgba(148, 163, 184, 0.75)"
                            >
                                Kompleksowy system zarządzania dla sklepów z
                                muzyką. Magazyn, giełdy, analityka — wszystko w
                                jednym miejscu.
                            </p>
                        </div>

                        <!-- Feature pills -->
                        <div class="flex flex-col gap-2 w-full max-w-xs">
                            <div
                                v-for="feat in [
                                    'Zarządzanie magazynem',
                                    'Integracje API',
                                    'Analityka sprzedaży',
                                ]"
                                :key="feat"
                                class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-xs text-left"
                                style="
                                    background: rgba(56, 189, 248, 0.07);
                                    border: 1px solid rgba(56, 189, 248, 0.12);
                                    color: rgba(148, 163, 184, 0.8);
                                "
                            >
                                <i
                                    class="pi pi-check-circle text-xs"
                                    style="color: #38bdf8"
                                />
                                {{ feat }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: form -->
                <div
                    class="flex items-center px-6 py-10 sm:px-10 md:px-12"
                    style="background: var(--surface-card)"
                >
                    <div class="mx-auto w-full max-w-md">
                        <h1
                            class="mb-2 text-3xl font-bold tracking-tight"
                            style="color: var(--text-color)"
                        >
                            Zaloguj się
                        </h1>
                        <p
                            class="mb-8 text-sm"
                            style="color: var(--text-color-secondary)"
                        >
                            Wprowadź swoje dane logowania, aby uzyskać dostęp.
                        </p>

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
                                    placeholder="twoj@email.pl"
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

                            <!-- Remember me + forgot password -->
                            <div class="flex items-center justify-between">
                                <BaseCheckbox
                                    v-model="form.remember"
                                    label="Zapamiętaj mnie"
                                />
                                <Link
                                    :href="route('password.request')"
                                    class="text-sm transition-colors"
                                    style="color: #38bdf8"
                                >
                                    Nie pamiętasz hasła?
                                </Link>
                            </div>

                            <!-- Submit -->
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
                                    box-shadow: 0 0 24px
                                        rgba(14, 165, 233, 0.25);
                                "
                            >
                                <i
                                    v-if="form.processing"
                                    class="pi pi-spin pi-spinner text-sm"
                                />
                                {{
                                    form.processing
                                        ? "Logowanie..."
                                        : "Zaloguj się"
                                }}
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
        </div>
    </div>
</template>
