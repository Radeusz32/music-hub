<script setup lang="ts">
import { onBeforeUnmount, ref, watch } from "vue";
import type {
    BooleanColumnFilter,
    ColumnDef,
    DateRangeColumnFilter,
    NumberColumnFilter,
    NumberRangeColumnFilter,
} from "@/types/datatable";
import DatePicker from "@/Components/DatePicker.vue";
import Tooltip from "@/Components/Tooltip.vue";

defineOptions({ name: "DataTableColumnFilter" });

const props = defineProps<{
    column: ColumnDef;
    filterValues: Record<string, string>;
}>();

const emit = defineEmits<{
    (e: "filter", key: string, value: string): void;
}>();

/* ── Local state for free-typed number inputs (decoupled from the server
   round-trip so the field doesn't lag/jump while typing) ── */
const numberValue = ref<number | null>(null);
const rangeFrom = ref<number | null>(null);
const rangeTo = ref<number | null>(null);

function toNumber(value: string | undefined): number | null {
    if (value === undefined || value === "") {
        return null;
    }
    const n = Number(value);
    return Number.isNaN(n) ? null : n;
}

function syncFromProps(): void {
    const filter = props.column.filter;
    if (filter?.type === "number") {
        numberValue.value = toNumber(props.filterValues[props.column.key]);
    } else if (filter?.type === "number-range") {
        rangeFrom.value = toNumber(props.filterValues[filter.fromKey]);
        rangeTo.value = toNumber(props.filterValues[filter.toKey]);
    }
}

syncFromProps();
watch(() => props.filterValues, syncFromProps, { deep: true });

/* ── Debounced emit ── */
let timer: ReturnType<typeof setTimeout> | null = null;

function emitDebounced(key: string, value: string): void {
    if (timer) {
        clearTimeout(timer);
    }
    timer = setTimeout(() => emit("filter", key, value), 450);
}

onBeforeUnmount(() => {
    if (timer) {
        clearTimeout(timer);
    }
});

function onNumber(value: number | null): void {
    numberValue.value = value;
    emitDebounced(props.column.key, value === null ? "" : String(value));
}

function onRangeFrom(key: string, value: number | null): void {
    rangeFrom.value = value;
    emitDebounced(key, value === null ? "" : String(value));
}

function onRangeTo(key: string, value: number | null): void {
    rangeTo.value = value;
    emitDebounced(key, value === null ? "" : String(value));
}

/* ── Boolean toggle (✓ / ✗ icons, click again to clear) ── */
function toggleBoolean(value: "1" | "0"): void {
    const current = props.filterValues[props.column.key];
    emit("filter", props.column.key, current === value ? "" : value);
}
</script>

