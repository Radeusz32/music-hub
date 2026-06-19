<script setup lang="ts">
import { computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { useToast } from "@/composables/useToast";
import {
    useStepByStep,
    type StepDefinition,
} from "@/composables/useStepByStep";
import StepByStep from "@/Components/StepByStep.vue";

interface InvitationProps {
    uuid: string;
    email: string;
    expires_at: string;
}

const props = defineProps<{
    invitation: InvitationProps;
    baseDomain: string;
}>();
const toast = useToast();

const form = useForm({
    // Domain
    domain: "",
    // Company
    company_name: "",
    tax_id: "",
    regon: "",
    krs_number: "",
    company_email: "",
    company_phone: "",
    website: "",
    // Address
    street: "",
    building_number: "",
    apartment_number: "",
    postal_code: "",
    city: "",
    country: "Polska",
    // Owner
    first_name: "",
    last_name: "",
    phone: "",
    pesel: "",
    // Security
    password: "",
    password_confirmation: "",
});

function sanitizeDomain(value: string): string {
    return value
        .toLowerCase()
        .replace(/[^a-z0-9-]/g, "-")
        .replace(/-+/g, "-")
        .replace(/^-/, "");
}

const domainPreview = computed(
    () => `${form.domain || "twoja-nazwa"}.${props.baseDomain}`,
);

const steps: StepDefinition[] = [
    {
        key: "domain",
        label: "Adres",
        icon: "pi pi-globe",
        description: "Wybierz unikalny adres swojej organizacji.",
        fields: ["domain"],
    },
    {
        key: "company",
        label: "Dane firmy",
        icon: "pi pi-building",
        description: "Dane identyfikacyjne Twojej organizacji.",
        fields: [
            "company_name",
            "tax_id",
            "regon",
            "krs_number",
            "company_email",
            "company_phone",
            "website",
        ],
    },
    {
        key: "address",
        label: "Adres siedziby",
        icon: "pi pi-map-marker",
        description: "Adres rejestrowy firmy.",
        fields: [
            "street",
            "building_number",
            "apartment_number",
            "postal_code",
            "city",
            "country",
        ],
    },
    {
        key: "owner",
        label: "Właściciel",
        icon: "pi pi-user",
        description: "Dane właściciela konta.",
        fields: ["first_name", "last_name", "phone", "pesel"],
    },
    {
        key: "security",
        label: "Hasło",
        icon: "pi pi-lock",
        description: "Ustaw hasło do logowania.",
        fields: ["password", "password_confirmation"],
    },
];

async function validateStep(step: StepDefinition): Promise<boolean> {
    form.clearErrors();

    try {
        await window.axios.post(
            route("invitation.validate-step", { uuid: props.invitation.uuid }),
            { step: step.key, ...form.data() },
        );
        return true;
    } catch (err) {
        const response = (
            err as {
                response?: {
                    status: number;
                    data?: {
                        errors?: Record<string, string[]>;
                        message?: string;
                    };
                };
            }
        ).response;

        if (response?.status === 422 && response.data?.errors) {
            for (const [field, messages] of Object.entries(
                response.data.errors,
            )) {
                form.setError(field as keyof typeof form.errors, messages[0]);
            }
        } else {
            toast.error(
                response?.data?.message ??
                    "Wystąpił błąd. Spróbuj ponownie.",
            );
        }
        return false;
    }
}

const {
    currentIndex,
    completed,
    validating,
    isFirst,
    isLast,
    next,
    prev,
    goTo,
    clearStorage,
} = useStepByStep({
    steps,
    data: form,
    storageKey: `sb-invitation-${props.invitation.uuid}`,
    excludeFromStorage: ["password", "password_confirmation"],
    onValidateStep: validateStep,
});

function submit(): void {
    form.post(route("invitation.submit", { uuid: props.invitation.uuid }), {
        onSuccess: () => clearStorage(),
        onError: (errors) => {
            const firstField = Object.keys(errors)[0];
            const stepIndex = steps.findIndex((s) =>
                s.fields?.includes(firstField),
            );
            if (stepIndex >= 0) {
                currentIndex.value = stepIndex;
            }
            toast.error("Popraw błędy w formularzu.");
        },
    });
}

const labelClass = "text-xs font-semibold uppercase tracking-wide" as const;
</script>

<template>
    <div
        class="min-h-screen px-4 py-12"
        style="background: var(--surface-ground); color: var(--text-color)"
    >
        <!-- Ambient glow -->
        <div
            class="pointer-events-none fixed inset-0 overflow-hidden"
            aria-hidden="true"
        >
            <div
                class="absolute -left-64 -top-64 h-[600px] w-[600px] rounded-full opacity-8"
                style="
                    background: radial-gradient(
                        circle,
                        #d946ef 0%,
                        transparent 70%
                    );
                "
            />
            <div
                class="absolute -bottom-64 -right-64 h-[500px] w-[500px] rounded-full opacity-6"
                style="
                    background: radial-gradient(
                        circle,
                        #6366f1 0%,
                        transparent 70%
                    );
                "
            />
        </div>

        <div class="relative mx-auto w-full max-w-3xl">
            <!-- Branding -->
            <div class="mb-8 text-center">
                <div class="mb-2 text-2xl font-bold" style="color: #d946ef">
                    MusicHub
                </div>
                <h1
                    class="text-xl font-bold"
                    style="color: var(--text-color)"
                >
                    Skonfiguruj swoją organizację
                </h1>
                <p
                    class="mt-1 text-sm"
                    style="color: var(--text-color-secondary)"
                >
                    Zaproszenie dla
                    <strong style="color: var(--text-color)">{{
                        invitation.email
                    }}</strong>
                </p>
            </div>

            <div
                class="rounded-2xl border border-sky-400/10 bg-slate-950/40 p-6 sm:p-8"
            >
                <StepByStep
                    :steps="steps"
                    :current-index="currentIndex"
                    :completed="completed"
                    :validating="validating"
                    :is-first="isFirst"
                    :is-last="isLast"
                    :final-processing="form.processing"
                    final-label="Wyślij formularz"
                    @next="next"
                    @prev="prev"
                    @go="goTo"
                    @finish="submit"
                >
                    <!-- Step 1: Domain -->
                    <template #domain>
                        <div class="flex flex-col gap-4">
                            <div class="flex flex-col gap-1.5">
                                <label
                                    :class="labelClass"
                                    style="color: var(--text-color-secondary)"
                                >
                                    Adres organizacji
                                    <span class="text-red-400">*</span>
                                </label>
                                <BaseInput
                                    :model-value="form.domain"
                                    placeholder="np. beskid-vinyl"
                                    prefix-icon="pi pi-globe"
                                    :error="!!form.errors.domain"
                                    @update:model-value="
                                        form.domain = sanitizeDomain($event)
                                    "
                                />
                                <small
                                    v-if="form.errors.domain"
                                    class="text-xs text-red-400"
                                    >{{ form.errors.domain }}</small
                                >
                            </div>

                            <div
                                class="flex items-center gap-2 rounded-xl border border-sky-400/15 bg-sky-400/5 px-4 py-3 text-sm text-slate-300"
                            >
                                <i class="pi pi-link text-sky-400" />
                                <span>
                                    Adres Twojej organizacji:
                                    <strong class="text-sky-300">{{
                                        domainPreview
                                    }}</strong>
                                </span>
                            </div>
                        </div>
                    </template>

                    <!-- Step 2: Company -->
                    <template #company>
                        <div class="flex flex-col gap-4">
                            <div class="flex flex-col gap-1.5">
                                <label
                                    :class="labelClass"
                                    style="color: var(--text-color-secondary)"
                                >
                                    Nazwa firmy
                                    <span class="text-red-400">*</span>
                                </label>
                                <BaseInput
                                    v-model="form.company_name"
                                    placeholder="Np. Beskid Vinyl"
                                    prefix-icon="pi pi-building"
                                    :error="!!form.errors.company_name"
                                />
                                <small
                                    v-if="form.errors.company_name"
                                    class="text-xs text-red-400"
                                    >{{ form.errors.company_name }}</small
                                >
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div class="flex flex-col gap-1.5">
                                    <label
                                        :class="labelClass"
                                        style="color: var(--text-color-secondary)"
                                        >NIP</label
                                    >
                                    <BaseInput
                                        v-model="form.tax_id"
                                        placeholder="Np. 5472158963"
                                        :error="!!form.errors.tax_id"
                                    />
                                    <small
                                        v-if="form.errors.tax_id"
                                        class="text-xs text-red-400"
                                        >{{ form.errors.tax_id }}</small
                                    >
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label
                                        :class="labelClass"
                                        style="color: var(--text-color-secondary)"
                                        >REGON</label
                                    >
                                    <BaseInput
                                        v-model="form.regon"
                                        placeholder="Np. 147852369"
                                        :error="!!form.errors.regon"
                                    />
                                    <small
                                        v-if="form.errors.regon"
                                        class="text-xs text-red-400"
                                        >{{ form.errors.regon }}</small
                                    >
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label
                                        :class="labelClass"
                                        style="color: var(--text-color-secondary)"
                                        >KRS</label
                                    >
                                    <BaseInput
                                        v-model="form.krs_number"
                                        placeholder="Np. 0000456789"
                                        :error="!!form.errors.krs_number"
                                    />
                                    <small
                                        v-if="form.errors.krs_number"
                                        class="text-xs text-red-400"
                                        >{{ form.errors.krs_number }}</small
                                    >
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="flex flex-col gap-1.5">
                                    <label
                                        :class="labelClass"
                                        style="color: var(--text-color-secondary)"
                                        >E-mail firmowy</label
                                    >
                                    <BaseInput
                                        v-model="form.company_email"
                                        type="email"
                                        placeholder="kontakt@firma.pl"
                                        prefix-icon="pi pi-envelope"
                                        :error="!!form.errors.company_email"
                                    />
                                    <small
                                        v-if="form.errors.company_email"
                                        class="text-xs text-red-400"
                                        >{{ form.errors.company_email }}</small
                                    >
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label
                                        :class="labelClass"
                                        style="color: var(--text-color-secondary)"
                                        >Telefon</label
                                    >
                                    <BaseMaskedInput
                                        v-model="form.company_phone"
                                        mask="### ### ###"
                                        placeholder="Np. 338 112 233"
                                        prefix-icon="pi pi-phone"
                                        :error="!!form.errors.company_phone"
                                    />
                                    <small
                                        v-if="form.errors.company_phone"
                                        class="text-xs text-red-400"
                                        >{{ form.errors.company_phone }}</small
                                    >
                                </div>
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label
                                    :class="labelClass"
                                    style="color: var(--text-color-secondary)"
                                    >Strona WWW</label
                                >
                                <BaseInput
                                    v-model="form.website"
                                    placeholder="https://firma.pl"
                                    prefix-icon="pi pi-globe"
                                    :error="!!form.errors.website"
                                />
                                <small
                                    v-if="form.errors.website"
                                    class="text-xs text-red-400"
                                    >{{ form.errors.website }}</small
                                >
                            </div>
                        </div>
                    </template>

                    <!-- Step 3: Address -->
                    <template #address>
                        <div class="flex flex-col gap-4">
                            <div class="flex flex-col gap-1.5">
                                <label
                                    :class="labelClass"
                                    style="color: var(--text-color-secondary)"
                                    >Ulica</label
                                >
                                <BaseInput
                                    v-model="form.street"
                                    placeholder="Np. Krakowska"
                                    :error="!!form.errors.street"
                                />
                                <small
                                    v-if="form.errors.street"
                                    class="text-xs text-red-400"
                                    >{{ form.errors.street }}</small
                                >
                            </div>

                            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                                <div class="flex flex-col gap-1.5">
                                    <label
                                        :class="labelClass"
                                        style="color: var(--text-color-secondary)"
                                        >Nr budynku</label
                                    >
                                    <BaseInput
                                        v-model="form.building_number"
                                        placeholder="12"
                                        :error="!!form.errors.building_number"
                                    />
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label
                                        :class="labelClass"
                                        style="color: var(--text-color-secondary)"
                                        >Nr lokalu</label
                                    >
                                    <BaseInput
                                        v-model="form.apartment_number"
                                        placeholder="4"
                                        :error="!!form.errors.apartment_number"
                                    />
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label
                                        :class="labelClass"
                                        style="color: var(--text-color-secondary)"
                                        >Kod pocztowy</label
                                    >
                                    <BaseInput
                                        v-model="form.postal_code"
                                        placeholder="43-300"
                                        :error="!!form.errors.postal_code"
                                    />
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label
                                        :class="labelClass"
                                        style="color: var(--text-color-secondary)"
                                        >Miasto</label
                                    >
                                    <BaseInput
                                        v-model="form.city"
                                        placeholder="Bielsko-Biała"
                                        :error="!!form.errors.city"
                                    />
                                </div>
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label
                                    :class="labelClass"
                                    style="color: var(--text-color-secondary)"
                                    >Kraj</label
                                >
                                <BaseInput
                                    v-model="form.country"
                                    placeholder="Polska"
                                    :error="!!form.errors.country"
                                />
                            </div>
                        </div>
                    </template>

                    <!-- Step 4: Owner -->
                    <template #owner>
                        <div class="flex flex-col gap-4">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="flex flex-col gap-1.5">
                                    <label
                                        :class="labelClass"
                                        style="color: var(--text-color-secondary)"
                                    >
                                        Imię
                                        <span class="text-red-400">*</span>
                                    </label>
                                    <BaseInput
                                        v-model="form.first_name"
                                        placeholder="Jan"
                                        :error="!!form.errors.first_name"
                                    />
                                    <small
                                        v-if="form.errors.first_name"
                                        class="text-xs text-red-400"
                                        >{{ form.errors.first_name }}</small
                                    >
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label
                                        :class="labelClass"
                                        style="color: var(--text-color-secondary)"
                                    >
                                        Nazwisko
                                        <span class="text-red-400">*</span>
                                    </label>
                                    <BaseInput
                                        v-model="form.last_name"
                                        placeholder="Kowalski"
                                        :error="!!form.errors.last_name"
                                    />
                                    <small
                                        v-if="form.errors.last_name"
                                        class="text-xs text-red-400"
                                        >{{ form.errors.last_name }}</small
                                    >
                                </div>
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label
                                    :class="labelClass"
                                    style="color: var(--text-color-secondary)"
                                    >E-mail</label
                                >
                                <BaseInput
                                    :model-value="invitation.email"
                                    disabled
                                    prefix-icon="pi pi-envelope"
                                />
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="flex flex-col gap-1.5">
                                    <label
                                        :class="labelClass"
                                        style="color: var(--text-color-secondary)"
                                        >Telefon</label
                                    >
                                    <BaseMaskedInput
                                        v-model="form.phone"
                                        mask="### ### ###"
                                        placeholder="Np. 500 100 200"
                                        prefix-icon="pi pi-phone"
                                        :error="!!form.errors.phone"
                                    />
                                    <small
                                        v-if="form.errors.phone"
                                        class="text-xs text-red-400"
                                        >{{ form.errors.phone }}</small
                                    >
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label
                                        :class="labelClass"
                                        style="color: var(--text-color-secondary)"
                                        >PESEL</label
                                    >
                                    <BaseMaskedInput
                                        v-model="form.pesel"
                                        mask="###########"
                                        placeholder="Np. 90010112345"
                                        prefix-icon="pi pi-id-card"
                                        :error="!!form.errors.pesel"
                                    />
                                    <small
                                        v-if="form.errors.pesel"
                                        class="text-xs text-red-400"
                                        >{{ form.errors.pesel }}</small
                                    >
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Step 5: Security -->
                    <template #security>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="flex flex-col gap-1.5">
                                <label
                                    :class="labelClass"
                                    style="color: var(--text-color-secondary)"
                                >
                                    Hasło <span class="text-red-400">*</span>
                                </label>
                                <BaseInput
                                    v-model="form.password"
                                    type="password"
                                    placeholder="Min. 8 znaków"
                                    prefix-icon="pi pi-lock"
                                    :error="!!form.errors.password"
                                />
                                <small
                                    v-if="form.errors.password"
                                    class="text-xs text-red-400"
                                    >{{ form.errors.password }}</small
                                >
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label
                                    :class="labelClass"
                                    style="color: var(--text-color-secondary)"
                                >
                                    Powtórz hasło
                                    <span class="text-red-400">*</span>
                                </label>
                                <BaseInput
                                    v-model="form.password_confirmation"
                                    type="password"
                                    placeholder="Powtórz hasło"
                                    prefix-icon="pi pi-lock"
                                    :error="!!form.errors.password_confirmation"
                                />
                                <small
                                    v-if="form.errors.password_confirmation"
                                    class="text-xs text-red-400"
                                    >{{
                                        form.errors.password_confirmation
                                    }}</small
                                >
                            </div>
                        </div>
                    </template>
                </StepByStep>
            </div>
        </div>
    </div>
</template>
