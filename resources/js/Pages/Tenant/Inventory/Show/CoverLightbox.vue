<script setup lang="ts">
import { nextTick, ref, watch } from "vue";

defineOptions({ name: "CoverLightbox" });

const props = defineProps<{
    show: boolean;
    src: string | null;
    alt: string;
}>();

const emit = defineEmits<{
    (e: "close"): void;
}>();

const lightboxRef = ref<HTMLElement | null>(null);

watch(
    () => props.show,
    (open) => {
        if (open) {
            nextTick(() => lightboxRef.value?.focus());
        }
    },
);

function onKeydown(e: KeyboardEvent): void {
    if (e.key === "Escape") emit("close");
}
</script>

<template>
    <Teleport to="body">
        <Transition name="lightbox-fade">
            <div
                v-if="show && src"
                ref="lightboxRef"
                class="lightbox-backdrop"
                tabindex="0"
                @click.self="emit('close')"
                @keydown="onKeydown"
            >
                <button
                    type="button"
                    class="lightbox-close"
                    @click="emit('close')"
                >
                    <i class="pi pi-times" />
                </button>
                <img :src="src" :alt="alt" class="lightbox-img" @click.stop />
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.lightbox-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.92);
    backdrop-filter: blur(8px);
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    outline: none;
}

.lightbox-img {
    max-width: 90vw;
    max-height: 90vh;
    object-fit: contain;
    border-radius: 8px;
    box-shadow: 0 30px 80px rgba(0, 0, 0, 0.8);
    user-select: none;
}

.lightbox-close {
    position: fixed;
    top: 1.2rem;
    right: 1.2rem;
    width: 40px;
    height: 40px;
    border-radius: 10px;
    border: 1px solid rgba(255, 255, 255, 0.15);
    background: rgba(255, 255, 255, 0.08);
    color: rgba(255, 255, 255, 0.8);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    transition: all 0.15s;
}

.lightbox-close:hover {
    background: rgba(248, 113, 113, 0.2);
    border-color: rgba(248, 113, 113, 0.4);
    color: #f87171;
}

.lightbox-fade-enter-active,
.lightbox-fade-leave-active {
    transition: opacity 0.2s ease;
}
.lightbox-fade-enter-from,
.lightbox-fade-leave-to {
    opacity: 0;
}
.lightbox-fade-enter-active .lightbox-img,
.lightbox-fade-leave-active .lightbox-img {
    transition: transform 0.2s ease;
}
.lightbox-fade-enter-from .lightbox-img,
.lightbox-fade-leave-to .lightbox-img {
    transform: scale(0.93);
}
</style>
