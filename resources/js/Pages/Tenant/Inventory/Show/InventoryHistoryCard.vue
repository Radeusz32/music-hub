<script setup lang="ts">
import { useDate } from "@/composables/useDate";
import {
    resolveOptionLabel,
    resolveOptionColor,
    type FilterOption,
    type InventoryMovement,
} from "../movements.resource";

defineOptions({ name: "InventoryHistoryCard" });

const props = defineProps<{
    movements: InventoryMovement[];
    typeOptions: FilterOption[];
}>();

const { formatDate } = useDate();

function typeBadgeStyle(type: string): Record<string, string> {
    const color = resolveOptionColor(props.typeOptions, type);
    return {
        color,
        borderColor: `${color}40`,
        background: `${color}12`,
    };
}

function deltaClass(delta: number): string {
    if (delta > 0) return "delta-up";
    if (delta < 0) return "delta-down";
    return "delta-zero";
}

function formatDelta(delta: number): string {
    return delta > 0 ? `+${delta}` : String(delta);
}
</script>

<template>
    <div class="history-card">
        <div class="history-header">
            <i class="pi pi-history history-icon" />
            <h3 class="history-title">Historia ruchów magazynowych</h3>
            <span class="history-count">{{ movements.length }}</span>
        </div>

        <div v-if="movements.length === 0" class="history-empty">
            <i class="pi pi-inbox" />
            Brak zarejestrowanych ruchów magazynowych
        </div>

        <div v-else class="history-table-wrap">
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Typ</th>
                        <th class="ta-right">Zmiana</th>
                        <th class="ta-right">Stan</th>
                        <th>Powód</th>
                        <th>Użytkownik</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="movement in movements" :key="movement.id">
                        <td class="td-date">
                            {{ formatDate(movement.created_at) }}
                        </td>
                        <td>
                            <span
                                class="type-badge"
                                :style="typeBadgeStyle(movement.type)"
                            >
                                {{
                                    resolveOptionLabel(
                                        typeOptions,
                                        movement.type,
                                    )
                                }}
                            </span>
                        </td>
                        <td class="ta-right">
                            <span :class="deltaClass(movement.delta)">
                                {{ formatDelta(movement.delta) }}
                            </span>
                        </td>
                        <td class="ta-right td-stock">
                            <span class="stock-before">
                                {{ movement.quantity_before }}
                            </span>
                            <i class="pi pi-arrow-right stock-arrow" />
                            <span class="stock-after">
                                {{ movement.quantity_after }}
                            </span>
                        </td>
                        <td class="td-note">
                            <span v-if="movement.note">{{
                                movement.note
                            }}</span>
                            <span v-else class="muted">—</span>
                        </td>
                        <td>
                            <span v-if="movement.user" class="user-chip">
                                <i class="pi pi-user" />
                                {{ movement.user.name }}
                            </span>
                            <span v-else class="muted">—</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<style scoped>
.history-card {
    padding: 1.4rem 1.6rem;
    background: rgba(10, 15, 30, 0.5);
    border: 1px solid rgba(56, 189, 248, 0.06);
    border-radius: 12px;
}

.history-header {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    margin-bottom: 1.1rem;
}

.history-icon {
    font-size: 0.85rem;
    color: rgba(56, 189, 248, 0.55);
}

.history-title {
    font-size: 0.92rem;
    font-weight: 600;
    color: #e2e8f0;
    margin: 0;
}

.history-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.4rem;
    height: 1.4rem;
    padding: 0 0.45rem;
    border-radius: 999px;
    background: rgba(56, 189, 248, 0.12);
    border: 1px solid rgba(56, 189, 248, 0.2);
    color: #38bdf8;
    font-size: 0.72rem;
    font-weight: 600;
}

.history-empty {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 2rem;
    color: rgba(148, 163, 184, 0.4);
    font-size: 0.85rem;
}

.history-table-wrap {
    overflow-x: auto;
}

.history-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.82rem;
}

.history-table thead th {
    padding: 0.55rem 0.7rem;
    text-align: left;
    font-size: 0.68rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: rgba(148, 163, 184, 0.5);
    border-bottom: 1px solid rgba(56, 189, 248, 0.08);
    white-space: nowrap;
}

.history-table tbody td {
    padding: 0.6rem 0.7rem;
    border-bottom: 1px solid rgba(148, 163, 184, 0.05);
    color: #cbd5e1;
    vertical-align: middle;
}

.history-table tbody tr:last-child td {
    border-bottom: none;
}

.history-table tbody tr:hover td {
    background: rgba(56, 189, 248, 0.03);
}

.ta-right {
    text-align: right;
}

.td-date {
    color: rgba(148, 163, 184, 0.75);
    white-space: nowrap;
}

.type-badge {
    padding: 0.18rem 0.5rem;
    border: 1px solid;
    border-radius: 5px;
    font-size: 0.72rem;
    font-weight: 600;
    white-space: nowrap;
}

.delta-up {
    color: #4ade80;
    font-weight: 600;
}
.delta-down {
    color: #f87171;
    font-weight: 600;
}
.delta-zero {
    color: #94a3b8;
}

.td-stock {
    white-space: nowrap;
}
.stock-before {
    color: rgba(148, 163, 184, 0.6);
}
.stock-arrow {
    margin: 0 0.35rem;
    font-size: 0.6rem;
    color: rgba(148, 163, 184, 0.4);
}
.stock-after {
    color: #e2e8f0;
    font-weight: 600;
}

.td-note {
    max-width: 260px;
    color: #94a3b8;
}

.user-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.78rem;
    color: #94a3b8;
    white-space: nowrap;
}
.user-chip .pi {
    font-size: 0.65rem;
    color: rgba(148, 163, 184, 0.45);
}

.muted {
    color: rgba(148, 163, 184, 0.35);
}
</style>
