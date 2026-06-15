<script setup lang="ts">
import { computed } from "vue";
import {
    resolveOptionColor,
    resolveOptionLabel,
    type FilterOption,
    type InventoryRecord,
} from "../inventory.resource";
import { useMoney } from "@/composables/useMoney";
import InventoryCover from "./InventoryCover.vue";

defineOptions({ name: "InventoryHeroCard" });

const { formatPrice } = useMoney();

const props = defineProps<{
    record: InventoryRecord;
    formatOptions: FilterOption[];
    conditionOptions: FilterOption[];
}>();

const formatLabel = computed(() =>
    resolveOptionLabel(props.formatOptions, props.record.format),
);
const formatColor = computed(() =>
    resolveOptionColor(props.formatOptions, props.record.format),
);
const conditionLabel = computed(() =>
    resolveOptionLabel(props.conditionOptions, props.record.condition),
);
const conditionColor = computed(() =>
    resolveOptionColor(props.conditionOptions, props.record.condition),
);

const quantityColor = computed<string>(() => {
    if (props.record.quantity === 0) return "#f87171";
    if (props.record.quantity <= 3) return "#fb923c";
    return "#4ade80";
});
</script>

<template>
    <div class="hero-card">
        <InventoryCover :record="record" :format-color="formatColor" />

        <!-- Key info -->
        <div class="hero-info">
            <p class="hero-artist">{{ record.artist }}</p>

            <div class="hero-badges">
                <span
                    class="badge-format"
                    :style="{
                        color: formatColor,
                        borderColor: `${formatColor}40`,
                        background: `${formatColor}12`,
                    }"
                >
                    {{ formatLabel }}
                </span>
                <span
                    class="badge-condition"
                    :style="{
                        color: conditionColor,
                        borderColor: `${conditionColor}40`,
                        background: `${conditionColor}12`,
                    }"
                >
                    <span
                        class="condition-dot"
                        :style="{
                            background: conditionColor,
                            boxShadow: `0 0 5px ${conditionColor}`,
                        }"
                    />
                    {{ conditionLabel }}
                </span>
            </div>

            <div class="hero-meta-row">
                <span v-if="record.year" class="hero-meta-item">
                    <i class="pi pi-calendar" />
                    {{ record.year }}
                </span>
                <span v-if="record.country" class="hero-meta-item">
                    <i class="pi pi-map-marker" />
                    {{ record.country }}
                </span>
                <span class="hero-meta-item">
                    <i class="pi pi-tag" />
                    {{ record.genre }}
                </span>
            </div>

            <div class="hero-divider" />

            <!-- Stock & pricing -->
            <div class="hero-stats">
                <div class="stat-block">
                    <span class="stat-label">Stan magazynu</span>
                    <span class="stat-value" :style="{ color: quantityColor }">
                        {{ record.quantity }}
                        <span class="stat-unit">szt.</span>
                    </span>
                </div>
                <div class="stat-block">
                    <span class="stat-label">Cena zakupu / szt.</span>
                    <span class="stat-value stat-cost">
                        {{ formatPrice(record.purchase_price_per_unit) }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.hero-card {
    display: grid;
    grid-template-columns: 220px 1fr;
    gap: 2rem;
    background: linear-gradient(135deg, #0a0f1e 0%, #070b16 60%, #0d1428 100%);
    border: 1px solid rgba(56, 189, 248, 0.1);
    border-radius: 16px;
    padding: 1.8rem;
    overflow: hidden;
    position: relative;
}

.hero-card::before {
    content: "";
    position: absolute;
    inset: 0;
    background: radial-gradient(
        ellipse at 15% 50%,
        rgba(56, 189, 248, 0.04) 0%,
        transparent 60%
    );
    pointer-events: none;
}

.hero-info {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    position: relative;
    z-index: 1;
}

.hero-artist {
    font-size: 1.1rem;
    color: rgba(148, 163, 184, 0.7);
    margin: 0;
}

.hero-badges {
    display: flex;
    gap: 0.6rem;
    flex-wrap: wrap;
}

.badge-format,
.badge-condition {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.3rem 0.75rem;
    border: 1px solid;
    border-radius: 20px;
    font-size: 0.78rem;
    font-weight: 600;
    white-space: nowrap;
}

.condition-dot {
    display: inline-block;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    flex-shrink: 0;
}

.hero-meta-row {
    display: flex;
    gap: 1.2rem;
    flex-wrap: wrap;
}

.hero-meta-item {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.8rem;
    color: rgba(148, 163, 184, 0.55);
}

.hero-meta-item .pi {
    font-size: 0.7rem;
    color: rgba(56, 189, 248, 0.4);
}

.hero-divider {
    height: 1px;
    background: linear-gradient(90deg, rgba(56, 189, 248, 0.12), transparent);
}

.hero-stats {
    display: flex;
    gap: 2rem;
    flex-wrap: wrap;
}

.stat-block {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}

.stat-label {
    font-size: 0.68rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: rgba(148, 163, 184, 0.4);
}

.stat-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: #e2e8f0;
    line-height: 1;
}

.stat-unit {
    font-size: 0.75rem;
    font-weight: 400;
    color: rgba(148, 163, 184, 0.5);
    margin-left: 2px;
}

.stat-price {
    color: #4ade80;
}

.stat-cost {
    color: #94a3b8;
    font-size: 1rem;
}

@media (max-width: 640px) {
    .hero-card {
        grid-template-columns: 1fr;
    }
    .hero-stats {
        gap: 1rem;
    }
}
</style>
