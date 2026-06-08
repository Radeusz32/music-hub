<script setup lang="ts">
withDefaults(
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
</script>

<template>
    <div class="tooltip-host">
        <slot />
        <span class="tooltip-bubble" :class="`tooltip-bubble--${position}`">
            {{ content }}
            <span class="tooltip-arrow" />
        </span>
    </div>
</template>

<style scoped>
.tooltip-host {
    position: relative;
    display: inline-flex;
}

.tooltip-bubble {
    position: absolute;
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

    opacity: 0;
    visibility: hidden;
    transition:
        opacity 0.15s ease,
        transform 0.15s ease,
        visibility 0.15s;
}

.tooltip-host:hover .tooltip-bubble {
    opacity: 1;
    visibility: visible;
    transform: translate(var(--tt-x, 0), var(--tt-y, 0)) !important;
}

/* ── Top (default) ── */
.tooltip-bubble--top {
    bottom: calc(100% + 7px);
    left: 50%;
    --tt-x: -50%;
    --tt-y: 0;
    transform: translateX(-50%) translateY(4px);
}

/* ── Bottom ── */
.tooltip-bubble--bottom {
    top: calc(100% + 7px);
    left: 50%;
    --tt-x: -50%;
    --tt-y: 0;
    transform: translateX(-50%) translateY(-4px);
}

/* ── Left ── */
.tooltip-bubble--left {
    top: 50%;
    right: calc(100% + 7px);
    --tt-x: 0;
    --tt-y: -50%;
    transform: translateY(-50%) translateX(4px);
}

/* ── Right ── */
.tooltip-bubble--right {
    top: 50%;
    left: calc(100% + 7px);
    --tt-x: 0;
    --tt-y: -50%;
    transform: translateY(-50%) translateX(-4px);
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
</style>
