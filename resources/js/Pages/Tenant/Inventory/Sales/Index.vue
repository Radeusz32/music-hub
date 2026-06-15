<script setup lang="ts">
import AppLayout from "@/layout/Tenant/AppLayout.vue";
import IndexLayout from "@/layout/Tenant/IndexLayout.vue";
import { computed, ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { useToast } from "@/composables/useToast";
import { useMoney } from "@/composables/useMoney";
import { useDate } from "@/composables/useDate";
import { usePermissions } from "@/composables/Tenant/usePermissions";
import { useFeatures } from "@/composables/Tenant/useFeatures";
import SaleDialog from "./SaleDialog.vue";
import {
    defaultSaleForm,
    filterRecords,
    type RecentSale,
    type SaleTodayStats,
    type SellableRecord,
} from "../sales.resource";

const props = defineProps<{
    records: SellableRecord[];
    recentSales: RecentSale[];
    todayStats: SaleTodayStats;
}>();

const toast = useToast();
const { formatPrice } = useMoney();
const { formatDateTime } = useDate();

/* ── Capabilities ── */
const { hasPermission } = usePermissions();
const { hasFeature } = useFeatures();
const canSell = computed(
    () => hasFeature("inventory") && hasPermission("inventory-sales-create"),
);

/* ── Search ── */
const search = ref("");
const visibleRecords = computed(() =>
    filterRecords(props.records, search.value),
);

/* ── Sale dialog ── */
const showDialog = ref(false);
const selectedRecord = ref<SellableRecord | null>(null);
const form = useForm({ ...defaultSaleForm });

function openSale(record: SellableRecord): void {
    selectedRecord.value = record;
    form.reset();
    form.clearErrors();
    form.inventory_record_id = record.id;
    showDialog.value = true;
}

function closeDialog(): void {
    showDialog.value = false;
    form.reset();
    form.clearErrors();
    selectedRecord.value = null;
}

function submitSale(): void {
    form.post(route("tenant.inventory.sales.store"), {
        preserveScroll: true,
        onSuccess: () => {
            closeDialog();
            toast.success("Sprzedaż została zapisana");
        },
        onError: (errors) => {
            const msg = Object.values(errors)[0];
            if (msg) toast.error(msg);
        },
    });
}
</script>

<template>
    <AppLayout>
        <IndexLayout
            title="Sprzedaż"
            subtitle="Szybka sprzedaż płyt z magazynu"
            icon="pi pi-shopping-cart"
            icon-color="#10b981"
        >
            <div class="sales-layout">
                <!-- Main: product grid -->
                <section class="sales-main">
                    <div class="search-bar">
                        <i class="pi pi-search" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Szukaj po wykonawcy, tytule lub formacie..."
                            class="search-input"
                        />
                    </div>

                    <div v-if="visibleRecords.length" class="product-grid">
                        <article
                            v-for="record in visibleRecords"
                            :key="record.id"
                            class="product-card"
                        >
                            <div class="cover">
                                <img
                                    v-if="record.cover_image"
                                    :src="record.cover_image"
                                    :alt="record.name"
                                />
                                <i v-else class="pi pi-image" />
                                <span class="stock-badge">
                                    {{ record.quantity }} szt.
                                </span>
                            </div>

                            <div class="product-body">
                                <p class="product-title" :title="record.name">
                                    {{ record.name }}
                                </p>
                                <p
                                    class="product-artist"
                                    :title="record.artist"
                                >
                                    {{ record.artist }}
                                </p>
                                <div class="product-meta">
                                    <span class="format-badge">{{
                                        record.format
                                    }}</span>
                                    <span class="condition-badge">{{
                                        record.condition
                                    }}</span>
                                </div>
                            </div>

                            <div class="product-footer">
                                <button
                                    v-if="canSell"
                                    type="button"
                                    class="btn-sell"
                                    @click="openSale(record)"
                                >
                                    <i class="pi pi-shopping-cart" />
                                    Sprzedaj
                                </button>
                            </div>
                        </article>
                    </div>

                    <div v-else class="empty-state">
                        <i class="pi pi-inbox" />
                        <p>
                            {{
                                search
                                    ? "Brak płyt pasujących do wyszukiwania."
                                    : "Brak płyt dostępnych do sprzedaży."
                            }}
                        </p>
                    </div>
                </section>

                <!-- Sidebar: stats + recent sales -->
                <aside class="sales-aside">
                    <div class="stats-card">
                        <p class="stats-title">Dziś</p>
                        <div class="stats-row">
                            <div class="stat">
                                <span class="stat-value">{{
                                    todayStats.transactions
                                }}</span>
                                <span class="stat-label">Transakcji</span>
                            </div>
                            <div class="stat">
                                <span class="stat-value">{{
                                    todayStats.units
                                }}</span>
                                <span class="stat-label">Sztuk</span>
                            </div>
                        </div>
                    </div>

                    <div class="recent-card">
                        <p class="recent-title">
                            <i class="pi pi-history" />
                            Ostatnie sprzedaże
                        </p>

                        <ul v-if="recentSales.length" class="recent-list">
                            <li
                                v-for="sale in recentSales"
                                :key="sale.id"
                                class="recent-item"
                            >
                                <div class="recent-info">
                                    <span class="recent-name">
                                        {{
                                            sale.inventoryRecord?.name ??
                                            "Usunięta płyta"
                                        }}
                                    </span>
                                    <span class="recent-sub">
                                        {{ sale.inventoryRecord?.artist }} ·
                                        {{ formatDateTime(sale.created_at) }}
                                    </span>
                                </div>
                                <div class="recent-amount">
                                    <span class="recent-total">
                                        {{ formatPrice(sale.sale_total) }}
                                    </span>
                                    <span class="recent-qty">
                                        −{{ sale.quantity }} szt.
                                    </span>
                                </div>
                            </li>
                        </ul>

                        <p v-else class="recent-empty">
                            Brak sprzedaży do wyświetlenia.
                        </p>
                    </div>
                </aside>
            </div>
        </IndexLayout>

        <SaleDialog
            :show="showDialog"
            :record="selectedRecord"
            :form="form"
            @close="closeDialog"
            @submit="submitSale"
        />
    </AppLayout>
</template>

<style scoped>
.sales-layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
}

