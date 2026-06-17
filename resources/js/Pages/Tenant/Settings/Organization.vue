<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import AppLayout from "@/layout/Tenant/AppLayout.vue";
import { useToast } from "@/composables/useToast";

interface OrganizationData {
    company_name: string | null;
    tax_id: string | null;
    regon: string | null;
    krs_number: string | null;
    company_email: string | null;
    company_phone: string | null;
    website: string | null;
    street: string | null;
    building_number: string | null;
    apartment_number: string | null;
    postal_code: string | null;
    city: string | null;
    country: string | null;
}

interface Props {
    organization: OrganizationData;
}

const props = defineProps<Props>();

const toast = useToast();

const form = useForm({
    company_name: props.organization.company_name ?? "",
    tax_id: props.organization.tax_id ?? "",
    regon: props.organization.regon ?? "",
    krs_number: props.organization.krs_number ?? "",
    company_email: props.organization.company_email ?? "",
    company_phone: props.organization.company_phone ?? "",
    website: props.organization.website ?? "",
    street: props.organization.street ?? "",
    building_number: props.organization.building_number ?? "",
    apartment_number: props.organization.apartment_number ?? "",
    postal_code: props.organization.postal_code ?? "",
    city: props.organization.city ?? "",
    country: props.organization.country ?? "",
});

