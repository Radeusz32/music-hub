<script setup lang="ts">
import { computed, useSlots, watch } from "vue";

defineOptions({ name: "BaseDialog" });

const props = withDefaults(
    defineProps<{
        visible: boolean;
        title?: string;
        /**
         * Tailwind classes controlling the panel box - width, max-width and any
         * responsive breakpoints. Manage sizing here just like the reference
         * example, e.g. `panel-class="w-11/12 max-w-5xl"`.
         */
        panelClass?: string;
        /** Vertical placement of the panel within the viewport. */
        align?: "top" | "center";
        /** Expand the panel to a full-screen sheet on small screens. */
        mobileFullscreen?: boolean;
        closeOnBackdrop?: boolean;
        showClose?: boolean;
    }>(),
    {
        title: "",
        panelClass: "w-full max-w-2xl",
        align: "top",
        mobileFullscreen: true,
        closeOnBackdrop: true,
        showClose: true,
    },
);

const emit = defineEmits<{
    (e: "update:visible", value: boolean): void;
}>();

const slots = useSlots();

const hasHeader = computed(
    () => !!props.title || !!slots.header || props.showClose,
);

function close(): void {
    emit("update:visible", false);
}

function onBackdrop(): void {
    if (props.closeOnBackdrop) {
        close();
    }
}

function onKeydown(e: KeyboardEvent): void {
    if (e.key === "Escape") {
        close();
    }
}

watch(
    () => props.visible,
    (open) => {
        if (typeof document === "undefined") {
            return;
        }
        document.body.style.overflow = open ? "hidden" : "";
    },
);
</script>

<template>
    <Teleport to="body">
        <Transition name="modal-fade">
            <div
                v-if="visible"
                class="dialog-backdrop"
                :class="{
                    'dialog-backdrop--center': align === 'center',
                    'dialog--mobile-full': mobileFullscreen,
                }"
                role="dialog"
                aria-modal="true"
                tabindex="-1"
                @click.self="onBackdrop"
                @keydown="onKeydown"
            >
                <div class="dialog-panel" :class="panelClass">
                    <!-- Header -->
                    <div v-if="hasHeader" class="dialog-header">
                        <slot name="header">
                            <h2 class="dialog-title">{{ title }}</h2>
                        </slot>
                        <button
                            v-if="showClose"
                            type="button"
                            class="dialog-close"
                            aria-label="Zamknij"
                            @click="close"
                        >
                            <i class="pi pi-times" />
                        </button>
                    </div>

                    <!-- Body: layout is managed by the consumer via Tailwind -->
                    <div class="dialog-body">
                        <slot />
                    </div>

                    <!-- Footer -->
                    <div v-if="slots.footer" class="dialog-footer">
                        <slot name="footer" />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.dialog-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.75);
    backdrop-filter: blur(6px);
    z-index: 9990;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: 1.5rem;
    overflow-y: auto;
    overflow-x: hidden;
    -webkit-overflow-scrolling: touch;
    outline: none;
}

.dialog-backdrop--center {
    align-items: center;
}

/* No width here - the panel box (width / max-width / breakpoints) is driven by
   the `panelClass` Tailwind classes passed in by the consumer. */
.dialog-panel {
    background: linear-gradient(145deg, #0d1728 0%, #080e1c 100%);
    border: 1px solid rgba(56, 189, 248, 0.13);
    border-radius: 18px;
    max-height: calc(100vh - 3rem);
    box-shadow:
        0 30px 70px rgba(0, 0, 0, 0.8),
        0 0 60px rgba(56, 189, 248, 0.04);
    margin: auto;
    display: flex;
    flex-direction: column;
}

/* ── Header ── */
.dialog-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.2rem 1.6rem;
    border-bottom: 1px solid rgba(56, 189, 248, 0.08);
    flex-shrink: 0;
}

.dialog-title {
    font-size: 1rem;
    font-weight: 600;
    color: #e2e8f0;
    margin: 0;
}

.dialog-close {
    width: 30px;
    height: 30px;
    border-radius: 7px;
    border: 1px solid rgba(148, 163, 184, 0.11);
    background: rgba(148, 163, 184, 0.05);
    color: #94a3b8;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    transition: all 0.15s;
    flex-shrink: 0;
}

.dialog-close:hover {
    background: rgba(248, 113, 113, 0.1);
    border-color: rgba(248, 113, 113, 0.22);
    color: #f87171;
}

/* ── Body ── */
.dialog-body {
    padding: 1.6rem;
    overflow-y: auto;
    flex: 1;
    min-height: 0;
}

/* ── Footer ── */
.dialog-footer {
    display: flex;
    gap: 0.7rem;
    justify-content: flex-end;
    padding: 1rem 1.6rem;
    border-top: 1px solid rgba(56, 189, 248, 0.07);
    flex-shrink: 0;
}

/* ── Mobile: full-screen sheet (opt-in via mobileFullscreen) ── */
@media (max-width: 640px) {
    .dialog--mobile-full {
        padding: 0;
    }

    .dialog--mobile-full .dialog-panel {
        max-height: 100vh;
        min-height: 100vh;
        border-radius: 0;
        margin: 0;
    }

    .dialog--mobile-full .dialog-header {
        position: sticky;
        top: 0;
        background: linear-gradient(145deg, #0d1728 0%, #080e1c 100%);
        z-index: 10;
        padding: 1rem;
    }

    .dialog--mobile-full .dialog-body {
        padding: 1rem;
    }

    .dialog--mobile-full .dialog-footer {
        position: sticky;
        bottom: 0;
        background: linear-gradient(145deg, #0d1728 0%, #080e1c 100%);
        padding: 1rem;
        border-top: 1px solid rgba(56, 189, 248, 0.12);
        box-shadow: 0 -10px 30px rgba(0, 0, 0, 0.3);
    }
}

/* ── Transition ── */
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.22s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}
.modal-fade-enter-active .dialog-panel,
.modal-fade-leave-active .dialog-panel {
    transition: transform 0.22s ease;
}
.modal-fade-enter-from .dialog-panel,
.modal-fade-leave-to .dialog-panel {
    transform: scale(0.96) translateY(-10px);
}
</style>
