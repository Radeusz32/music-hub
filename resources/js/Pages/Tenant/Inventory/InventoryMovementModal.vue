<script setup lang="ts">
import type { InertiaForm } from "@inertiajs/vue3";
import type {
    FilterOption,
    MovementFormData,
    RecordOption,
} from "./movements.resource";

defineOptions({ name: "InventoryMovementModal" });

interface Props {
    show: boolean;
    form: InertiaForm<MovementFormData>;
    recordOptions: RecordOption[];
    typeOptions: FilterOption[];
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
        title="Dodaj ruch magazynowy"
        panel-class="w-11/12 max-w-xl"
        @update:visible="onVisibility"
    >
        <form
            id="inventory-movement-form"
            class="flex w-full flex-col gap-3.5"
            @submit.prevent="emit('submit')"
        >
            <div class="flex flex-col gap-1.5">
                <label
                    class="text-xs font-medium tracking-wide text-slate-400 uppercase"
                >
                    Płyta <span class="text-red-400">*</span>
                </label>
                <BaseDropdown
                    v-model="form.inventory_record_id"
                    :options="recordOptions"
                    searchable
                    search-placeholder="Szukaj płyty..."
                    placeholder="Wybierz płytę"
                    class="w-full"
                    :error="!!form.errors.inventory_record_id"
                />
                <small
                    v-if="form.errors.inventory_record_id"
                    class="text-xs text-red-400"
                >
                    {{ form.errors.inventory_record_id }}
                </small>
            </div>

            <div class="flex flex-col gap-1.5">
                <label
                    class="text-xs font-medium tracking-wide text-slate-400 uppercase"
                >
                    Typ ruchu <span class="text-red-400">*</span>
                </label>
                <BaseDropdown
                    v-model="form.type"
                    :options="typeOptions"
                    placeholder="Wybierz typ"
                    class="w-full"
                    :error="!!form.errors.type"
                />
                <small v-if="form.errors.type" class="text-xs text-red-400">
                    {{ form.errors.type }}
                </small>
            </div>

            <div class="flex flex-col gap-1.5">
                <label
                    class="text-xs font-medium tracking-wide text-slate-400 uppercase"
                >
                    Ilość <span class="text-red-400">*</span>
                </label>
                <BaseInputNumber
                    v-model="form.quantity"
                    :min="1"
                    show-buttons
                    placeholder="1"
                    :error="!!form.errors.quantity"
                />
                <small v-if="form.errors.quantity" class="text-xs text-red-400">
                    {{ form.errors.quantity }}
                </small>
            </div>

            <div class="flex flex-col gap-1.5">
                <label
                    class="text-xs font-medium tracking-wide text-slate-400 uppercase"
                >
                    Powód
                </label>
                <BaseTextArea
                    v-model="form.note"
                    :rows="3"
                    placeholder="Np. dostawa od dystrybutora, sprzedaż, zwrot..."
                    :error="!!form.errors.note"
                />
                <small v-if="form.errors.note" class="text-xs text-red-400">
                    {{ form.errors.note }}
                </small>
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
                form="inventory-movement-form"
                class="inline-flex items-center gap-1.5 rounded-lg border border-sky-400/35 bg-sky-400/15 px-5 py-2 text-sm font-medium text-sky-400 transition-all hover:border-sky-400/50 hover:bg-sky-400/25 disabled:cursor-not-allowed disabled:opacity-45"
                :disabled="form.processing"
            >
                <i v-if="form.processing" class="pi pi-spin pi-spinner" />
                <i v-else class="pi pi-check" />
                {{ form.processing ? "Zapisywanie..." : "Zapisz ruch" }}
            </button>
        </template>
    </BaseDialog>
</template>
