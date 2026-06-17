<script setup lang="ts">
defineOptions({ name: "AnalyticsStatCard" });

withDefaults(
    defineProps<{
        label: string;
        value: string;
        icon?: string;
        color?: string;
        delta?: number | null;
        caption?: string;
    }>(),
    { color: "#a78bfa" },
);
</script>

<template>
    <div class="stat-card">
        <div class="stat-head">
            <span class="stat-label">{{ label }}</span>
            <span
                v-if="icon"
                class="stat-icon"
                :style="{ color, background: `${color}1f` }"
            >
                <i :class="icon" />
            </span>
        </div>
        <div class="stat-value">{{ value }}</div>
        <div v-if="(delta ?? null) !== null || caption" class="stat-foot">
            <span
                v-if="(delta ?? null) !== null"
                class="stat-delta"
                :class="
                    (delta ?? 0) >= 0 ? 'stat-delta--up' : 'stat-delta--down'
                "
            >
                <i
                    :class="
                        (delta ?? 0) >= 0
                            ? 'pi pi-arrow-up-right'
                            : 'pi pi-arrow-down-right'
                    "
                />
                {{ Math.abs(delta ?? 0) }}%
            </span>
            <span v-if="caption" class="stat-caption">{{ caption }}</span>
        </div>
    </div>
</template>

<style scoped>
.stat-card {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    background: rgba(2, 6, 23, 0.4);
    border: 1px solid rgba(56, 189, 248, 0.1);
    border-radius: 16px;
    padding: 1.25rem;
}
.stat-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.5rem;
}
.stat-label {
    font-size: 0.68rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: rgba(148, 163, 184, 0.6);
}
.stat-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 2rem;
    width: 2rem;
    border-radius: 9px;
    font-size: 0.85rem;
    flex-shrink: 0;
}
.stat-value {
    font-size: 1.55rem;
    font-weight: 700;
    color: #e2e8f0;
    line-height: 1.1;
}
.stat-foot {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    flex-wrap: wrap;
}
.stat-delta {
    display: inline-flex;
    align-items: center;
    gap: 0.2rem;
    font-size: 0.74rem;
    font-weight: 600;
    padding: 0.1rem 0.45rem;
    border-radius: 6px;
}
.stat-delta--up {
    color: #4ade80;
    background: rgba(74, 222, 128, 0.12);
}
.stat-delta--down {
    color: #f87171;
    background: rgba(248, 113, 113, 0.12);
}
.stat-caption {
    font-size: 0.72rem;
    color: rgba(148, 163, 184, 0.5);
}
</style>
