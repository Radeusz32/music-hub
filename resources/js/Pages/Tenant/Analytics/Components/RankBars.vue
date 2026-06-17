<script setup lang="ts">
import { computed } from "vue";

defineOptions({ name: "AnalyticsRankBars" });

interface RankItem {
    label: string;
    value: number;
    sublabel?: string;
}

const props = withDefaults(
    defineProps<{
        items: RankItem[];
        color?: string;
        formatValue?: (value: number) => string;
    }>(),
    { color: "#a78bfa" },
);

const max = computed(() =>
    Math.max(1, ...props.items.map((item) => item.value)),
);

function format(value: number): string {
    return props.formatValue ? props.formatValue(value) : String(value);
}
</script>

<template>
    <ol v-if="items.length" class="rank">
        <li v-for="(item, index) in items" :key="index" class="rank-row">
            <span class="rank-pos">{{ index + 1 }}</span>
            <div class="rank-body">
                <div class="rank-top">
                    <span class="rank-label">{{ item.label }}</span>
                    <span class="rank-value">{{ format(item.value) }}</span>
                </div>
                <div class="rank-track">
                    <div
                        class="rank-fill"
                        :style="{
                            width: `${(item.value / max) * 100}%`,
                            background: color,
                        }"
                    />
                </div>
                <span v-if="item.sublabel" class="rank-sub">{{
                    item.sublabel
                }}</span>
            </div>
        </li>
    </ol>
    <p v-else class="rank-empty">Brak danych do wyświetlenia.</p>
</template>

<style scoped>
.rank {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin: 0;
    padding: 0;
    list-style: none;
}
.rank-row {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}
.rank-pos {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 1.6rem;
    width: 1.6rem;
    flex-shrink: 0;
    border-radius: 7px;
    background: rgba(148, 163, 184, 0.08);
    color: rgba(148, 163, 184, 0.7);
    font-size: 0.75rem;
    font-weight: 700;
}
.rank-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    min-width: 0;
}
.rank-top {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 0.75rem;
}
.rank-label {
    font-size: 0.85rem;
    color: #e2e8f0;
    font-weight: 500;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.rank-value {
    font-size: 0.82rem;
    font-weight: 600;
    color: #cbd5e1;
    flex-shrink: 0;
}
.rank-track {
    height: 0.4rem;
    border-radius: 3px;
    background: rgba(148, 163, 184, 0.08);
    overflow: hidden;
}
.rank-fill {
    height: 100%;
    border-radius: 3px;
    transition: width 0.3s ease;
}
.rank-sub {
    font-size: 0.72rem;
    color: rgba(148, 163, 184, 0.5);
}
.rank-empty {
    padding: 2rem;
    text-align: center;
    font-size: 0.85rem;
    color: rgba(148, 163, 184, 0.5);
}
</style>
