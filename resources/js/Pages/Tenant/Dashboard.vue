<script setup lang="ts">
import AppLayout from "../../layout/Tenant/AppLayout.vue";
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";

const page = usePage();

/**
 * Docelowo:
 * const stats = computed(() => page.props.dashboard.stats);
 */
const stats = computed(() => ({
    totalSales: "$5k",
    totalOrders: 500,
    productsSold: 9,
    newCustomers: 12,
    earnings: "$6078.76",
    profitPercent: 80,
}));

const topProducts = computed(() => [
    { name: "Home Decor Range", sales: "43%" },
    { name: "Disney Princess Dress", sales: "17%" },
    { name: "Bathroom Essentials", sales: "19%" },
    { name: "Apple Smartwatch", sales: "29%" },
]);
</script>

<template>
    <AppLayout>
        <div class="dashboard">
            <!-- PAGE HEADER -->
            <div class="page-header">
                <p>Witaj ponownie, user</p>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.dashboard {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

/* HEADER */

.page-header h1 {
    font-size: 1.8rem;
    margin-bottom: 0.3rem;
}

.page-header p {
    color: #94a3b8;
}

/* STATS */

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1.5rem;
}

.main-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1.5rem;
}

/* CARD */

.card {
    background: #111827;
    padding: 1.5rem;
    border-radius: 12px;
    border: 1px solid #1f2937;
}

.value {
    font-size: 1.5rem;
    font-weight: 600;
}

.value.large {
    font-size: 2rem;
}

.product-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #1f2937;
}

.product-row:last-child {
    border-bottom: none;
}

.badge {
    background: #1e293b;
    padding: 0.25rem 0.6rem;
    border-radius: 8px;
    font-size: 0.85rem;
}

.progress {
    height: 8px;
    background: #1f2937;
    border-radius: 8px;
    margin: 1rem 0;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background: #22c55e;
}

.muted {
    color: #94a3b8;
    font-size: 0.9rem;
}
</style>
