<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, watch } from "vue";
import Chart from "chart.js/auto";
import type { ChartConfiguration, Plugin, TooltipItem } from "chart.js";

defineOptions({ name: "AnalyticsDonutChart" });

interface Segment {
    label: string;
    value: number;
    color: string;
}

const props = withDefaults(
    defineProps<{
        segments: Segment[];
        formatValue?: (value: number) => string;
        size?: number;
        thickness?: number;
        centerValue?: string;
        centerCaption?: string;
    }>(),
    { size: 200, thickness: 26 },
);

const canvas = ref<HTMLCanvasElement | null>(null);
let chart: Chart<"doughnut"> | null = null;

function total(): number {
    return props.segments.reduce((sum, segment) => sum + segment.value, 0);
}

function cutout(): string {
    return `${Math.max(0, ((props.size / 2 - props.thickness) / (props.size / 2)) * 100)}%`;
}

const centerTextPlugin: Plugin<"doughnut"> = {
    id: "analyticsCenterText",
    afterDraw(instance): void {
        if (!props.centerValue && !props.centerCaption) {
            return;
        }
        const { ctx } = instance;
        const meta = instance.getDatasetMeta(0);
        const arc = meta.data[0] as unknown as { x: number; y: number };
        if (!arc) {
            return;
        }

        ctx.save();
        ctx.textAlign = "center";
        ctx.textBaseline = "middle";
        ctx.fillStyle = "#e2e8f0";
        ctx.font = "700 18px sans-serif";
        ctx.fillText(
            props.centerValue ?? String(total()),
            arc.x,
            props.centerCaption ? arc.y - 6 : arc.y,
        );

        if (props.centerCaption) {
            ctx.fillStyle = "rgba(148,163,184,0.6)";
            ctx.font = "600 10px sans-serif";
            ctx.fillText(props.centerCaption.toUpperCase(), arc.x, arc.y + 12);
        }
        ctx.restore();
    },
};

function buildConfig(): ChartConfiguration<"doughnut"> {
    return {
        type: "doughnut",
        data: {
            labels: props.segments.map((segment) => segment.label),
            datasets: [
                {
                    data: props.segments.map((segment) => segment.value),
                    backgroundColor: props.segments.map(
                        (segment) => segment.color,
                    ),
                    borderWidth: 0,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: cutout(),
            plugins: {
                legend: {
                    position: "right",
                    labels: {
                        color: "rgba(203,213,225,0.8)",
                        usePointStyle: true,
                        pointStyle: "rectRounded",
                        boxWidth: 10,
                        padding: 14,
                        font: { size: 12 },
                    },
                },
                tooltip: {
                    callbacks: {
                        label: (context: TooltipItem<"doughnut">): string => {
                            const value = props.formatValue
                                ? props.formatValue(context.parsed)
                                : String(context.parsed);
                            return `${context.label}: ${value}`;
                        },
                    },
                },
            },
        },
        plugins: [centerTextPlugin],
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
    () => props.segments,
    () => render(),
    { deep: true },
);

onBeforeUnmount(() => {
    chart?.destroy();
    chart = null;
});
</script>

<template>
    <div class="chart-wrap" :style="{ height: `${size}px` }">
        <canvas v-if="total() > 0" ref="canvas" />
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
