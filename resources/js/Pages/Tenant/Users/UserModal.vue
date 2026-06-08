<script setup lang="ts">
import type { InertiaForm } from "@inertiajs/vue3";
import type { UserFormData, FilterOption } from "./users.resource";

defineOptions({ name: "UserModal" });

interface Props {
    show: boolean;
    title: string;
    isEdit: boolean;
    form: InertiaForm<UserFormData>;
    roleOptions: FilterOption[];
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
</script>

<template>
    <BaseDialog
        :visible="show"
        :title="title"
        panel-class="w-11/12 max-w-xl lg:max-w-5xl"
        @update:visible="onVisibility"
    >
        <form
            id="user-form"
            class="flex w-full flex-col gap-5 pb-4 md:flex-row"
            @submit.prevent="emit('submit')"
        >
            <!-- === Column 1: dane osobowe === -->
            <section
                class="flex flex-1 flex-col gap-3.5 rounded-xl border border-sky-400/10 bg-slate-950/40 p-4 md:min-h-[20rem]"
            >
                <h3
                    class="border-b border-sky-400/10 pb-2 text-xs font-semibold tracking-wider text-slate-400 uppercase"
                >
                    Dane osobowe
                </h3>

                <div class="flex flex-col gap-1.5">
                    <label
                        class="text-xs font-medium tracking-wide text-slate-400 uppercase"
                    >
                        Imię <span class="text-red-400">*</span>
                    </label>
                    <BaseInput
                        v-model="form.first_name"
                        placeholder="Np. Anna"
                        :error="!!form.errors.first_name"
                    />
                    <small
                        v-if="form.errors.first_name"
                        class="text-xs text-red-400"
                    >
                        {{ form.errors.first_name }}
                    </small>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label
                        class="text-xs font-medium tracking-wide text-slate-400 uppercase"
                    >
                        Nazwisko <span class="text-red-400">*</span>
                    </label>
                    <BaseInput
                        v-model="form.last_name"
                        placeholder="Np. Kowalska"
                        :error="!!form.errors.last_name"
                    />
                    <small
                        v-if="form.errors.last_name"
                        class="text-xs text-red-400"
                    >
                        {{ form.errors.last_name }}
                    </small>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label
                        class="text-xs font-medium tracking-wide text-slate-400 uppercase"
                    >
                        E-mail <span class="text-red-400">*</span>
                    </label>
                    <BaseInput
                        v-model="form.email"
                        type="email"
                        placeholder="Np. anna@sklep.pl"
                        prefix-icon="pi pi-envelope"
                        :error="!!form.errors.email"
                    />
                    <small
                        v-if="form.errors.email"
                        class="text-xs text-red-400"
                    >
                        {{ form.errors.email }}
                    </small>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label
                        class="text-xs font-medium tracking-wide text-slate-400 uppercase"
                    >
                        Telefon
                    </label>
                    <BaseMaskedInput
                        v-model="form.phone"
                        mask="### ### ###"
                        placeholder="Np. 600 700 800"
                        prefix-icon="pi pi-phone"
                        :error="!!form.errors.phone"
                    />
                    <small
                        v-if="form.errors.phone"
                        class="text-xs text-red-400"
                    >
                        {{ form.errors.phone }}
                    </small>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label
                        class="text-xs font-medium tracking-wide text-slate-400 uppercase"
                    >
                        PESEL
                    </label>
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
                    >
                        {{ form.errors.pesel }}
                    </small>
                </div>
            </section>

            <!-- === Column 2: adres === -->
            <section
                class="flex flex-1 flex-col gap-3.5 rounded-xl border border-sky-400/10 bg-slate-950/40 p-4 md:min-h-[20rem]"
            >
                <h3
                    class="border-b border-sky-400/10 pb-2 text-xs font-semibold tracking-wider text-slate-400 uppercase"
                >
                    Adres
                </h3>