@media (min-width: 1024px) {
    .sales-layout {
        grid-template-columns: minmax(0, 1fr) 320px;
    }
}

/* ── Search ── */
.search-bar {
    position: relative;
    display: flex;
    align-items: center;
    margin-bottom: 1.25rem;
}
.search-bar .pi {
    position: absolute;
    left: 0.9rem;
    color: rgba(148, 163, 184, 0.5);
    font-size: 0.85rem;
}
.search-input {
    width: 100%;
    padding: 0.65rem 1rem 0.65rem 2.4rem;
    background: rgba(2, 6, 23, 0.4);
    border: 1px solid rgba(148, 163, 184, 0.15);
    border-radius: 10px;
    color: #e2e8f0;
    font-size: 0.875rem;
    transition: all 0.2s;
}
.search-input:focus {
    outline: none;
    border-color: rgba(16, 185, 129, 0.4);
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

/* ── Product grid ── */
.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
    gap: 1rem;
}

.product-card {
    display: flex;
    flex-direction: column;
    background: rgba(15, 23, 42, 0.5);
    border: 1px solid rgba(148, 163, 184, 0.1);
    border-radius: 14px;
    overflow: hidden;
    transition: all 0.2s;
}
.product-card:hover {
    border-color: rgba(16, 185, 129, 0.3);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
}

.cover {
    position: relative;
    aspect-ratio: 1 / 1;
    display: flex;
    align-items: center;
    justify-content: center;
    background: radial-gradient(
        circle at center,
        rgba(30, 41, 59, 0.6),
        rgba(2, 6, 23, 0.8)
    );
}
.cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.cover .pi {
    font-size: 2rem;
    color: rgba(148, 163, 184, 0.25);
}
.stock-badge {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    padding: 0.15rem 0.5rem;
    background: rgba(2, 6, 23, 0.8);
    border: 1px solid rgba(16, 185, 129, 0.35);
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 600;
    color: #34d399;
}

