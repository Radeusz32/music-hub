<script setup lang="ts">
import { onBeforeUnmount, ref } from "vue";

const props = withDefaults(
    defineProps<{
        content: string;
        position?: "top" | "bottom" | "left" | "right";
        delay?: number;
    }>(),
    {
        position: "top",
        delay: 0,
    },
);

const GAP = 9;

const hostRef = ref<HTMLElement | null>(null);
const visible = ref(false);
const coords = ref<{ top: number; left: number }>({ top: 0, left: 0 });

let showTimer: ReturnType<typeof setTimeout> | null = null;

function updatePosition(): void {
    const host = hostRef.value;
    if (!host) {
        return;
    }

    const r = host.getBoundingClientRect();

    switch (props.position) {
        case "top":
            coords.value = { top: r.top - GAP, left: r.left + r.width / 2 };
            break;
        case "bottom":
            coords.value = { top: r.bottom + GAP, left: r.left + r.width / 2 };
            break;
        case "left":
            coords.value = { top: r.top + r.height / 2, left: r.left - GAP };
            break;
        case "right":
            coords.value = { top: r.top + r.height / 2, left: r.right + GAP };
            break;
    }
}

function show(): void {
    const reveal = (): void => {
        updatePosition();
        visible.value = true;
        window.addEventListener("scroll", onReposition, true);
        window.addEventListener("resize", onReposition);
    };

    if (props.delay > 0) {
        showTimer = setTimeout(reveal, props.delay);
    } else {
        reveal();
    }
}

function hide(): void {
    if (showTimer) {
        clearTimeout(showTimer);
        showTimer = null;
    }
    visible.value = false;
    window.removeEventListener("scroll", onReposition, true);
    window.removeEventListener("resize", onReposition);
}

function onReposition(): void {
    if (visible.value) {
        updatePosition();
    }
}

onBeforeUnmount(hide);
</script>

<template>
    <span
        ref="hostRef"
        class="tooltip-host"
        @mouseenter="show"
        @mouseleave="hide"
    >
        <slot />

        <Teleport to="body">
            <Transition name="tt">
                <span
                    v-if="visible"
                    class="tooltip-bubble"
                    :class="`tooltip-bubble--${position}`"
                    :style="{
                        top: `${coords.top}px`,
                        left: `${coords.left}px`,
                    }"
                >
                    {{ content }}
                    <span class="tooltip-arrow" />
                </span>
            </Transition>
        </Teleport>
    </span>
</template>

<style scoped>
.tooltip-host {
    position: relative;
    display: inline-flex;
}

.tooltip-bubble {
    position: fixed;
    z-index: 9999;
    background: rgba(10, 15, 30, 0.95);
    border: 1px solid rgba(56, 189, 248, 0.18);
    color: #e2e8f0;
    font-size: 0.7rem;
    font-weight: 500;
    line-height: 1;
    padding: 0.3rem 0.6rem;
    border-radius: 5px;
    white-space: nowrap;
    pointer-events: none;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4);
}

/* ── Anchor transforms (center the bubble around its fixed coordinate) ── */
.tooltip-bubble--top {
    transform: translate(-50%, -100%);
}

.tooltip-bubble--bottom {
    transform: translate(-50%, 0);
}

.tooltip-bubble--left {
    transform: translate(-100%, -50%);
}

.tooltip-bubble--right {
    transform: translate(0, -50%);
}

/* ── Arrow ── */
.tooltip-arrow {
    position: absolute;
    width: 0;
    height: 0;
    border: 4px solid transparent;
}

.tooltip-bubble--top .tooltip-arrow {
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    border-top-color: rgba(56, 189, 248, 0.18);
}

.tooltip-bubble--bottom .tooltip-arrow {
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    border-bottom-color: rgba(56, 189, 248, 0.18);
}

.tooltip-bubble--left .tooltip-arrow {
    top: 50%;
    left: 100%;
    transform: translateY(-50%);
    border-left-color: rgba(56, 189, 248, 0.18);
}

.tooltip-bubble--right .tooltip-arrow {
    top: 50%;
    right: 100%;
    transform: translateY(-50%);
    border-right-color: rgba(56, 189, 248, 0.18);
}

/* ── Fade transition (opacity only — transform is reserved for anchoring) ── */
.tt-enter-active,
.tt-leave-active {
    transition: opacity 0.15s ease;
}

.tt-enter-from,
.tt-leave-to {
    opacity: 0;
}
</style>
