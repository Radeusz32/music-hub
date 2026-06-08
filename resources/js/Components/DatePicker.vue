<script setup lang="ts">
import { computed } from "vue";
import PrimeDatePicker from "primevue/datepicker";

defineOptions({ name: "DatePicker" });

const props = defineProps<{
    modelValue: string;
    placeholder?: string;
}>();

const emit = defineEmits<{
    (e: "update:modelValue", value: string): void;
}>();

const dateValue = computed<Date | null>(() =>
    props.modelValue ? new Date(props.modelValue) : null,
);

function handleUpdate(
    value: Date | Date[] | (Date | null)[] | null | undefined,
): void {
    if (!value || Array.isArray(value)) {
        emit("update:modelValue", "");
        return;
    }
    const y = value.getFullYear();
    const m = String(value.getMonth() + 1).padStart(2, "0");
    const d = String(value.getDate()).padStart(2, "0");
    emit("update:modelValue", `${y}-${m}-${d}`);
}
</script>

<template>
    <PrimeDatePicker
        :model-value="dateValue"
        :placeholder="placeholder"
        date-format="yy-mm-dd"
        show-button-bar
        fluid
        @update:model-value="handleUpdate"
    />
</template>

<style scoped>
/* ── Input (rendered inside this component) ── */
:deep(.p-datepicker-input) {
    background: rgba(10, 16, 32, 0.8);
    border: 1px solid rgba(56, 189, 248, 0.12);
    border-radius: 5px;
    padding: 0.22rem 0.5rem;
    color: #94a3b8;
    font-size: 0.72rem;
    height: 24px;
    min-height: unset;
    width: 100%;
    transition: border-color 0.15s;
    box-shadow: none;
}

:deep(.p-datepicker-input:focus) {
    outline: none;
    border-color: rgba(56, 189, 248, 0.38);
    box-shadow: none;
    color: #e2e8f0;
}

:deep(.p-datepicker-input::placeholder) {
    color: rgba(148, 163, 184, 0.35);
}

:deep(.p-datepicker-input-icon-container) {
    display: none;
}
</style>

<!--
  The overlay panel is teleported to <body>, so it lives outside this
  component's scope. These rules must be global to reach it; they sit on top
  of the Aura dark preset.
-->
<style>
/* ── Panel (Popup) ── */
.p-datepicker-panel {
    background: linear-gradient(135deg, #0d1728 0%, #080e1c 100%);
    border: 1px solid rgba(56, 189, 248, 0.15);
    border-radius: 10px;
    padding: 0.75rem;
    box-shadow:
        0 20px 50px rgba(0, 0, 0, 0.7),
        0 0 40px rgba(56, 189, 248, 0.05);
    color: #e2e8f0;
}

/* ── Header ── */
.p-datepicker-panel .p-datepicker-header {
    background: transparent;
    border-bottom: 1px solid rgba(56, 189, 248, 0.1);
    padding: 0.5rem;
    margin-bottom: 0.5rem;
}

.p-datepicker-panel .p-datepicker-title {
    color: #e2e8f0;
    font-weight: 600;
    font-size: 0.875rem;
}

/* ── Navigation Buttons ── */
.p-datepicker-panel .p-datepicker-prev-button,
.p-datepicker-panel .p-datepicker-next-button {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    background: rgba(56, 189, 248, 0.08);
    border: 1px solid rgba(56, 189, 248, 0.15);
    color: #38bdf8;
    transition: all 0.15s;
}

.p-datepicker-panel .p-datepicker-prev-button:hover,
.p-datepicker-panel .p-datepicker-next-button:hover {
    background: rgba(56, 189, 248, 0.15);
    border-color: rgba(56, 189, 248, 0.3);
    color: #38bdf8;
}

.p-datepicker-panel .p-datepicker-prev-button:focus,
.p-datepicker-panel .p-datepicker-next-button:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.2);
}

/* ── Month/Year Selectors ── */
.p-datepicker-panel .p-datepicker-select-month,
.p-datepicker-panel .p-datepicker-select-year {
    background: rgba(10, 16, 32, 0.7);
    border: 1px solid rgba(56, 189, 248, 0.12);
    border-radius: 5px;
    color: #cbd5e1;
    font-size: 0.8rem;
    padding: 0.25rem 0.5rem;
    transition: border-color 0.15s;
}

.p-datepicker-panel .p-datepicker-select-month:hover,
.p-datepicker-panel .p-datepicker-select-year:hover {
    border-color: rgba(56, 189, 248, 0.25);
}

.p-datepicker-panel .p-datepicker-select-month:focus,
.p-datepicker-panel .p-datepicker-select-year:focus {
    outline: none;
    border-color: rgba(56, 189, 248, 0.38);
    box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.1);
}

.p-datepicker-panel .p-datepicker-select-month option,
.p-datepicker-panel .p-datepicker-select-year option {
    background: #0d1424;
    color: #e2e8f0;
}

