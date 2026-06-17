<script setup lang="ts">
import AppLayout from "@/layout/Tenant/AppLayout.vue";
import IndexLayout from "@/layout/Tenant/IndexLayout.vue";
import { computed } from "vue";
import { useMoney } from "@/composables/useMoney";
import BarChart from "./Components/BarChart.vue";
import DonutChart from "./Components/DonutChart.vue";
import type {
    ConditionSlice,
    FormatReportRow,
    GenreRow,
    MonthPoint,
} from "./analytics.resource";

const props = defineProps<{
    byFormat: FormatReportRow[];
    byCondition: ConditionSlice[];
    topGenres: GenreRow[];
    monthlyRevenue: MonthPoint[];
}>();

const { formatPrice } = useMoney();

const totals = computed(() =>
    props.byFormat.reduce(
        (acc, row) => ({
            titles: acc.titles + row.titles,
            stockUnits: acc.stockUnits + row.stockUnits,
            stockValue: acc.stockValue + row.stockValue,
            soldUnits: acc.soldUnits + row.soldUnits,
            revenue: acc.revenue + row.revenue,
        }),
        { titles: 0, stockUnits: 0, stockValue: 0, soldUnits: 0, revenue: 0 },
    ),
);

const conditionSegments = computed(() =>
    props.byCondition.map((slice) => ({
        label: slice.label,
        value: slice.units,
        color: slice.color,
    })),
);

const monthlySeries = computed(() =>
    props.monthlyRevenue.map((point) => ({
        label: point.label,
        value: point.revenue,
    })),
);
</script>

<template>
    <AppLayout>
        <IndexLayout
            title="Analityka - Raporty"
            subtitle="Zestawienia magazynu i sprzedaży"
            icon="pi pi-file"
            icon-color="#60a5fa"
        >
            <!-- Format report -->
            <section class="panel">
                <h2 class="panel-title">Zestawienie wg formatu</h2>
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Format</th>
                                <th class="num">Tytuły</th>
                                <th class="num">Stan (szt.)</th>
                                <th class="num">Wartość magazynu</th>
                                <th class="num">Sprzedane (szt.)</th>
                                <th class="num">Przychód</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in byFormat" :key="row.format">
                                <td>
                                    <span
                                        class="badge"
                                        :style="{
                                            color: row.color,
                                            background: `${row.color}1f`,
                                        }"
                                        >{{ row.label }}</span
                                    >
                                </td>
                                <td class="num">{{ row.titles }}</td>
                                <td class="num">{{ row.stockUnits }}</td>
                                <td class="num">
                                    {{ formatPrice(row.stockValue) }}
                                </td>
                                <td class="num">{{ row.soldUnits }}</td>
                                <td class="num strong">
                                    {{ formatPrice(row.revenue) }}
                                </td>
                            </tr>
                            <tr v-if="!byFormat.length">
                                <td colspan="6" class="empty">Brak danych.</td>
                            </tr>
                        </tbody>
                        <tfoot v-if="byFormat.length">
                            <tr>
                                <td>Razem</td>
                                <td class="num">{{ totals.titles }}</td>
                                <td class="num">{{ totals.stockUnits }}</td>
                                <td class="num">
                                    {{ formatPrice(totals.stockValue) }}
                                </td>
                                <td class="num">{{ totals.soldUnits }}</td>
                                <td class="num strong">
                                    {{ formatPrice(totals.revenue) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>

            <!-- Condition + genres -->
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <section class="panel">
                    <h2 class="panel-title">Stan wg kondycji (szt.)</h2>
                    <DonutChart
                        :segments="conditionSegments"
                        center-caption="szt."
                    />
                </section>

                <section class="panel">
                    <h2 class="panel-title">Top gatunki</h2>
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Gatunek</th>
                                    <th class="num">Tytuły</th>
                                    <th class="num">Stan (szt.)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in topGenres" :key="row.genre">
                                    <td>{{ row.genre || "-" }}</td>
                                    <td class="num">{{ row.titles }}</td>
                                    <td class="num">{{ row.units }}</td>
                                </tr>
                                <tr v-if="!topGenres.length">
                                    <td colspan="3" class="empty">
                                        Brak danych.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <!-- Monthly revenue -->
            <section class="panel">
                <h2 class="panel-title">
                    Przychód miesięczny - ostatnie 12 miesięcy
                </h2>
                <BarChart
                    :series="monthlySeries"
                    :format-value="formatPrice"
                    color="#60a5fa"
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
.data-table tfoot td {
    font-weight: 600;
    color: #cbd5e1;
    border-top: 1px solid rgba(148, 163, 184, 0.18);
    border-bottom: none;
}
.data-table .num {
    text-align: right;
    font-variant-numeric: tabular-nums;
}
.data-table .strong {
    font-weight: 600;
}
.data-table .empty {
    text-align: center;
    color: rgba(148, 163, 184, 0.5);
    padding: 1.5rem;
}
.badge {
    display: inline-block;
    padding: 0.15rem 0.55rem;
    border-radius: 6px;
    font-size: 0.74rem;
    font-weight: 600;
}
</style>
