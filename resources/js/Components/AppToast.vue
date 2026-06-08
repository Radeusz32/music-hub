<script setup lang="ts">
import { useToast } from "@/composables/useToast";

const { toasts, dismiss } = useToast();
</script>

<template>
    <Teleport to="body">
        <div class="fixed right-4 top-4 z-[9999] flex w-84 flex-col gap-3">
            <TransitionGroup
                enter-active-class="transition-all duration-350 ease-out"
                enter-from-class="translate-x-full opacity-0 scale-95"
                enter-to-class="translate-x-0 opacity-100 scale-100"
                leave-active-class="transition-all duration-200 ease-in absolute w-full"
                leave-from-class="translate-x-0 opacity-100"
                leave-to-class="translate-x-full opacity-0"
            >
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    class="toast-card relative flex items-start gap-3 overflow-hidden rounded-xl border px-4 py-3.5"
                    :class="
                        toast.type === 'success'
                            ? 'toast-card--success border-green-400/30 bg-[#0c1f0e]/60 text-green-200'
                            : 'toast-card--error border-red-400/30 bg-[#1f0c0c]/60 text-red-200'
                    "
                >
                    <!-- glow bar left -->
                    <span
                        class="absolute inset-y-0 left-0 w-[3px] rounded-r-full"
                        :class="
                            toast.type === 'success'
                                ? 'bg-green-400'
                                : 'bg-red-400'
                        "
                    />

                    <!-- icon -->
                    <i
                        class="mt-0.5 shrink-0 text-lg"
                        :class="
                            toast.type === 'success'
                                ? 'pi pi-check-circle text-green-400'
                                : 'pi pi-times-circle text-red-400'
                        "
                    />

                    <!-- message -->
                    <span class="flex-1 text-sm font-medium leading-snug tracking-wide">
                        {{ toast.message }}
                    </span>

                    <!-- dismiss -->
                    <button
                        class="mt-0.5 shrink-0 opacity-40 transition-opacity hover:opacity-100"
                        @click="dismiss(toast.id)"
                    >
                        <i class="pi pi-times text-xs" />
                    </button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<style scoped>
.toast-card {
    box-shadow:
        0 0 0 1px rgba(255, 255, 255, 0.04),
        0 8px 32px rgba(0, 0, 0, 0.6),
        0 2px 8px rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(12px);
}

.toast-card--success {
    box-shadow:
        0 0 0 1px rgba(255, 255, 255, 0.04),
        0 8px 32px rgba(0, 0, 0, 0.6),
        0 0 24px rgba(74, 222, 128, 0.08);
}

.toast-card--error {
    box-shadow:
        0 0 0 1px rgba(255, 255, 255, 0.04),
        0 8px 32px rgba(0, 0, 0, 0.6),
        0 0 24px rgba(248, 113, 113, 0.08);
}
</style>
