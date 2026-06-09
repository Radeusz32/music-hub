<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import AppLayout from "@/layout/Tenant/AppLayout.vue";
import { useToast } from "@/composables/useToast";

interface ProfileData {
    first_name: string;
    last_name: string;
    email: string;
    phone: string | null;
    street: string | null;
    building_number: string | null;
    apartment_number: string | null;
    postal_code: string | null;
    city: string | null;
    pesel: string | null;
}

interface Props {
    profile: ProfileData;
}

const props = defineProps<Props>();

const toast = useToast();

const profileForm = useForm({
    first_name: props.profile.first_name ?? "",
    last_name: props.profile.last_name ?? "",
    email: props.profile.email ?? "",
    phone: props.profile.phone ?? "",
    street: props.profile.street ?? "",
    building_number: props.profile.building_number ?? "",
    apartment_number: props.profile.apartment_number ?? "",
    postal_code: props.profile.postal_code ?? "",
    city: props.profile.city ?? "",
    pesel: props.profile.pesel ?? "",
});

const submitProfile = (): void => {
    profileForm.put(route("tenant.settings.profile.update"), {
        preserveScroll: true,
        onSuccess: () => toast.success("Dane profilu zostały zaktualizowane."),
    });
};

interface PasswordForm {
    current_password: string;
    password: string;
    password_confirmation: string;
}

const passwordForm = useForm<PasswordForm>({
    current_password: "",
    password: "",
    password_confirmation: "",
});

const submitPassword = (): void => {
    passwordForm.put(route("tenant.settings.password.update"), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success("Hasło zostało zmienione.");
            passwordForm.reset();
        },
        onError: () => passwordForm.reset("password", "password_confirmation"),
    });
};

const labelClass = "text-xs font-medium uppercase tracking-wide text-slate-400";
const cardStyle =
    "background: var(--surface-card); border: 1px solid rgba(56, 189, 248, 0.12);";
const sectionClass =
    "flex flex-1 flex-col gap-3.5 rounded-xl border border-sky-400/10 bg-slate-950/40 p-4";
const sectionTitleClass =
    "border-b border-sky-400/10 pb-2 text-xs font-semibold uppercase tracking-wider text-slate-400";
const submitButtonStyle =
    "background: linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%); color: #fff; border: none; cursor: pointer; box-shadow: 0 0 24px rgba(14, 165, 233, 0.25);";
</script>