                <div class="flex flex-col gap-1.5">
                    <label
                        class="text-xs font-medium tracking-wide text-slate-400 uppercase"
                    >
                        Ulica
                    </label>
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
                        <label
                            class="text-xs font-medium tracking-wide text-slate-400 uppercase"
                        >
                            Nr budynku
                        </label>
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
                        <label
                            class="text-xs font-medium tracking-wide text-slate-400 uppercase"
                        >
                            Nr mieszkania
                        </label>
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
                        <label
                            class="text-xs font-medium tracking-wide text-slate-400 uppercase"
                        >
                            Kod pocztowy
                        </label>
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
                        <label
                            class="text-xs font-medium tracking-wide text-slate-400 uppercase"
                        >
                            Miasto
                        </label>
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
            </section>

            <!-- === Column 3: dostęp === -->
            <section
                class="flex flex-1 flex-col gap-3.5 rounded-xl border border-sky-400/10 bg-slate-950/40 p-4 md:min-h-[20rem]"
            >
                <h3
                    class="border-b border-sky-400/10 pb-2 text-xs font-semibold tracking-wider text-slate-400 uppercase"
                >
                    Dostęp
                </h3>

                <div class="flex flex-col gap-1.5">
                    <label
                        class="text-xs font-medium tracking-wide text-slate-400 uppercase"
                    >
                        Rola <span class="text-red-400">*</span>
                    </label>
                    <BaseDropdown
                        v-model="form.role"
                        :options="roleOptions"
                        placeholder="Wybierz rolę"
                        :error="!!form.errors.role"
                    />
                    <small v-if="form.errors.role" class="text-xs text-red-400">
                        {{ form.errors.role }}
                    </small>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label
                        class="text-xs font-medium tracking-wide text-slate-400 uppercase"
                    >
                        Status konta
                    </label>
                    <div
                        class="flex items-center gap-2.5 rounded-lg border border-sky-400/10 bg-slate-950/40 p-3"
                    >
                        <BaseCheckbox
                            v-model="form.is_active"
                            label="Konto aktywne"
                        />
                    </div>
                    <small
                        v-if="form.errors.is_active"
                        class="text-xs text-red-400"
                    >
                        {{ form.errors.is_active }}
                    </small>
                </div>

                <div v-if="!isEdit" class="flex flex-col gap-1.5">
                    <label
                        class="text-xs font-medium tracking-wide text-slate-400 uppercase"
                    >
                        Hasło <span class="text-red-400">*</span>
                    </label>
                    <BasePassword
                        v-model="form.password"
                        placeholder="Minimum 8 znaków"
                        :show-strength="true"
                        :error="!!form.errors.password"
                    />
                    <small
                        v-if="form.errors.password"
                        class="text-xs text-red-400"
                    >
                        {{ form.errors.password }}
                    </small>
                </div>

                <p
                    v-else
                    class="mt-1 flex items-start gap-2 rounded-lg border border-sky-400/10 bg-sky-400/5 p-2.5 text-xs leading-relaxed text-slate-400"
                >
                    <i class="pi pi-info-circle mt-0.5 text-sky-400/70" />
                    Hasła nie można zmienić podczas edycji konta.
                </p>
            </section>
        </form>

        <template #footer>
            <button
                type="button"
                class="rounded-lg border border-slate-400/15 bg-slate-400/5 px-4 py-2 text-sm text-slate-400 transition-colors hover:bg-slate-400/10 disabled:cursor-not-allowed disabled:opacity-45"
                :disabled="form.processing"
                @click="emit('close')"
            >
                Anuluj
            </button>
            <button
                type="submit"
                form="user-form"
                class="inline-flex items-center gap-1.5 rounded-lg border border-sky-400/35 bg-sky-400/15 px-5 py-2 text-sm font-medium text-sky-400 transition-all hover:border-sky-400/50 hover:bg-sky-400/25 disabled:cursor-not-allowed disabled:opacity-45"
                :disabled="form.processing"
            >
                <i v-if="form.processing" class="pi pi-spin pi-spinner" />
                <i v-else class="pi pi-check" />
                {{ form.processing ? "Zapisywanie..." : "Zapisz" }}
            </button>
        </template>
    </BaseDialog>
</template>
