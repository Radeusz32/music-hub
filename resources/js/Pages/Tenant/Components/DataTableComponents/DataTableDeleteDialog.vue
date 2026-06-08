<script setup lang="ts">
defineOptions({ name: "DataTableDeleteDialog" });

withDefaults(
    defineProps<{
        show: boolean;
        target: Record<string, unknown> | null;
        processing?: boolean;
    }>(),
    {
        processing: false,
    },
);

const emit = defineEmits<{
    (e: "cancel"): void;
    (e: "confirm"): void;
}>();

function onVisibility(visible: boolean): void {
    if (!visible) {
        emit("cancel");
    }
}
</script>

<template>
    <BaseDialog
        :visible="show"
        panel-class="w-full max-w-md"
        align="center"
        :mobile-fullscreen="false"
        :show-close="false"
        @update:visible="onVisibility"
    >
        <div class="text-center">
            <div
                class="mx-auto mb-4 flex h-13 w-13 items-center justify-center rounded-full border border-red-400/20 bg-red-400/10 text-2xl text-red-400"
            >
                <i class="pi pi-exclamation-triangle" />
            </div>
            <h3 class="mb-2.5 text-base font-semibold text-slate-200">
                Potwierdź usunięcie
            </h3>
            <p class="mb-6 text-sm leading-relaxed text-slate-400">
                <slot name="text" :row="target">
                    Na pewno chcesz usunąć ten rekord? Operacja jest
                    nieodwracalna.
                </slot>
            </p>
            <div class="flex justify-center gap-3">
                <button
                    type="button"
                    class="rounded-lg border border-slate-400/15 bg-slate-400/5 px-5 py-2 text-sm text-slate-400 transition-colors hover:bg-slate-400/10"
                    @click="emit('cancel')"
                >
                    Anuluj
                </button>
                <button
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-red-400/30 bg-red-400/12 px-5 py-2 text-sm text-red-400 transition-all hover:bg-red-400/20 disabled:cursor-not-allowed disabled:opacity-45"
                    :disabled="processing"
                    @click="emit('confirm')"
                >
                    <i class="pi pi-trash" />
                    Usuń
                </button>
            </div>
        </div>
    </BaseDialog>
</template>
