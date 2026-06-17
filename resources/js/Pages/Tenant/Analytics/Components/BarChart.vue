<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, watch } from "vue";
import Chart from "chart.js/auto";
import type { ChartConfiguration, TooltipItem } from "chart.js";

defineOptions({ name: "AnalyticsBarChart" });

interface Point {
    label: string;
    value: number;
}

const props = withDefaults(
    defineProps<{
        series: Point[];
        color?: string;
        formatValue?: (value: number) => string;
        height?: number;
    }>(),
    { color: "#a78bfa", height: 220 },
);

const canvas = ref<HTMLCanvasElement | null>(null);
let chart: Chart<"bar"> | null = null;

function buildConfig(): ChartConfiguration<"bar"> {
    return {
        type: "bar",
        data: {
            labels: props.series.map((point) => point.label),
            datasets: [
                {
                    data: props.series.map((point) => point.value),
                    backgroundColor: props.color,
                    borderRadius: 4,
                    borderSkipped: false,
                    maxBarThickness: 48,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (context: TooltipItem<"bar">): string => {
                            const value = context.parsed.y ?? 0;
                            return props.formatValue
                                ? props.formatValue(value)
                                : String(value);
                        },
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        color: "rgba(148,163,184,0.55)",
                        font: { size: 10 },
                        maxRotation: 0,
                        autoSkip: true,
                        autoSkipPadding: 12,
                    },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: "rgba(148,163,184,0.08)" },
                    ticks: {
                        color: "rgba(148,163,184,0.55)",
                        font: { size: 10 },
                        callback: (value: number | string): string =>
                            props.formatValue
                                ? props.formatValue(Number(value))
                                : String(value),
                    },
                },
            },
        },
    };
}

function render(): void {
    if (!canvas.value) {
        return;
    }
    chart?.destroy();
    chart = new Chart(canvas.value, buildConfig());
}

onMounted(render);

watch(
    () => props.series,
    () => render(),
    { deep: true },
);

onBeforeUnmount(() => {
    chart?.destroy();
    chart = null;
});
</script>

<template>
    <div class="chart-wrap" :style="{ height: `${height}px` }">
        <canvas v-if="series.length" ref="canvas" />
        <p v-else class="chart-empty">Brak danych do wyświetlenia.</p>
    </div>
</template>

<style scoped>
.chart-wrap {
    position: relative;
    width: 100%;
}
.chart-empty {
    padding: 2rem;
    text-align: center;
    font-size: 0.85rem;
    color: rgba(148, 163, 184, 0.5);
}
</style>
