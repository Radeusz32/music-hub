<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import { computed } from "vue";

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

const inputClass =
    "w-full rounded-[var(--border-radius)] border border-[var(--surface-border)] bg-[var(--surface-ground)] px-4 py-3 text-[var(--text-color)] transition outline-none placeholder:text-[var(--text-color-secondary)] focus:border-[var(--primary-color)] focus:ring-2 focus:ring-[var(--primary-color)]/20";

const labelClass =
    "mb-2 block text-sm font-medium text-[var(--text-color-secondary)]";
</script>

<template>
    <div
        class="flex min-h-screen items-center justify-center bg-[var(--surface-ground)] px-4 text-[var(--text-color)]"
    >
        <Card
            class="w-full max-w-6xl overflow-hidden rounded-2xl border border-[var(--surface-border)] bg-[var(--surface-card)] shadow-2xl"
        >
            <template #content>
                <div class="grid min-h-[620px] md:grid-cols-2">
                    <div
                        class="hidden md:flex flex-col items-center justify-center bg-gradient-to-br from-[var(--primary-color)] to-amber-400 px-12 py-16 text-center text-[var(--primary-color-text)]"
                    >
                        <h2 class="mb-4 text-4xl font-semibold tracking-tight">
                            Welcome back to SoundBase
                        </h2>

                        <p class="max-w-sm text-base leading-7 opacity-90">
                            Manage your tenant, users and features from a
                            single, scalable SaaS platform.
                        </p>
                    </div>

                    <div class="flex items-center px-6 py-10 sm:px-10 md:px-12">
                        <div class="mx-auto w-full max-w-md">
                            <h1
                                class="mb-2 text-4xl font-semibold tracking-tight"
                            >
                                Login
                            </h1>

                            <p
                                class="mb-8 text-sm text-[var(--text-color-secondary)]"
                            >
                                Enter your credentials to access your tenant.
                            </p>

                            <form @submit.prevent="submit" class="space-y-6">
                                <div>
                                    <label for="email" :class="labelClass">
                                        Email
                                    </label>

                                    <InputText
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        autocomplete="username"
                                        :class="inputClass"
                                    />

                                    <p
                                        v-if="form.errors.email"
                                        class="mt-2 text-sm text-red-400"
                                    >
                                        {{ form.errors.email }}
                                    </p>
                                </div>

                                <div>
                                    <label for="password" :class="labelClass">
                                        Password
                                    </label>

                                    <Password
                                        id="password"
                                        v-model="form.password"
                                        :feedback="false"
                                        toggleMask
                                        autocomplete="current-password"
                                        class="w-full"
                                        :pt="{
                                            root: { class: 'relative w-full' },
                                            pcInputText: {
                                                root: { class: inputClass },
                                            },
                                            inputIconContainer: {
                                                class: 'absolute inset-y-0 right-0 flex items-center pr-4 text-[var(--text-color-secondary)]',
                                            },
                                        }"
                                    />

                                    <p
                                        v-if="form.errors.password"
                                        class="mt-2 text-sm text-red-400"
                                    >
                                        {{ form.errors.password }}
                                    </p>
                                </div>

                                <div class="flex items-center gap-3">
                                    <Checkbox
                                        inputId="remember"
                                        v-model="form.remember"
                                        binary
                                        :pt="{
                                            root: {
                                                class: 'flex items-center',
                                            },
                                            box: {
                                                class: [
                                                    'flex h-4 w-4 items-center justify-center rounded border transition',
                                                    form.remember
                                                        ? 'border-[var(--primary-color)] bg-[var(--primary-color)] text-[var(--primary-color-text)]'
                                                        : 'border-[var(--surface-border)] bg-[var(--surface-ground)] text-transparent',
                                                ],
                                            },
                                            icon: {
                                                class: 'text-[10px] font-bold',
                                            },
                                        }"
                                    />

                                    <label
                                        for="remember"
                                        class="cursor-pointer text-sm text-[var(--text-color-secondary)]"
                                    >
                                        Remember me
                                    </label>
                                </div>

                                <Button
                                    type="submit"
                                    label="Login"
                                    :loading="form.processing"
                                    class="w-full rounded-[var(--border-radius)] bg-[var(--primary-color)] px-4 py-3 font-semibold text-[var(--primary-color-text)] transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60"
                                />

                                <div
                                    v-if="hasGlobalError"
                                    class="rounded-[var(--border-radius)] border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300"
                                >
                                    Invalid credentials.
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </template>
        </Card>
    </div>
</template>
