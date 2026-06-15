<script setup lang="ts">
import type { InertiaForm } from "@inertiajs/vue3";
import type { InventoryFormData, FilterOption } from "./inventory.resource";

defineOptions({ name: "InventoryRecordModal" });

interface Props {
    show: boolean;
    title: string;
    form: InertiaForm<InventoryFormData>;
    formatOptions: FilterOption[];
    conditionOptions: FilterOption[];
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
        panel-class="w-11/12 max-w-5xl"
        @update:visible="onVisibility"
    >
        <form
            id="inventory-record-form"
            class="flex w-full flex-col gap-5 md:flex-row"
            @submit.prevent="emit('submit')"
        >
            <!-- === Column 1: podstawowe dane === -->
            <section
                class="flex flex-1 flex-col gap-3.5 rounded-xl border border-sky-400/10 bg-slate-950/40 p-4"
            >
                <h3
                    class="border-b border-sky-400/10 pb-2 text-xs font-semibold tracking-wider text-slate-400 uppercase"
                >
                    Podstawowe dane
                </h3>

                <div class="flex flex-col gap-1.5">
                    <label
                        class="text-xs font-medium tracking-wide text-slate-400 uppercase"
                    >
                        Tytuł <span class="text-red-400">*</span>
                    </label>
                    <BaseInput
                        v-model="form.name"
                        placeholder="Np. Dark Side of the Moon"
                        :error="!!form.errors.name"
                    />
                    <small v-if="form.errors.name" class="text-xs text-red-400">
                        {{ form.errors.name }}
                    </small>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label
                        class="text-xs font-medium tracking-wide text-slate-400 uppercase"
                    >
                        Artysta <span class="text-red-400">*</span>
                    </label>
                    <BaseInput
                        v-model="form.artist"
                        placeholder="Np. Pink Floyd"
                        :error="!!form.errors.artist"
                    />
                    <small
                        v-if="form.errors.artist"
                        class="text-xs text-red-400"
                    >
                        {{ form.errors.artist }}
                    </small>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label
                        class="text-xs font-medium tracking-wide text-slate-400 uppercase"
                    >
                        Gatunek <span class="text-red-400">*</span>
                    </label>
                    <BaseInput
                        v-model="form.genre"
                        placeholder="Np. Rock"
                        :error="!!form.errors.genre"
                    />
                    <small
                        v-if="form.errors.genre"
                        class="text-xs text-red-400"
                    >
                        {{ form.errors.genre }}
                    </small>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label
                        class="text-xs font-medium tracking-wide text-slate-400 uppercase"
                    >
                        Format <span class="text-red-400">*</span>
                    </label>
                    <BaseDropdown
                        v-model="form.format"
                        :options="formatOptions"
                        placeholder="Wybierz format"
                        :error="!!form.errors.format"
                    />
                    <small
                        v-if="form.errors.format"
                        class="text-xs text-red-400"
                    >
                        {{ form.errors.format }}
                    </small>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label
                        class="text-xs font-medium tracking-wide text-slate-400 uppercase"
                    >
                        Stan <span class="text-red-400">*</span>
                    </label>
                    <BaseDropdown
                        v-model="form.condition"
                        :options="conditionOptions"
                        placeholder="Wybierz stan"
                        :error="!!form.errors.condition"
                    />
                    <small
                        v-if="form.errors.condition"
                        class="text-xs text-red-400"
                    >
                        {{ form.errors.condition }}
                    </small>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label
                        class="text-xs font-medium tracking-wide text-slate-400 uppercase"
                    >
                        Wytwórnia
                    </label>
                    <BaseInput v-model="form.label" placeholder="Np. EMI" />
                </div>

                <div class="flex flex-col gap-1.5">
                    <label
                        class="text-xs font-medium tracking-wide text-slate-400 uppercase"
                    >
                        Nr katalogowy
                    </label>
                    <BaseInput
                        v-model="form.catalog_number"
                        placeholder="Np. SHVL 804"
                    />
                </div>
            </section>

            <!-- === Column 2: wydanie i ceny === -->
            <section
                class="flex flex-1 flex-col gap-3.5 rounded-xl border border-sky-400/10 bg-slate-950/40 p-4"
            >
                <h3
                    class="border-b border-sky-400/10 pb-2 text-xs font-semibold tracking-wider text-slate-400 uppercase"
                >
                    Wydanie i ceny
                </h3>

                <div class="grid grid-cols-2 gap-3.5">
                    <div class="flex flex-col gap-1.5">
                        <label
                            class="text-xs font-medium tracking-wide text-slate-400 uppercase"
                        >
                            Rok
                        </label>
                        <BaseInputNumber
                            v-model="form.year"
                            :min="1900"
                            :max="new Date().getFullYear()"
                            placeholder="Np. 1973"
                            :error="!!form.errors.year"
                        />
                        <small
                            v-if="form.errors.year"
                            class="text-xs text-red-400"
                        >
                            {{ form.errors.year }}
                        </small>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label
                            class="text-xs font-medium tracking-wide text-slate-400 uppercase"
                        >
                            Kraj
                        </label>
                        <BaseInput
                            v-model="form.country"
                            placeholder="Np. UK"
                        />
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label
                        class="text-xs font-medium tracking-wide text-slate-400 uppercase"
                    >
                        Barcode
                    </label>
                    <BaseInput v-model="form.barcode" placeholder="EAN-13" />
                </div>

                <div class="flex flex-col gap-1.5">
                    <label
                        class="text-xs font-medium tracking-wide text-slate-400 uppercase"
                    >
                        Ilość <span class="text-red-400">*</span>
                    </label>
                    <BaseInputNumber
                        v-model="form.quantity"
                        :min="0"
                        show-buttons
                        placeholder="0"
                        :error="!!form.errors.quantity"
                    />
                    <small
                        v-if="form.errors.quantity"
                        class="text-xs text-red-400"
                    >
                        {{ form.errors.quantity }}
                    </small>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label
                        class="text-xs font-medium tracking-wide text-slate-400 uppercase"
                    >
                        Cena zakupu za szt. (PLN)
                    </label>
                    <BaseInputNumber
                        v-model="form.purchase_price_per_unit"
                        :min="0"
                        :step="0.01"
                        format="currency"
                        suffix="zł"
                        placeholder="0,00"
                        :error="!!form.errors.purchase_price_per_unit"
                    />
                    <small
                        v-if="form.errors.purchase_price_per_unit"
                        class="text-xs text-red-400"
                    >
                        {{ form.errors.purchase_price_per_unit }}
                    </small>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label
                        class="text-xs font-medium tracking-wide text-slate-400 uppercase"
                    >
                        Notatki
                    </label>
                    <BaseTextArea
                        v-model="form.notes"
                        :rows="3"
                        placeholder="Dodatkowe informacje..."
                        :error="!!form.errors.notes"
                    />
                </div>
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
                form="inventory-record-form"
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
