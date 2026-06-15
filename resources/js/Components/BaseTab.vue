<script setup lang="ts">
import { computed, ref, watch } from "vue";

defineOptions({ name: "BaseTab" });

export interface TabItem {
    value: string;
    label: string;
    icon?: string;
    badge?: number | string | null;
}

const props = withDefaults(
    defineProps<{
        tabs: TabItem[];
        modelValue?: string;
    }>(),
    {
        modelValue: undefined,
    },
);

const emit = defineEmits<{
    (e: "update:modelValue", value: string): void;
    (e: "change", value: string): void;
}>();

const internal = ref<string>(props.modelValue ?? props.tabs[0]?.value ?? "");

watch(
    () => props.modelValue,
    (value) => {
        if (value !== undefined) {
            internal.value = value;
        }
    },
);

const active = computed<string>(() => props.modelValue ?? internal.value);

function select(value: string): void {
    if (value === active.value) {
        return;
    }
    internal.value = value;
    emit("update:modelValue", value);
    emit("change", value);
}
</script>

<template>
    <div class="bt">
        <!-- Tab bar -->
        <div class="bt__bar" role="tablist">
            <button
                v-for="tab in tabs"
                :key="tab.value"
                type="button"
                role="tab"
                :aria-selected="tab.value === active"
                class="bt__tab"
                :class="{ 'bt__tab--active': tab.value === active }"
                @click="select(tab.value)"
            >
                <i v-if="tab.icon" :class="tab.icon" class="bt__icon" />
                <span>{{ tab.label }}</span>
                <span
                    v-if="tab.badge !== undefined && tab.badge !== null"
                    class="bt__badge"
                >
                    {{ tab.badge }}
                </span>
            </button>
        </div>

        <!-- Active panel -->
        <div class="bt__panel" role="tabpanel">
            <slot :name="active" :active="active" />
        </div>
    </div>
</template>

<style scoped>
.bt {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* ── Tab bar ── */
.bt__bar {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    flex-wrap: wrap;
    padding: 0.3rem;
    background: rgba(10, 15, 30, 0.5);
    border: 1px solid rgba(56, 189, 248, 0.06);
    border-radius: 12px;
}

.bt__tab {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.55rem 1.1rem;
    border: 1px solid transparent;
    border-radius: 9px;
    background: transparent;
    color: rgba(148, 163, 184, 0.7);
    font-size: 0.85rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.18s ease;
    white-space: nowrap;
}

.bt__tab:hover {
    color: #cbd5e1;
    background: rgba(56, 189, 248, 0.05);
}

.bt__tab--active {
    color: #38bdf8;
    background: rgba(56, 189, 248, 0.12);
    border-color: rgba(56, 189, 248, 0.28);
    box-shadow: 0 0 14px rgba(56, 189, 248, 0.12);
}

.bt__icon {
    font-size: 0.78rem;
}

.bt__badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.25rem;
    height: 1.25rem;
    padding: 0 0.4rem;
    border-radius: 999px;
    background: rgba(56, 189, 248, 0.16);
    color: #38bdf8;
    font-size: 0.68rem;
    font-weight: 600;
    line-height: 1;
}

.bt__tab:not(.bt__tab--active) .bt__badge {
    background: rgba(148, 163, 184, 0.14);
    color: rgba(148, 163, 184, 0.75);
}

/* ── Panel ── */
.bt__panel {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}
</style>