<template>
    <div class="dt-col-filter" @click.stop>
        <!-- SELECT -->
        <BaseDropdown
            v-if="column.filter?.type === 'select'"
            searchable
            :model-value="filterValues[column.key] ?? null"
            :options="column.filter.options"
            clearable
            @update:model-value="emit('filter', column.key, $event ?? '')"
        />

        <!-- BOOLEAN (clickable ✓ / ✗) -->
        <div
            v-else-if="column.filter?.type === 'boolean'"
            class="dt-bool"
            role="group"
        >
            <Tooltip
                :content="
                    (column.filter as BooleanColumnFilter).trueLabel ?? 'Tak'
                "
                position="top"
            >
                <button
                    type="button"
                    class="dt-bool-btn dt-bool-btn--true"
                    :class="{
                        'dt-bool-btn--active': filterValues[column.key] === '1',
                    }"
                    @click="toggleBoolean('1')"
                >
                    <i class="pi pi-check" />
                </button>
            </Tooltip>
            <Tooltip
                :content="
                    (column.filter as BooleanColumnFilter).falseLabel ?? 'Nie'
                "
                position="top"
            >
                <button
                    type="button"
                    class="dt-bool-btn dt-bool-btn--false"
                    :class="{
                        'dt-bool-btn--active': filterValues[column.key] === '0',
                    }"
                    @click="toggleBoolean('0')"
                >
                    <i class="pi pi-times" />
                </button>
            </Tooltip>
        </div>

        <!-- DATE RANGE -->
        <div v-else-if="column.filter?.type === 'date-range'" class="dt-range">
            <DatePicker
                :model-value="
                    filterValues[
                        (column.filter as DateRangeColumnFilter).fromKey
                    ] ?? ''
                "
                placeholder="od"
                @update:model-value="
                    emit(
                        'filter',
                        (column.filter as DateRangeColumnFilter).fromKey,
                        $event,
                    )
                "
            />
            <DatePicker
                :model-value="
                    filterValues[
                        (column.filter as DateRangeColumnFilter).toKey
                    ] ?? ''
                "
                placeholder="do"
                @update:model-value="
                    emit(
                        'filter',
                        (column.filter as DateRangeColumnFilter).toKey,
                        $event,
                    )
                "
            />
        </div>

        <!-- NUMBER (exact) -->
        <BaseInputNumber
            v-else-if="column.filter?.type === 'number'"
            class="dt-number"
            :model-value="numberValue"
            :placeholder="
                (column.filter as NumberColumnFilter).placeholder ?? 'Wartość'
            "
            :format="
                (column.filter as NumberColumnFilter).currency
                    ? 'currency'
                    : undefined
            "
            :suffix="
                (column.filter as NumberColumnFilter).currency
                    ? 'zł'
                    : undefined
            "
            :min="(column.filter as NumberColumnFilter).min"
            :max="(column.filter as NumberColumnFilter).max"
            @update:model-value="onNumber"
        />

        <!-- NUMBER RANGE -->
        <div
            v-else-if="column.filter?.type === 'number-range'"
            class="dt-range"
        >
            <BaseInputNumber
                class="dt-number"
                :model-value="rangeFrom"
                placeholder="od"
                :format="
                    (column.filter as NumberRangeColumnFilter).currency
                        ? 'currency'
                        : undefined
                "
                :suffix="
                    (column.filter as NumberRangeColumnFilter).currency
                        ? 'zł'
                        : undefined
                "
                :min="(column.filter as NumberRangeColumnFilter).min"
                :max="(column.filter as NumberRangeColumnFilter).max"
                @update:model-value="
                    onRangeFrom(
                        (column.filter as NumberRangeColumnFilter).fromKey,
                        $event,
                    )
                "
            />
            <BaseInputNumber
                class="dt-number"
                :model-value="rangeTo"
                placeholder="do"
                :format="
                    (column.filter as NumberRangeColumnFilter).currency
                        ? 'currency'
                        : undefined
                "
                :suffix="
                    (column.filter as NumberRangeColumnFilter).currency
                        ? 'zł'
                        : undefined
                "
                :min="(column.filter as NumberRangeColumnFilter).min"
                :max="(column.filter as NumberRangeColumnFilter).max"
                @update:model-value="
                    onRangeTo(
                        (column.filter as NumberRangeColumnFilter).toKey,
                        $event,
                    )
                "
            />
        </div>
    </div>
</template>

<style scoped>
.dt-col-filter {
    margin-bottom: 0.35rem;
}

.dt-range {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}

/* ── Boolean toggle ── */
.dt-bool {
    display: flex;
    justify-content: center;
    gap: 0.3rem;
}

.dt-bool-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 7px;
    border: 1px solid rgba(148, 163, 184, 0.18);
    background: rgba(148, 163, 184, 0.05);
    color: rgba(148, 163, 184, 0.5);
    font-size: 0.72rem;
    cursor: pointer;
    transition:
        background 0.15s,
        border-color 0.15s,
        color 0.15s;
}

.dt-bool-btn:hover {
    color: #cbd5e1;
    border-color: rgba(148, 163, 184, 0.32);
}

.dt-bool-btn--true.dt-bool-btn--active {
    background: rgba(74, 222, 128, 0.14);
    border-color: rgba(74, 222, 128, 0.4);
    color: #4ade80;
}

.dt-bool-btn--false.dt-bool-btn--active {
    background: rgba(248, 113, 113, 0.14);
    border-color: rgba(248, 113, 113, 0.4);
    color: #f87171;
}

/* Compact the number inputs to match the small date-range filter inputs */
.dt-number :deep(.base-input-number__field) {
    padding: 0.22rem 0.5rem;
    font-size: 0.72rem;
}

.dt-number :deep(.base-input-number__suffix) {
    padding: 0 0.5rem;
    font-size: 0.7rem;
}
</style>
