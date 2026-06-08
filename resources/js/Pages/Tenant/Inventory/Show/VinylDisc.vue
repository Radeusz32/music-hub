<script setup lang="ts">
defineOptions({ name: "VinylDisc" });

defineProps<{
    format: string;
    color: string;
}>();
</script>

<template>
    <div class="vinyl-disc">
        <svg viewBox="0 0 200 200" class="vinyl-svg">
            <circle
                cx="100"
                cy="100"
                r="96"
                fill="#0d1424"
                stroke="rgba(56,189,248,0.12)"
                stroke-width="2"
            />
            <circle cx="100" cy="100" r="80" fill="#0a0f1e" />
            <circle
                cx="100"
                cy="100"
                r="64"
                fill="#080d18"
                :style="{
                    color: color,
                    borderColor: `${color}40`,
                    background: `${color}12`,
                }"
            />
            <circle cx="100" cy="100" r="48" fill="#0d1424" />
            <circle cx="100" cy="100" r="32" fill="#0a0f1e" />
            <circle cx="100" cy="100" r="18" fill="#0d1424" />
            <circle cx="100" cy="100" r="8" fill="#38bdf8" opacity="0.6" />
            <circle cx="100" cy="100" r="4" fill="#0a0f1e" />
            <circle
                v-for="gr in [74, 88, 56, 42]"
                :key="gr"
                cx="100"
                cy="100"
                :r="gr"
                fill="none"
                stroke="rgba(56,189,248,0.04)"
                stroke-width="1"
            />
        </svg>
        <span
            class="vinyl-format-tag"
            :style="{
                color: color,
                borderColor: `${color}40`,
                background: `${color}12`,
            }"
        >
            {{ format }}
        </span>
    </div>
</template>

<style scoped>
.vinyl-disc {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    background: #070b16;
}

.vinyl-svg {
    width: 160px;
    height: 160px;
    filter: drop-shadow(0 0 18px rgba(56, 189, 248, 0.08));
    animation: vinyl-spin 18s linear infinite;
}

@keyframes vinyl-spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.vinyl-format-tag {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 0.65rem;
    font-weight: 700;
    padding: 0.2rem 0.45rem;
    border: 1px solid;
    border-radius: 4px;
    white-space: nowrap;
    letter-spacing: 0.05em;
    margin-top: 10px;
}
</style>
