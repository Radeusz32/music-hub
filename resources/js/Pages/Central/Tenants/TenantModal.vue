<script setup lang="ts">
import type { InertiaForm } from "@inertiajs/vue3";
import type { TenantFormData } from "./tenants.resource";

defineOptions({ name: "TenantModal" });

interface Props {
    show: boolean;
    title: string;
    form: InertiaForm<TenantFormData>;
}

defineProps<Props>();

const emit = defineEmits<{
    (e: "close"): void;
    (e: "submit"): void;
}>();

function onVisibility(visible: boolean): void {
    if (!visible) {
        emit("close");
    }
}

const labelClass = "text-xs font-medium tracking-wide text-slate-400 uppercase";
const sectionClass =
    "flex flex-1 flex-col gap-3.5 rounded-xl border border-fuchsia-400/10 bg-slate-950/40 p-4";
const headingClass =
    "border-b border-fuchsia-400/10 pb-2 text-xs font-semibold tracking-wider text-slate-400 uppercase";
</script>

<template>
    <BaseDialog
        :visible="show"
        :title="title"
        panel-class="w-11/12 max-w-4xl"
        @update:visible="onVisibility"
    >
        <form
            id="tenant-form"
            class="flex w-full flex-col gap-5 md:flex-row"
            @submit.prevent="emit('submit')"
        >
            <!-- === Column 1: dane firmy === -->
            <section :class="sectionClass">
                <h3 :class="headingClass">Dane firmy</h3>

                <div class="flex flex-col gap-1.5">
                    <label :class="labelClass">
                        Nazwa firmy <span class="text-red-400">*</span>
                    </label>
                    <BaseInput
                        v-model="form.company_name"
                        placeholder="Np. Beskid Vinyl"
                        :error="!!form.errors.company_name"
                    />
                    <small
                        v-if="form.errors.company_name"
                        class="text-xs text-red-400"
                    >
                        {{ form.errors.company_name }}
                    </small>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1.5">
                        <label :class="labelClass">NIP</label>
                        <BaseInput
                            v-model="form.tax_id"
                            :error="!!form.errors.tax_id"
                        />
                        <small
                            v-if="form.errors.tax_id"
                            class="text-xs text-red-400"
                            >{{ form.errors.tax_id }}</small
                        >
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label :class="labelClass">REGON</label>
                        <BaseInput
                            v-model="form.regon"
                            :error="!!form.errors.regon"
                        />
                        <small
                            v-if="form.errors.regon"
                            class="text-xs text-red-400"
                            >{{ form.errors.regon }}</small
                        >
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label :class="labelClass">KRS</label>
                    <BaseInput
                        v-model="form.krs_number"
                        :error="!!form.errors.krs_number"
                    />
                    <small
                        v-if="form.errors.krs_number"
                        class="text-xs text-red-400"
                        >{{ form.errors.krs_number }}</small
                    >
                </div>

                <div class="flex flex-col gap-1.5">
                    <label :class="labelClass">E-mail firmy</label>
                    <BaseInput
                        v-model="form.company_email"
                        type="email"
                        :error="!!form.errors.company_email"
                    />
                    <small
                        v-if="form.errors.company_email"
                        class="text-xs text-red-400"
                        >{{ form.errors.company_email }}</small
                    >
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1.5">
                        <label :class="labelClass">Telefon</label>
                        <BaseInput
                            v-model="form.company_phone"
                            :error="!!form.errors.company_phone"
                        />
                        <small
                            v-if="form.errors.company_phone"
                            class="text-xs text-red-400"
                            >{{ form.errors.company_phone }}</small
                        >
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label :class="labelClass">Strona WWW</label>
                        <BaseInput
                            v-model="form.website"
                            :error="!!form.errors.website"
                        />
                        <small
                            v-if="form.errors.website"
                            class="text-xs text-red-400"
                            >{{ form.errors.website }}</small
                        >
                    </div>
                </div>
            </section>

            <!-- === Column 2: adres === -->
            <section :class="sectionClass">
                <h3 :class="headingClass">Adres</h3>

                <div class="flex flex-col gap-1.5">
                    <label :class="labelClass">Ulica</label>
                    <BaseInput
                        v-model="form.street"
                        :error="!!form.errors.street"
                    />
                    <small
                        v-if="form.errors.street"
                        class="text-xs text-red-400"
                        >{{ form.errors.street }}</small
                    >
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1.5">
                        <label :class="labelClass">Nr budynku</label>
                        <BaseInput
                            v-model="form.building_number"
                            :error="!!form.errors.building_number"
                        />
                        <small
                            v-if="form.errors.building_number"
                            class="text-xs text-red-400"
                            >{{ form.errors.building_number }}</small
                        >
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label :class="labelClass">Nr lokalu</label>
                        <BaseInput
                            v-model="form.apartment_number"
                            :error="!!form.errors.apartment_number"
                        />
                        <small
                            v-if="form.errors.apartment_number"
                            class="text-xs text-red-400"
                            >{{ form.errors.apartment_number }}</small
                        >
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1.5">
                        <label :class="labelClass">Kod pocztowy</label>
                        <BaseInput
                            v-model="form.postal_code"
                            :error="!!form.errors.postal_code"
                        />
                        <small
                            v-if="form.errors.postal_code"
                            class="text-xs text-red-400"
                            >{{ form.errors.postal_code }}</small
                        >
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label :class="labelClass">Miasto</label>
                        <BaseInput
                            v-model="form.city"
                            :error="!!form.errors.city"
                        />
                        <small
                            v-if="form.errors.city"
                            class="text-xs text-red-400"
                            >{{ form.errors.city }}</small
                        >
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label :class="labelClass">Kraj</label>
                    <BaseInput
                        v-model="form.country"
                        :error="!!form.errors.country"
                    />
                    <small
                        v-if="form.errors.country"
                        class="text-xs text-red-400"
                        >{{ form.errors.country }}</small
                    >
                </div>
            </section>
        </form>

        <template #footer>
            <button type="button" class="btn-cancel" @click="emit('close')">
                Anuluj
            </button>
            <button
                type="submit"
                form="tenant-form"
                class="btn-save"
                :disabled="form.processing"
            >
                <i
                    v-if="form.processing"
                    class="pi pi-spin pi-spinner text-sm"
                />
                Zapisz
            </button>
        </template>
    </BaseDialog>
</template>

<style scoped>
.btn-cancel {
    padding: 0.5rem 1.1rem;
    background: transparent;
    border: 1px solid rgba(148, 163, 184, 0.2);
    border-radius: 8px;
    color: #94a3b8;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-cancel:hover {
    border-color: rgba(148, 163, 184, 0.4);
    color: #e2e8f0;
}

.btn-save {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.5rem 1.3rem;
    background: linear-gradient(135deg, #a855f7 0%, #6366f1 100%);
    border: none;
    border-radius: 8px;
    color: #fff;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 0 18px rgba(168, 85, 247, 0.25);
    transition: all 0.2s;
}

.btn-save:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
