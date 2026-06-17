<script setup lang="ts">
import type { InventoryRecord } from "../inventory.resource";

defineOptions({ name: "InventoryDetailsCard" });

defineProps<{
    record: InventoryRecord;
}>();
</script>

<template>
    <div class="detail-card">
        <h2 class="card-title">
            <i class="pi pi-info-circle" />
            Szczegóły
        </h2>
        <div class="detail-grid">
            <div class="detail-field">
                <span class="detail-label">Wytwórnia</span>
                <span class="detail-value">{{ record.label ?? "-" }}</span>
            </div>
            <div class="detail-field">
                <span class="detail-label">Nr katalogowy</span>
                <span class="detail-value mono">{{
                    record.catalog_number ?? "-"
                }}</span>
            </div>
            <div class="detail-field">
                <span class="detail-label">Barcode</span>
                <span class="detail-value mono">{{
                    record.barcode ?? "-"
                }}</span>
            </div>
            <div class="detail-field">
                <span class="detail-label">Kraj wydania</span>
                <span class="detail-value">{{ record.country ?? "-" }}</span>
            </div>
            <div class="detail-field">
                <span class="detail-label">Rok</span>
                <span class="detail-value">{{ record.year ?? "-" }}</span>
            </div>
            <div class="detail-field">
                <span class="detail-label">Gatunek</span>
                <span class="detail-value">{{ record.genre }}</span>
            </div>
        </div>

        <!-- Notes -->
        <template v-if="record.notes">
            <div class="notes-divider" />
            <div class="notes-block">
                <span class="detail-label">
                    <i class="pi pi-file-edit" />
                    Notatki
                </span>
                <p class="notes-text">{{ record.notes }}</p>
            </div>
        </template>
    </div>
</template>

<style scoped>
.detail-card {
    background: linear-gradient(135deg, #0a0f1e 0%, #070b16 100%);
    border: 1px solid rgba(56, 189, 248, 0.08);
    border-radius: 16px;
    padding: 1.6rem;
}

.card-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.78rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: rgba(148, 163, 184, 0.4);
    margin: 0 0 1.2rem;
}

.card-title .pi {
    color: rgba(56, 189, 248, 0.5);
    font-size: 0.75rem;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.2rem 2rem;
}

.detail-field {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.detail-label {
    font-size: 0.68rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: rgba(148, 163, 184, 0.38);
}

.detail-value {
    font-size: 0.9rem;
    color: #cbd5e1;
}

.detail-value.mono {
    font-family: "Courier New", monospace;
    font-size: 0.82rem;
    color: rgba(56, 189, 248, 0.8);
}

.notes-divider {
    height: 1px;
    background: rgba(56, 189, 248, 0.06);
    margin: 1.3rem 0;
}

.notes-block {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.notes-text {
    font-size: 0.875rem;
    color: #94a3b8;
    line-height: 1.6;
    margin: 0;
    padding: 0.75rem 1rem;
    background: rgba(56, 189, 248, 0.03);
    border-left: 2px solid rgba(56, 189, 248, 0.2);
    border-radius: 0 6px 6px 0;
}

@media (max-width: 640px) {
    .detail-grid {
        grid-template-columns: 1fr 1fr;
    }
}
</style>
