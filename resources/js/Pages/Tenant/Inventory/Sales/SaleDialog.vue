<script setup lang="ts">
import type { InertiaForm } from "@inertiajs/vue3";
import { computed } from "vue";
import { useMoney } from "@/composables/useMoney";
import type { SaleFormData, SellableRecord } from "../sales.resource";

defineOptions({ name: "SaleDialog" });

const props = defineProps<{
    show: boolean;
    record: SellableRecord | null;
    form: InertiaForm<SaleFormData>;
}>();

const emit = defineEmits<{
    (e: "close"): void;
    (e: "submit"): void;
}>();

const { formatPrice } = useMoney();

const total = computed<number>(
    () =>
        (Number(props.form.sale_price) || 0) *
        (Number(props.form.quantity) || 0),
);

const maxQuantity = computed<number>(() => props.record?.quantity ?? 1);

function onVisibility(visible: boolean): void {
    if (!visible) {
        emit("close");
    }
}
</script>

<template>
    <BaseDialog
        :visible="show"
        title="Sprzedaż płyty"
        panel-class="w-11/12 max-w-lg"
        @update:visible="onVisibility"
    >
        <form
            v-if="record"
            id="inventory-sale-form"
            class="flex w-full flex-col gap-4"
            @submit.prevent="emit('submit')"
        >
            <!-- Selected record summary -->
            <div
                class="flex items-center gap-3.5 rounded-xl border border-emerald-400/15 bg-emerald-400/5 p-3"
            >
                <div
                    class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-slate-400/15 bg-slate-950/60"
                >
                    <img
                        v-if="record.cover_image"
                        :src="record.cover_image"
                        :alt="record.name"
                        class="h-full w-full object-cover"
                    />
                    <i v-else class="pi pi-image text-xl text-slate-600" />
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-slate-100">
                        {{ record.name }}
                    </p>
                    <p class="truncate text-xs text-slate-400">
                        {{ record.artist }}
                    </p>
                    <div class="mt-1.5 flex items-center gap-2">
                        <span
                            class="rounded border border-sky-400/25 bg-sky-400/10 px-1.5 py-0.5 text-[0.68rem] font-medium text-sky-300"
                        >
                            {{ record.format }}
                        </span>
                        <span class="text-[0.7rem] text-slate-500">
                            Stan: {{ record.quantity }} szt.
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <!-- Quantity -->
                <div class="flex flex-col gap-1.5">
                    <label
                        class="text-xs font-medium tracking-wide text-slate-400 uppercase"
                    >
                        Ilość <span class="text-red-400">*</span>
                    </label>
                    <BaseInputNumber
                        v-model="form.quantity"
                        :min="1"
                        :max="maxQuantity"
                        show-buttons
                        placeholder="1"
                        :error="!!form.errors.quantity"
                    />
                    <small
                        v-if="form.errors.quantity"
                        class="text-xs text-red-400"
                    >
                        {{ form.errors.quantity }}
                    </small>
                </div>

                <!-- Unit sale price -->
                <div class="flex flex-col gap-1.5">
                    <label
                        class="text-xs font-medium tracking-wide text-slate-400 uppercase"
                    >
                        Cena za szt. <span class="text-red-400">*</span>
                    </label>
                    <BaseInputNumber
                        v-model="form.sale_price"
                        :min="0"
                        :step="0.01"
                        format="currency"
                        suffix="zł"
                        placeholder="0,00"
                        :error="!!form.errors.sale_price"
                    />
                    <small
                        v-if="form.errors.sale_price"
                        class="text-xs text-red-400"
                    >
                        {{ form.errors.sale_price }}
                    </small>
                </div>
            </div>

            <!-- Note -->
            <div class="flex flex-col gap-1.5">
                <label
                    class="text-xs font-medium tracking-wide text-slate-400 uppercase"
                >
                    Notatka
                </label>
                <BaseTextArea
                    v-model="form.note"
                    :rows="2"
                    placeholder="Np. kupujący, kanał sprzedaży..."
                    :error="!!form.errors.note"
                />
                <small v-if="form.errors.note" class="text-xs text-red-400">
                    {{ form.errors.note }}
                </small>
            </div>

            <!-- Totals -->
            <div
                class="flex items-center justify-between rounded-xl border border-slate-400/10 bg-slate-950/40 px-4 py-3"
            >
                <div class="flex flex-col">
                    <span class="text-[0.7rem] text-slate-500 uppercase">
                        Cena jednostkowa
                    </span>
                    <span class="text-sm text-slate-300">
                        {{ formatPrice(form.sale_price || null) }}
                    </span>
                </div>
                <div class="flex flex-col items-end">
                    <span class="text-[0.7rem] text-slate-500 uppercase">
                        Razem
                    </span>
                    <span class="text-lg font-bold text-emerald-400">
                        {{ formatPrice(total) }}
                    </span>
                </div>
            </div>
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
                form="inventory-sale-form"
                class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-400/35 bg-emerald-400/15 px-5 py-2 text-sm font-medium text-emerald-400 transition-all hover:border-emerald-400/50 hover:bg-emerald-400/25 disabled:cursor-not-allowed disabled:opacity-45"
                :disabled="form.processing"
            >
                <i v-if="form.processing" class="pi pi-spin pi-spinner" />
                <i v-else class="pi pi-shopping-cart" />
                {{ form.processing ? "Sprzedawanie..." : "Sprzedaj" }}
            </button>
        </template>
    </BaseDialog>
</template>
