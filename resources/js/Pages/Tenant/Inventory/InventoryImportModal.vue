<script setup lang="ts">
import { ref, watch } from "vue";
import type { InertiaForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import FileUpload from "@/Components/FileUpload.vue";

defineOptions({ name: "InventoryImportModal" });

interface ImportFormData {
    file: File | null;
}

const props = defineProps<{
    show: boolean;
    form: InertiaForm<ImportFormData>;
}>();

const emit = defineEmits<{
    (e: "close"): void;
    (e: "submit"): void;
}>();

const fileUploadRef = ref<InstanceType<typeof FileUpload> | null>(null);

function onFileSelected(e: { files: File[] }): void {
    props.form.file = e.files?.[0] ?? null;
}

function onVisibility(visible: boolean): void {
    if (!visible) {
        emit("close");
    }
}

watch(
    () => props.show,
    (open) => {
        if (!open) {
            fileUploadRef.value?.clearFile();
        }
    },
);
</script>

<template>
    <BaseDialog
        :visible="show"
        panel-class="w-11/12 max-w-xl"
        @update:visible="onVisibility"
    >
        <template #header>
            <h2
                class="flex items-center gap-2 text-base font-semibold text-slate-200"
            >
                <i class="pi pi-upload text-sky-400" />
                Import z Excel
            </h2>
        </template>

        <!-- Import instructions -->
        <div
            class="mb-6 rounded-xl border border-sky-400/10 bg-slate-950/40 p-5"
        >
            <h3
                class="mb-3 flex items-center gap-2 text-sm font-semibold text-sky-400"
            >
                <i class="pi pi-info-circle" />
                Instrukcja importu
            </h3>
            <ol
                class="mb-4 list-decimal space-y-2 pl-5 text-sm leading-relaxed text-slate-400"
            >
                <li>
                    <strong class="font-semibold text-slate-300"
                        >Pobierz szablon</strong
                    >
                    - Kliknij poniższy link, aby pobrać wzorcowy plik Excel z
                    prawidłową strukturą kolumn
                </li>
                <li>
                    <strong class="font-semibold text-slate-300"
                        >Wypełnij dane</strong
                    >
                    - Uzupełnij plik danymi płyt. Zachowaj nazwy kolumn i
                    kolejność
                </li>
                <li>
                    <strong class="font-semibold text-slate-300"
                        >Wymagane pola:</strong
                    >
                    Tytuł, Artysta, Gatunek, Format, Stan, Ilość
                </li>
                <li>
                    <strong class="font-semibold text-slate-300"
                        >Wgraj plik</strong
                    >
                    - Wybierz wypełniony plik (.xlsx, .xls lub .csv) poniżej
                </li>
            </ol>
            <a
                :href="route('tenant.inventory.records.export-template')"
                class="inline-flex items-center gap-1.5 rounded-lg border border-sky-400/30 bg-sky-400/10 px-4 py-2 text-sm font-medium text-sky-400 transition-all hover:border-sky-400/45 hover:bg-sky-400/20"
                download
            >
                <i class="pi pi-download" />
                Pobierz szablon Excel
            </a>
        </div>

        <div class="flex flex-col gap-1.5">
            <label
                class="text-xs font-medium tracking-wide text-slate-400 uppercase"
            >
                Plik do importu <span class="text-red-400">*</span>
            </label>
            <FileUpload
                ref="fileUploadRef"
                accept=".xlsx,.xls,.csv"
                :max-file-size="10000000"
                :disabled="form.processing"
                @select="onFileSelected"
            />
            <small v-if="form.errors.file" class="text-xs text-red-400">
                {{ form.errors.file }}
            </small>
        </div>

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
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg border border-sky-400/35 bg-sky-400/15 px-5 py-2 text-sm font-medium text-sky-400 transition-all hover:border-sky-400/50 hover:bg-sky-400/25 disabled:cursor-not-allowed disabled:opacity-45"
                :disabled="form.processing || !form.file"
                @click="emit('submit')"
            >
                <i v-if="form.processing" class="pi pi-spin pi-spinner" />
                <i v-else class="pi pi-upload" />
                {{ form.processing ? "Importowanie..." : "Importuj" }}
            </button>
        </template>
    </BaseDialog>
</template>