/* ── Day Names (Header) ── */
.p-datepicker-panel .p-datepicker-weekday {
    color: rgba(148, 163, 184, 0.6);
    font-weight: 600;
    font-size: 0.7rem;
    text-transform: uppercase;
}

.p-datepicker-panel .p-datepicker-weekday-cell {
    padding: 0.4rem 0.3rem;
}

/* ── Day Cells ── */
.p-datepicker-panel .p-datepicker-day-cell {
    padding: 0.15rem;
}

.p-datepicker-panel .p-datepicker-day {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    background: transparent;
    border: 1px solid transparent;
    color: #cbd5e1;
    font-size: 0.8rem;
    transition: all 0.15s;
}

.p-datepicker-panel .p-datepicker-day:hover {
    background: rgba(56, 189, 248, 0.1);
    border-color: rgba(56, 189, 248, 0.2);
    color: #38bdf8;
}

.p-datepicker-panel .p-datepicker-day:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.25);
}

/* Dzisiejsza data */
.p-datepicker-panel .p-datepicker-today > .p-datepicker-day {
    background: rgba(56, 189, 248, 0.12);
    border-color: rgba(56, 189, 248, 0.25);
    color: #38bdf8;
    font-weight: 600;
}

/* Wybrana data */
.p-datepicker-panel .p-datepicker-day-selected,
.p-datepicker-panel .p-datepicker-today > .p-datepicker-day-selected {
    background: linear-gradient(
        135deg,
        rgba(56, 189, 248, 0.25),
        rgba(56, 189, 248, 0.15)
    );
    border-color: rgba(56, 189, 248, 0.45);
    color: #38bdf8;
    font-weight: 600;
}

.p-datepicker-panel .p-datepicker-day-selected:hover {
    background: linear-gradient(
        135deg,
        rgba(56, 189, 248, 0.35),
        rgba(56, 189, 248, 0.2)
    );
    border-color: rgba(56, 189, 248, 0.6);
}

/* Dni wyłączone */
.p-datepicker-panel .p-datepicker-day.p-disabled {
    color: rgba(148, 163, 184, 0.2);
    cursor: not-allowed;
}

.p-datepicker-panel .p-datepicker-day.p-disabled:hover {
    background: transparent;
    border-color: transparent;
}

/* ── Footer Buttons ── */
.p-datepicker-panel .p-datepicker-buttonbar {
    border-top: 1px solid rgba(56, 189, 248, 0.1);
    padding: 0.5rem;
    margin-top: 0.5rem;
    display: flex;
    justify-content: space-between;
    gap: 0.5rem;
}

.p-datepicker-panel .p-datepicker-buttonbar button {
    padding: 0.4rem 0.8rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
    transition: all 0.15s;
    flex: 1;
}

/* Przycisk "Dziś" */
.p-datepicker-panel .p-datepicker-buttonbar button:first-child {
    background: rgba(56, 189, 248, 0.12);
    border: 1px solid rgba(56, 189, 248, 0.25);
    color: #38bdf8;
}

.p-datepicker-panel .p-datepicker-buttonbar button:first-child:hover {
    background: rgba(56, 189, 248, 0.2);
    border-color: rgba(56, 189, 248, 0.4);
}

/* Przycisk "Wyczyść" */
.p-datepicker-panel .p-datepicker-buttonbar button:last-child {
    background: rgba(148, 163, 184, 0.08);
    border: 1px solid rgba(148, 163, 184, 0.15);
    color: rgba(148, 163, 184, 0.7);
}

.p-datepicker-panel .p-datepicker-buttonbar button:last-child:hover {
    background: rgba(248, 113, 113, 0.12);
    border-color: rgba(248, 113, 113, 0.25);
    color: #f87171;
}

.p-datepicker-panel .p-datepicker-buttonbar button:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.2);
}

/* ── Month/Year Picker ── */
.p-datepicker-panel .p-datepicker-month-view .p-datepicker-month,
.p-datepicker-panel .p-datepicker-year-view .p-datepicker-year {
    border-radius: 6px;
    background: transparent;
    border: 1px solid transparent;
    color: #cbd5e1;
    padding: 0.5rem;
    transition: all 0.15s;
}

.p-datepicker-panel .p-datepicker-month-view .p-datepicker-month:hover,
.p-datepicker-panel .p-datepicker-year-view .p-datepicker-year:hover {
    background: rgba(56, 189, 248, 0.1);
    border-color: rgba(56, 189, 248, 0.2);
    color: #38bdf8;
}

.p-datepicker-panel .p-datepicker-month-view .p-datepicker-month-selected,
.p-datepicker-panel .p-datepicker-year-view .p-datepicker-year-selected {
    background: linear-gradient(
        135deg,
        rgba(56, 189, 248, 0.25),
        rgba(56, 189, 248, 0.15)
    );
    border-color: rgba(56, 189, 248, 0.45);
    color: #38bdf8;
    font-weight: 600;
}
</style>
