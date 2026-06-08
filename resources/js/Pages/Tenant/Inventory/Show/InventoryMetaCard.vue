<script setup lang="ts">
import type { InventoryRecord } from "../inventory.resource";
import { useDate } from "@/composables/useDate";

defineOptions({ name: "InventoryMetaCard" });

defineProps<{
    record: InventoryRecord;
}>();

const { formatDate } = useDate();
</script>

<template>
    <div class="meta-card">
        <div v-if="record.user" class="meta-item">
            <i class="pi pi-user meta-icon" />
            <span class="meta-label">Właściciel:</span>
            <span class="meta-value">{{ record.user.name }}</span>
        </div>
        <div class="meta-item">
            <i class="pi pi-plus-circle meta-icon" />
            <span class="meta-label">Dodano:</span>
            <span class="meta-value">{{ formatDate(record.created_at) }}</span>
        </div>
        <div class="meta-item">
            <i class="pi pi-pencil meta-icon" />
            <span class="meta-label">Aktualizacja:</span>
            <span class="meta-value">{{ formatDate(record.updated_at) }}</span>
        </div>
    </div>
</template>

<style scoped>
.meta-card {
    display: flex;
    align-items: center;
    gap: 2rem;
    flex-wrap: wrap;
    padding: 1rem 1.4rem;
    background: rgba(10, 15, 30, 0.5);
    border: 1px solid rgba(56, 189, 248, 0.06);
    border-radius: 12px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.78rem;
    color: rgba(148, 163, 184, 0.45);
}

.meta-icon {
    font-size: 0.68rem;
    color: rgba(56, 189, 248, 0.3);
}

.meta-label {
    color: rgba(148, 163, 184, 0.35);
}

.meta-value {
    color: rgba(148, 163, 184, 0.65);
}
</style>