.product-body {
    flex: 1;
    padding: 0.75rem 0.85rem 0.5rem;
}
.product-title {
    margin: 0;
    font-size: 0.85rem;
    font-weight: 600;
    color: #e2e8f0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.product-artist {
    margin: 0.1rem 0 0;
    font-size: 0.75rem;
    color: rgba(148, 163, 184, 0.6);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.product-meta {
    display: flex;
    gap: 0.4rem;
    margin-top: 0.55rem;
}
.format-badge,
.condition-badge {
    padding: 0.12rem 0.45rem;
    border-radius: 5px;
    font-size: 0.66rem;
    font-weight: 600;
}
.format-badge {
    border: 1px solid rgba(56, 189, 248, 0.25);
    background: rgba(56, 189, 248, 0.1);
    color: #7dd3fc;
}
.condition-badge {
    border: 1px solid rgba(148, 163, 184, 0.2);
    background: rgba(148, 163, 184, 0.08);
    color: #cbd5e1;
}

.product-footer {
    display: flex;
    align-items: center;
    padding: 0.6rem 0.85rem 0.85rem;
}
.btn-sell {
    display: inline-flex;
    flex: 1;
    justify-content: center;
    align-items: center;
    gap: 0.35rem;
    padding: 0.4rem 0.7rem;
    background: linear-gradient(
        135deg,
        rgba(16, 185, 129, 0.2),
        rgba(16, 185, 129, 0.08)
    );
    border: 1px solid rgba(16, 185, 129, 0.35);
    border-radius: 8px;
    color: #34d399;
    font-size: 0.78rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}
.btn-sell:hover {
    background: linear-gradient(
        135deg,
        rgba(16, 185, 129, 0.3),
        rgba(16, 185, 129, 0.15)
    );
    box-shadow: 0 0 16px rgba(16, 185, 129, 0.2);
}

/* ── Empty ── */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    padding: 4rem 1rem;
    color: rgba(148, 163, 184, 0.5);
    text-align: center;
}
.empty-state .pi {
    font-size: 2.5rem;
    color: rgba(148, 163, 184, 0.25);
}

/* ── Sidebar ── */
.sales-aside {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.stats-card,
.recent-card {
    background: rgba(15, 23, 42, 0.5);
    border: 1px solid rgba(148, 163, 184, 0.1);
    border-radius: 14px;
    padding: 1.1rem 1.25rem;
}

.stats-title,
.recent-title {
    margin: 0 0 0.85rem;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: rgba(148, 163, 184, 0.6);
}
.recent-title {
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.stats-row {
    display: flex;
    gap: 1rem;
}
.stat {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    padding: 0.75rem;
    background: rgba(16, 185, 129, 0.06);
    border: 1px solid rgba(16, 185, 129, 0.15);
    border-radius: 10px;
}
.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #34d399;
    line-height: 1;
}
.stat-label {
    font-size: 0.72rem;
    color: rgba(148, 163, 184, 0.6);
}

.recent-list {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    margin: 0;
    padding: 0;
    list-style: none;
}
.recent-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.6rem;
    padding-bottom: 0.6rem;
    border-bottom: 1px solid rgba(148, 163, 184, 0.08);
}
.recent-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}
.recent-info {
    min-width: 0;
    display: flex;
    flex-direction: column;
}
.recent-name {
    font-size: 0.82rem;
    font-weight: 500;
    color: #e2e8f0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.recent-sub {
    font-size: 0.7rem;
    color: rgba(148, 163, 184, 0.5);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.recent-amount {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    white-space: nowrap;
}
.recent-total {
    font-size: 0.85rem;
    font-weight: 700;
    color: #34d399;
}
.recent-qty {
    font-size: 0.7rem;
    font-weight: 600;
    color: rgba(248, 113, 113, 0.85);
    white-space: nowrap;
}
.recent-empty {
    margin: 0;
    font-size: 0.8rem;
    color: rgba(148, 163, 184, 0.5);
}
</style>
