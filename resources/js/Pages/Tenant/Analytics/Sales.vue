<script setup lang="ts">
import AppLayout from "@/layout/Tenant/AppLayout.vue";
import IndexLayout from "@/layout/Tenant/IndexLayout.vue";
import { computed } from "vue";
import { useMoney } from "@/composables/useMoney";
import { useDate } from "@/composables/useDate";
import StatCard from "./Components/StatCard.vue";
import BarChart from "./Components/BarChart.vue";
import DonutChart from "./Components/DonutChart.vue";
import type {
    FormatSlice,
    Kpi,
    MonthPoint,
    RecentSale,
    TrendPoint,
} from "./analytics.resource";

const props = defineProps<{
    kpis: {
        revenue: Kpi<number>;
        units: Kpi<number>;
        transactions: Kpi<number>;
    };
    monthlyRevenue: MonthPoint[];
    dailyRevenue: TrendPoint[];
    byFormat: FormatSlice[];
    recent: RecentSale[];
}>();

const { formatPrice } = useMoney();
const { formatDateTime } = useDate();

const monthlySeries = computed(() =>
    props.monthlyRevenue.map((point) => ({
        label: point.label,
        value: point.revenue,
    })),
);

const dailySeries = computed(() =>
    props.dailyRevenue.map((point) => ({
        label: point.label,
        value: point.revenue,
    })),
);

const formatSegments = computed(() =>
    props.byFormat.map((slice) => ({
        label: slice.label,
        value: slice.revenue,
        color: slice.color,
    })),
);
</script>

<template>
    <AppLayout>
        <IndexLayout
            title="Analityka - Sprzedaż"
            subtitle="Przychody i trendy sprzedaży"
            icon="pi pi-dollar"
            icon-color="#4ade80"
        >
            <!-- KPIs (ostatnie 30 dni vs poprzednie 30) -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <StatCard
                    label="Przychód (30 dni)"
                    :value="formatPrice(kpis.revenue.value)"
                    icon="pi pi-wallet"
                    color="#4ade80"
                    :delta="kpis.revenue.delta"
                    caption="vs poprz. 30 dni"
                />
                <StatCard
                    label="Sprzedane szt. (30 dni)"
                    :value="String(kpis.units.value)"
                    icon="pi pi-shopping-cart"
                    color="#a78bfa"
                    :delta="kpis.units.delta"
                    caption="vs poprz. 30 dni"
                />
                <StatCard
                    label="Transakcje (30 dni)"
                    :value="String(kpis.transactions.value)"
                    icon="pi pi-receipt"
                    color="#38bdf8"
                    :delta="kpis.transactions.delta"
                    caption="vs poprz. 30 dni"
                />
            </div>

            <!-- Monthly revenue -->
            <section class="panel">
                <h2 class="panel-title">
                    Przychód miesięczny - ostatnie 12 miesięcy
                </h2>
                <BarChart
                    :series="monthlySeries"
                    :format-value="formatPrice"
                    color="#4ade80"
                />
            </section>

            <!-- Daily + format -->
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
                <section class="panel lg:col-span-2">
                    <h2 class="panel-title">
                        Przychód dzienny - ostatnie 30 dni
                    </h2>
                    <BarChart
                        :series="dailySeries"
                        :format-value="formatPrice"
                        :label-every="5"
                        color="#a78bfa"
                    />
                </section>

                <section class="panel">
                    <h2 class="panel-title">Przychód wg formatu</h2>
                    <DonutChart
                        :segments="formatSegments"
                        :format-value="formatPrice"
                        center-caption="formaty"
                        :center-value="String(byFormat.length)"
                    />
                </section>
            </div>

            <!-- Recent sales -->
            <section class="panel">
                <h2 class="panel-title">Ostatnie sprzedaże</h2>
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Płyta</th>
                                <th>Artysta</th>
                                <th class="num">Ilość</th>
                                <th class="num">Cena</th>
                                <th class="num">Razem</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="sale in recent" :key="sale.id">
                                <td>{{ sale.name ?? "-" }}</td>
                                <td class="muted">{{ sale.artist ?? "-" }}</td>
                                <td class="num">{{ sale.quantity }}</td>
                                <td class="num">
                                    {{ formatPrice(sale.sale_price) }}
                                </td>
                                <td class="num strong">
                                    {{ formatPrice(sale.total) }}
                                </td>
                                <td class="muted">
                                    {{ formatDateTime(sale.created_at) }}
                                </td>
                            </tr>
                            <tr v-if="!recent.length">
                                <td colspan="6" class="empty">
                                    Brak sprzedaży.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
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
.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.85rem;
}
.data-table th {
    text-align: left;
    padding: 0.5rem 0.75rem;
    font-size: 0.66rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: rgba(148, 163, 184, 0.5);
    border-bottom: 1px solid rgba(148, 163, 184, 0.12);
}
.data-table td {
    padding: 0.6rem 0.75rem;
    color: #e2e8f0;
    border-bottom: 1px solid rgba(148, 163, 184, 0.06);
}
.data-table .num {
    text-align: right;
    font-variant-numeric: tabular-nums;
}
.data-table .muted {
    color: rgba(148, 163, 184, 0.65);
}
.data-table .strong {
    font-weight: 600;
}
.data-table .empty {
    text-align: center;
    color: rgba(148, 163, 184, 0.5);
    padding: 1.5rem;
}
</style>