<template>
    <AppLayout>
        <div class="mx-auto flex w-full max-w-5xl flex-col gap-6">
            <div>
                <h1
                    class="text-2xl font-bold tracking-tight"
                    style="color: var(--text-color)"
                >
                    Profil
                </h1>
                <p class="text-sm" style="color: var(--text-color-secondary)">
                    Zarządzaj swoimi danymi, adresem i bezpieczeństwem.
                </p>
            </div>

            <!-- Profile data -->
            <div class="rounded-2xl p-6" :style="cardStyle">
                <h2
                    class="mb-1 text-lg font-semibold"
                    style="color: var(--text-color)"
                >
                    Dane profilu
                </h2>
                <p
                    class="mb-6 text-sm"
                    style="color: var(--text-color-secondary)"
                >
                    Twoje dane osobowe i adresowe. PESEL i adres są szyfrowane.
                </p>

                <form
                    id="profile-form"
                    class="flex w-full flex-col gap-5 md:flex-row"
                    @submit.prevent="submitProfile"
                >
                    <!-- Personal data -->
                    <section :class="sectionClass">
                        <h3 :class="sectionTitleClass">Dane osobowe</h3>

                        <div class="flex flex-col gap-1.5">
                            <label :class="labelClass">
                                Imię <span class="text-red-400">*</span>
                            </label>
                            <BaseInput
                                v-model="profileForm.first_name"
                                placeholder="Np. Anna"
                                :error="!!profileForm.errors.first_name"
                            />
                            <small
                                v-if="profileForm.errors.first_name"
                                class="text-xs text-red-400"
                            >
                                {{ profileForm.errors.first_name }}
                            </small>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label :class="labelClass">
                                Nazwisko <span class="text-red-400">*</span>
                            </label>
                            <BaseInput
                                v-model="profileForm.last_name"
                                placeholder="Np. Kowalska"
                                :error="!!profileForm.errors.last_name"
                            />
                            <small
                                v-if="profileForm.errors.last_name"
                                class="text-xs text-red-400"
                            >
                                {{ profileForm.errors.last_name }}
                            </small>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label :class="labelClass">
                                E-mail <span class="text-red-400">*</span>
                            </label>
                            <BaseInput
                                v-model="profileForm.email"
                                type="email"
                                placeholder="Np. anna@sklep.pl"
                                prefix-icon="pi pi-envelope"
                                :error="!!profileForm.errors.email"
                            />
                            <small
                                v-if="profileForm.errors.email"
                                class="text-xs text-red-400"
                            >
                                {{ profileForm.errors.email }}
                            </small>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label :class="labelClass">Telefon</label>
                            <BaseMaskedInput
                                v-model="profileForm.phone"
                                mask="### ### ###"
                                placeholder="Np. 600 700 800"
                                prefix-icon="pi pi-phone"
                                :error="!!profileForm.errors.phone"
                            />
                            <small
                                v-if="profileForm.errors.phone"
                                class="text-xs text-red-400"
                            >
                                {{ profileForm.errors.phone }}
                            </small>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label :class="labelClass">PESEL</label>
                            <BaseMaskedInput
                                v-model="profileForm.pesel"
                                mask="###########"
                                placeholder="Np. 90010112345"
                                prefix-icon="pi pi-id-card"
                                :error="!!profileForm.errors.pesel"
                            />
                            <small
                                v-if="profileForm.errors.pesel"
                                class="text-xs text-red-400"
                            >
                                {{ profileForm.errors.pesel }}
                            </small>
                        </div>
                    </section>

                    <!-- Address -->
                    <section :class="sectionClass">
                        <h3 :class="sectionTitleClass">Adres</h3>

                        <div class="flex flex-col gap-1.5">
                            <label :class="labelClass">Ulica</label>
                            <BaseInput
                                v-model="profileForm.street"
                                placeholder="Np. Krakowska"
                                :error="!!profileForm.errors.street"
                            />
                            <small
                                v-if="profileForm.errors.street"
                                class="text-xs text-red-400"
                            >
                                {{ profileForm.errors.street }}
                            </small>
                        </div>

                        <div class="grid grid-cols-2 gap-3.5">
                            <div class="flex flex-col gap-1.5">
                                <label :class="labelClass">Nr budynku</label>
                                <BaseInput
                                    v-model="profileForm.building_number"
                                    placeholder="Np. 12"
                                    :error="
                                        !!profileForm.errors.building_number
                                    "
                                />
                                <small
                                    v-if="profileForm.errors.building_number"
                                    class="text-xs text-red-400"
                                >
                                    {{ profileForm.errors.building_number }}
                                </small>
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label :class="labelClass">Nr mieszkania</label>
                                <BaseInput
                                    v-model="profileForm.apartment_number"
                                    placeholder="Np. 4"
                                    :error="
                                        !!profileForm.errors.apartment_number
                                    "
                                />
                                <small
                                    v-if="profileForm.errors.apartment_number"
                                    class="text-xs text-red-400"
                                >
                                    {{ profileForm.errors.apartment_number }}
                                </small>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3.5">
                            <div class="flex flex-col gap-1.5">
                                <label :class="labelClass">Kod pocztowy</label>
                                <BaseInput
                                    v-model="profileForm.postal_code"
                                    placeholder="Np. 43-300"
                                    :error="!!profileForm.errors.postal_code"
                                />
                                <small
                                    v-if="profileForm.errors.postal_code"
                                    class="text-xs text-red-400"
                                >
                                    {{ profileForm.errors.postal_code }}
                                </small>
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label :class="labelClass">Miasto</label>
                                <BaseInput
                                    v-model="profileForm.city"
                                    placeholder="Np. Bielsko-Biała"
                                    :error="!!profileForm.errors.city"
                                />
                                <small
                                    v-if="profileForm.errors.city"
                                    class="text-xs text-red-400"
                                >
                                    {{ profileForm.errors.city }}
                                </small>
                            </div>
                        </div>
                    </section>
                </form>

                <div class="mt-5 flex justify-end">
                    <button
                        type="submit"
                        form="profile-form"
                        :disabled="profileForm.processing"
                        class="flex items-center justify-center gap-2 rounded-[var(--border-radius)] px-5 py-2.5 text-sm font-semibold transition-all duration-150"
                        :style="submitButtonStyle"
                    >
                        <i
                            v-if="profileForm.processing"
                            class="pi pi-spin pi-spinner text-sm"
                        />
                        {{
                            profileForm.processing
                                ? "Zapisywanie..."
                                : "Zapisz dane"
                        }}
                    </button>
                </div>
            </div>

            <!-- Change password -->
            <div class="rounded-2xl p-6" :style="cardStyle">
                <h2
                    class="mb-1 text-lg font-semibold"
                    style="color: var(--text-color)"
                >
                    Zmiana hasła
                </h2>
                <p
                    class="mb-6 text-sm"
                    style="color: var(--text-color-secondary)"
                >
                    Użyj długiego, losowego hasła, aby zapewnić bezpieczeństwo
                    konta.
                </p>

                <form
                    @submit.prevent="submitPassword"
                    class="flex max-w-md flex-col gap-5"
                >
                    <div class="flex flex-col gap-1.5">
                        <label :class="labelClass">Aktualne hasło</label>
                        <BasePassword
                            v-model="passwordForm.current_password"
                            placeholder="••••••••"
                            prefix-icon="pi pi-lock"
                            :error="!!passwordForm.errors.current_password"
                        />
                        <small
                            v-if="passwordForm.errors.current_password"
                            class="text-xs text-red-400"
                        >
                            {{ passwordForm.errors.current_password }}
                        </small>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label :class="labelClass">Nowe hasło</label>
                        <BasePassword
                            v-model="passwordForm.password"
                            placeholder="••••••••"
                            prefix-icon="pi pi-lock"
                            show-strength
                            :error="!!passwordForm.errors.password"
                        />
                        <small
                            v-if="passwordForm.errors.password"
                            class="text-xs text-red-400"
                        >
                            {{ passwordForm.errors.password }}
                        </small>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label :class="labelClass">Powtórz nowe hasło</label>
                        <BasePassword
                            v-model="passwordForm.password_confirmation"
                            placeholder="••••••••"
                            prefix-icon="pi pi-lock"
                            :error="!!passwordForm.errors.password_confirmation"
                        />
                    </div>

                    <div>
                        <button
                            type="submit"
                            :disabled="passwordForm.processing"
                            class="flex items-center justify-center gap-2 rounded-[var(--border-radius)] px-5 py-2.5 text-sm font-semibold transition-all duration-150"
                            :style="submitButtonStyle"
                        >
                            <i
                                v-if="passwordForm.processing"
                                class="pi pi-spin pi-spinner text-sm"
                            />
                            {{
                                passwordForm.processing
                                    ? "Zapisywanie..."
                                    : "Zapisz nowe hasło"
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
