<script setup lang="ts">
import AppLayout from "@/layout/Tenant/AppLayout.vue";
import IndexLayout from "@/layout/Tenant/IndexLayout.vue";
import { computed } from "vue";
import { useMoney } from "@/composables/useMoney";
import StatCard from "./Components/StatCard.vue";
import BarChart from "./Components/BarChart.vue";
import DonutChart from "./Components/DonutChart.vue";
import RankBars from "./Components/RankBars.vue";
import type {
    ArtistSales,
    FormatSlice,
    SalesTotals,
    StockTotals,
    TrendPoint,
} from "./analytics.resource";

const props = defineProps<{
    sales: SalesTotals;
    stock: StockTotals;
    revenueTrend: TrendPoint[];
    salesByFormat: FormatSlice[];
    topArtists: ArtistSales[];
}>();

const { formatPrice } = useMoney();

const trendSeries = computed(() =>
    props.revenueTrend.map((point) => ({
        label: point.label,
        value: point.revenue,
    })),
);

const formatSegments = computed(() =>
    props.salesByFormat.map((slice) => ({
        label: slice.label,
        value: slice.units,
        color: slice.color,
    })),
);

const artistItems = computed(() =>
    props.topArtists.map((artist) => ({
        label: artist.artist,
        value: artist.revenue,
        sublabel: `${artist.units} szt. • ${artist.titles} tyt.`,
    })),
);
</script>

<template>
    <AppLayout>
        <IndexLayout
            title="Analityka — Podsumowanie"
            subtitle="Najważniejsze wskaźniki sprzedaży i magazynu"
            icon="pi pi-chart-pie"
            icon-color="#a78bfa"
        >
            <!-- KPIs -->
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <StatCard
                    label="Przychód"
                    :value="formatPrice(sales.revenue)"
                    icon="pi pi-wallet"
                    color="#4ade80"
                />
                <StatCard
                    label="Sprzedane szt."
                    :value="String(sales.units)"
                    icon="pi pi-shopping-cart"
                    color="#a78bfa"
                />
                <StatCard
                    label="Transakcje"
                    :value="String(sales.transactions)"
                    icon="pi pi-receipt"
                    color="#38bdf8"
                />
                <StatCard
                    label="Śr. wartość"
                    :value="formatPrice(sales.avg)"
                    icon="pi pi-chart-line"
                    color="#fbbf24"
                />
                <StatCard
                    label="Wartość magazynu"
                    :value="formatPrice(stock.value)"
                    icon="pi pi-box"
                    color="#f97316"
                />
                <StatCard
                    label="Tytuły"
                    :value="String(stock.titles)"
                    icon="pi pi-database"
                    color="#60a5fa"
                />
                <StatCard
                    label="Niski stan"
                    :value="String(stock.lowStock)"
                    icon="pi pi-exclamation-triangle"
                    color="#fbbf24"
                    caption="≤ 3 szt."
                />
                <StatCard
                    label="Brak na stanie"
                    :value="String(stock.outOfStock)"
                    icon="pi pi-times-circle"
                    color="#f87171"
                />
            </div>

            <!-- Trend + format split -->
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
                <section class="panel lg:col-span-2">
                    <h2 class="panel-title">Przychód — ostatnie 30 dni</h2>
                    <BarChart
                        :series="trendSeries"
                        :format-value="formatPrice"
                        :label-every="5"
                        color="#a78bfa"
                    />
                </section>

                <section class="panel">
                    <h2 class="panel-title">Sprzedaż wg formatu (szt.)</h2>
                    <DonutChart
                        :segments="formatSegments"
                        center-caption="szt."
                    />
                </section>
            </div>

            <!-- Top artists -->
            <section class="panel">
                <h2 class="panel-title">Top artyści wg przychodu</h2>
                <RankBars
                    :items="artistItems"
                    :format-value="formatPrice"
                    color="#4ade80"
                />
            </section>
        </IndexLayout>
    </AppLayout>
</template>

<style scoped>
.panel {
    background: rgba(2, 6, 23, 0.4);
    border: 1px solid rgba(56, 189, 248, 0.1);
    border-radius: 16px;
    padding: 1.5rem;
}
.panel-title {
    font-size: 0.72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: rgba(148, 163, 184, 0.55);
    margin: 0 0 1.25rem;
}
</style>