const submit = (): void => {
    form.put(route("tenant.settings.organization.update"), {
        preserveScroll: true,
        onSuccess: () =>
            toast.success("Dane organizacji zostały zaktualizowane."),
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
                    Organizacja
                </h1>
                <p class="text-sm" style="color: var(--text-color-secondary)">
                    Dane firmy wykorzystywane na dokumentach i fakturach.
                </p>
            </div>

            <div class="rounded-2xl p-6" :style="cardStyle">
                <h2
                    class="mb-1 text-lg font-semibold"
                    style="color: var(--text-color)"
                >
                    Dane firmy
                </h2>
                <p
                    class="mb-6 text-sm"
                    style="color: var(--text-color-secondary)"
                >
                    Dane identyfikacyjne i adres siedziby Twojej organizacji.
                </p>

                <form
                    id="organization-form"
                    class="flex w-full flex-col gap-5 md:flex-row"
                    @submit.prevent="submit"
                >
                    <!-- Identification -->
                    <section :class="sectionClass">
                        <h3 :class="sectionTitleClass">Dane identyfikacyjne</h3>

                        <div class="flex flex-col gap-1.5">
                            <label :class="labelClass">
                                Nazwa firmy <span class="text-red-400">*</span>
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
                            >
                                {{ form.errors.company_name }}
                            </small>
                        </div>

                        <div class="grid grid-cols-2 gap-3.5">
                            <div class="flex flex-col gap-1.5">
                                <label :class="labelClass">NIP</label>
                                <BaseInput
                                    v-model="form.tax_id"
                                    placeholder="Np. 5472158963"
                                    :error="!!form.errors.tax_id"
                                />
                                <small
                                    v-if="form.errors.tax_id"
                                    class="text-xs text-red-400"
                                >
                                    {{ form.errors.tax_id }}
                                </small>
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label :class="labelClass">REGON</label>
                                <BaseInput
                                    v-model="form.regon"
                                    placeholder="Np. 147852369"
                                    :error="!!form.errors.regon"
                                />
                                <small
                                    v-if="form.errors.regon"
                                    class="text-xs text-red-400"
                                >
                                    {{ form.errors.regon }}
                                </small>
                            </div>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label :class="labelClass">Numer KRS</label>
                            <BaseInput
                                v-model="form.krs_number"
                                placeholder="Np. 0000456789"
                                :error="!!form.errors.krs_number"
                            />
                            <small
                                v-if="form.errors.krs_number"
                                class="text-xs text-red-400"
                            >
                                {{ form.errors.krs_number }}
                            </small>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label :class="labelClass">E-mail firmowy</label>
                            <BaseInput
                                v-model="form.company_email"
                                type="email"
                                placeholder="Np. kontakt@firma.pl"
                                prefix-icon="pi pi-envelope"
                                :error="!!form.errors.company_email"
                            />
                            <small
                                v-if="form.errors.company_email"
                                class="text-xs text-red-400"
                            >
                                {{ form.errors.company_email }}
                            </small>
                        </div>

                        <div class="grid grid-cols-2 gap-3.5">
                            <div class="flex flex-col gap-1.5">
                                <label :class="labelClass">Telefon</label>
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
                                >
                                    {{ form.errors.company_phone }}
                                </small>
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label :class="labelClass">Strona WWW</label>
                                <BaseInput
                                    v-model="form.website"
                                    placeholder="Np. https://firma.pl"
                                    prefix-icon="pi pi-globe"
                                    :error="!!form.errors.website"
                                />
                                <small
                                    v-if="form.errors.website"
                                    class="text-xs text-red-400"
                                >
                                    {{ form.errors.website }}
                                </small>
                            </div>
                        </div>
                    </section>

                    <!-- Address -->
                    <section :class="sectionClass">
                        <h3 :class="sectionTitleClass">Adres siedziby</h3>

                        <div class="flex flex-col gap-1.5">
                            <label :class="labelClass">Ulica</label>
                            <BaseInput
                                v-model="form.street"
                                placeholder="Np. Krakowska"
                                :error="!!form.errors.street"
                            />
                            <small
                                v-if="form.errors.street"
                                class="text-xs text-red-400"
                            >
                                {{ form.errors.street }}
                            </small>
                        </div>

                        <div class="grid grid-cols-2 gap-3.5">
                            <div class="flex flex-col gap-1.5">
                                <label :class="labelClass">Nr budynku</label>
                                <BaseInput
                                    v-model="form.building_number"
                                    placeholder="Np. 12"
                                    :error="!!form.errors.building_number"
                                />
                                <small
                                    v-if="form.errors.building_number"
                                    class="text-xs text-red-400"
                                >
                                    {{ form.errors.building_number }}
                                </small>
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label :class="labelClass">Nr lokalu</label>
                                <BaseInput
                                    v-model="form.apartment_number"
                                    placeholder="Np. 4"
                                    :error="!!form.errors.apartment_number"
                                />
                                <small
                                    v-if="form.errors.apartment_number"
                                    class="text-xs text-red-400"
                                >
                                    {{ form.errors.apartment_number }}
                                </small>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3.5">
                            <div class="flex flex-col gap-1.5">
                                <label :class="labelClass">Kod pocztowy</label>
                                <BaseInput
                                    v-model="form.postal_code"
                                    placeholder="Np. 43-300"
                                    :error="!!form.errors.postal_code"
                                />
                                <small
                                    v-if="form.errors.postal_code"
                                    class="text-xs text-red-400"
                                >
                                    {{ form.errors.postal_code }}
                                </small>
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label :class="labelClass">Miasto</label>
                                <BaseInput
                                    v-model="form.city"
                                    placeholder="Np. Bielsko-Biała"
                                    :error="!!form.errors.city"
                                />
                                <small
                                    v-if="form.errors.city"
                                    class="text-xs text-red-400"
                                >
                                    {{ form.errors.city }}
                                </small>
                            </div>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label :class="labelClass">Kraj</label>
                            <BaseInput
                                v-model="form.country"
                                placeholder="Np. Polska"
                                :error="!!form.errors.country"
                            />
                            <small
                                v-if="form.errors.country"
                                class="text-xs text-red-400"
                            >
                                {{ form.errors.country }}
                            </small>
                        </div>
                    </section>
                </form>

                <div class="mt-5 flex justify-end">
                    <button
                        type="submit"
                        form="organization-form"
                        :disabled="form.processing"
                        class="flex items-center justify-center gap-2 rounded-[var(--border-radius)] px-5 py-2.5 text-sm font-semibold transition-all duration-150"
                        :style="submitButtonStyle"
                    >
                        <i
                            v-if="form.processing"
                            class="pi pi-spin pi-spinner text-sm"
                        />
                        {{ form.processing ? "Zapisywanie..." : "Zapisz dane" }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
