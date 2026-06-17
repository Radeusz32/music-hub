<script setup lang="ts">
import AppLayout from "@/layout/Tenant/AppLayout.vue";
import IndexLayout from "@/layout/Tenant/IndexLayout.vue";
import { computed } from "vue";
import { useMoney } from "@/composables/useMoney";
import StatCard from "./Components/StatCard.vue";
import RankBars from "./Components/RankBars.vue";
import type { ArtistSales, ArtistStock } from "./analytics.resource";

const props = defineProps<{
    byRevenue: ArtistSales[];
    byStock: ArtistStock[];
    distinctArtists: number;
}>();

const { formatPrice } = useMoney();

const revenueItems = computed(() =>
    props.byRevenue.map((artist) => ({
        label: artist.artist,
        value: artist.revenue,
        sublabel: `${artist.units} szt. • ${artist.titles} tyt.`,
    })),
);

const stockItems = computed(() =>
    props.byStock.map((artist) => ({
        label: artist.artist,
        value: artist.units,
        sublabel: `${artist.titles} tyt. • ${formatPrice(artist.value)}`,
    })),
);

const topArtist = computed(() => props.byRevenue[0]?.artist ?? "—");
</script>

<template>
    <AppLayout>
        <IndexLayout
            title="Analityka — Top artyści"
            subtitle="Najlepiej sprzedający się i najliczniej reprezentowani artyści"
            icon="pi pi-star"
            icon-color="#fbbf24"
        >
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <StatCard
                    label="Artyści w katalogu"
                    :value="String(distinctArtists)"
                    icon="pi pi-users"
                    color="#a78bfa"
                />
                <StatCard
                    label="Lider sprzedaży"
                    :value="topArtist"
                    icon="pi pi-star"
                    color="#fbbf24"
                />
            </div>

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <section class="panel">
                    <h2 class="panel-title">Top wg przychodu</h2>
                    <RankBars
                        :items="revenueItems"
                        :format-value="formatPrice"
                        color="#4ade80"
                    />
                </section>

                <section class="panel">
                    <h2 class="panel-title">Top wg stanu magazynu (szt.)</h2>
                    <RankBars :items="stockItems" color="#38bdf8" />
                </section>
            </div>
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
