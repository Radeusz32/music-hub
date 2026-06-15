<script setup lang="ts">
import { computed, ref } from "vue";

defineOptions({ name: "BaseInputNumber" });

interface Props {
    modelValue?: number | null;
    placeholder?: string;
    disabled?: boolean;
    readonly?: boolean;
    min?: number;
    max?: number;
    step?: number;
    prefix?: string;
    suffix?: string;
    prefixIcon?: string;
    suffixIcon?: string;
    error?: boolean;
    showButtons?: boolean;
    format?: "decimal" | "currency";
    locale?: string;
    grouping?: boolean;
    minFractionDigits?: number;
    maxFractionDigits?: number;
    id?: string;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: null,
    placeholder: "",
    disabled: false,
    readonly: false,
    step: 1,
    error: false,
    showButtons: false,
    locale: "pl-PL",
    grouping: true,
    minFractionDigits: 0,
    maxFractionDigits: 2,
});

const emit = defineEmits<{
    (e: "update:modelValue", value: number | null): void;
    (e: "focus", event: FocusEvent): void;
    (e: "blur", event: FocusEvent): void;
}>();

const inputRef = ref<HTMLInputElement | null>(null);
const isFocused = ref(false);
const editingText = ref("");

const isFormatted = computed(
    () => props.format === "decimal" || props.format === "currency",
);

const minFrac = computed(() =>
    props.format === "currency" ? 2 : props.minFractionDigits,
);
const maxFrac = computed(() =>
    props.format === "currency"
        ? 2
        : Math.max(props.maxFractionDigits, props.minFractionDigits),
);

const decimalSep = computed(() => {
    const parts = new Intl.NumberFormat(props.locale).formatToParts(1.1);
    return parts.find((p) => p.type === "decimal")?.value ?? ",";
});

const groupSep = computed(() => {
    const parts = new Intl.NumberFormat(props.locale).formatToParts(1000);
    return parts.find((p) => p.type === "group")?.value ?? " ";
});

function formatNumber(value: number): string {
    return new Intl.NumberFormat(props.locale, {
        minimumFractionDigits: minFrac.value,
        maximumFractionDigits: maxFrac.value,
        useGrouping: props.grouping,
    }).format(value);
}

function normalizeNumeric(value: string): string {
    return value
        .split(groupSep.value)
        .join("")
        .replace(/\s/g, "")
        .replace(decimalSep.value, ".")
        .replace(/[^0-9.-]/g, "");
}

function clamp(num: number): number {
    let value = num;
    if (props.min !== undefined && value < props.min) {
        value = props.min;
    }
    if (props.max !== undefined && value > props.max) {
        value = props.max;
    }
    return value;
}

function isEmpty(value: number | null | undefined): boolean {
    return value === null || value === undefined || (value as unknown) === "";
}

const displayValue = computed(() => {
    if (isEmpty(props.modelValue)) {
        return isFormatted.value && isFocused.value ? editingText.value : "";
    }
    if (isFormatted.value) {
        if (isFocused.value) {
            return editingText.value;
        }
        const num = Number(props.modelValue);
        return isNaN(num) ? "" : formatNumber(num);
    }
    return String(props.modelValue);
});

function onInput(event: Event): void {
    const target = event.target as HTMLInputElement;
    const value = target.value;

    if (isFormatted.value) {
        editingText.value = value;
        const normalized = normalizeNumeric(value);
        if (normalized === "" || normalized === "-") {
            emit("update:modelValue", null);
            return;
        }
        const num = parseFloat(normalized);
        if (!isNaN(num)) {
            const clamped = clamp(num);
            emit("update:modelValue", clamped);
            if (clamped !== num) {
                editingText.value = String(clamped).replace(
                    ".",
                    decimalSep.value,
                );
                target.value = editingText.value;
            }
        }
        return;
    }

    if (value === "" || value === "-") {
        emit("update:modelValue", null);
        return;
    }
    const num = parseFloat(value);
    if (!isNaN(num)) {
        const clamped = clamp(num);
        emit("update:modelValue", clamped);
        if (clamped !== num) {
            target.value = String(clamped);
        }
    }
}

function onFocus(event: FocusEvent): void {
    isFocused.value = true;
    if (isFormatted.value) {
        const num = isEmpty(props.modelValue) ? null : Number(props.modelValue);
        editingText.value =
            num === null || isNaN(num)
                ? ""
                : String(num).replace(".", decimalSep.value);
    }
    emit("focus", event);
}

function onBlur(event: FocusEvent): void {
    isFocused.value = false;
    emit("blur", event);
}

function setValueFromButton(num: number): void {
    emit("update:modelValue", num);
    if (isFormatted.value && isFocused.value) {
        editingText.value = String(num).replace(".", decimalSep.value);
    }
}

function increment(): void {
    if (props.disabled || props.readonly) {
        return;
    }
    const current = Number(props.modelValue ?? 0) || 0;
    let newValue = current + props.step;
    if (props.max !== undefined && newValue > props.max) {
        newValue = props.max;
    }
    setValueFromButton(newValue);
}

function decrement(): void {
    if (props.disabled || props.readonly) {
        return;
    }
    const current = Number(props.modelValue ?? 0) || 0;
    let newValue = current - props.step;
    if (props.min !== undefined && newValue < props.min) {
        newValue = props.min;
    }
    setValueFromButton(newValue);
}

const hasPrefix = computed(() => !!(props.prefix || props.prefixIcon));
const hasSuffix = computed(() => !!(props.suffix || props.suffixIcon));

const slots = defineSlots<{
    prefix?: () => any;
    suffix?: () => any;
}>();
</script>

<template>
    <div
        class="base-input-number"
        :class="{
            'base-input-number--disabled': disabled,
            'base-input-number--error': error,
            'base-input-number--readonly': readonly,
            'base-input-number--with-buttons': showButtons,
        }"
    >
        <!-- Prefix -->
        <div v-if="hasPrefix || slots.prefix" class="base-input-number__prefix">
            <slot name="prefix">
                <i
                    v-if="prefixIcon"
                    :class="prefixIcon"
                    class="base-input-number__icon"
                />
                <span v-else-if="prefix" class="base-input-number__text">{{
                    prefix
                }}</span>
            </slot>
        </div>

        <!-- Input -->
        <input
            :id="id"
            ref="inputRef"
            :value="displayValue"
            :type="isFormatted ? 'text' : 'number'"
            :inputmode="isFormatted ? 'decimal' : undefined"
            :placeholder="placeholder"
            :disabled="disabled"
            :readonly="readonly"
            :min="isFormatted ? undefined : min"
            :max="isFormatted ? undefined : max"
            :step="isFormatted ? undefined : step"
            class="base-input-number__field"
            @input="onInput"
            @focus="onFocus"
            @blur="onBlur"
        />

        <!-- Spinner Buttons -->
        <div v-if="showButtons" class="base-input-number__buttons">
            <button
                type="button"
                class="base-input-number__button base-input-number__button--up"
                :disabled="
                    disabled ||
                    readonly ||
                    (max !== undefined && (modelValue ?? 0) >= max)
                "
                @click="increment"
            >
                <i class="pi pi-chevron-up" />
            </button>
            <button
                type="button"
                class="base-input-number__button base-input-number__button--down"
                :disabled="
                    disabled ||
                    readonly ||
                    (min !== undefined && (modelValue ?? 0) <= min)
                "
                @click="decrement"
            >
                <i class="pi pi-chevron-down" />
            </button>
        </div>

        <!-- Suffix -->
        <div v-if="hasSuffix || slots.suffix" class="base-input-number__suffix">
            <slot name="suffix">
                <i
                    v-if="suffixIcon"
                    :class="suffixIcon"
                    class="base-input-number__icon"
                />
                <span v-else-if="suffix" class="base-input-number__text">{{
                    suffix
                }}</span>
            </slot>
        </div>
    </div>
</template>

<style scoped>
.base-input-number {
    position: relative;
    display: flex;
    align-items: center;
    background: rgba(10, 16, 32, 0.7);
    border: 1px solid rgba(56, 189, 248, 0.1);
    border-radius: 8px;
    transition: all 0.2s;
    overflow: hidden;
}

.base-input-number:focus-within {
    border-color: rgba(56, 189, 248, 0.38);
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.06);
}

.base-input-number--error {
    border-color: rgba(248, 113, 113, 0.38);
}

.base-input-number--error:focus-within {
    border-color: rgba(248, 113, 113, 0.5);
    box-shadow: 0 0 0 3px rgba(248, 113, 113, 0.1);
}

.base-input-number--disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background: rgba(10, 16, 32, 0.4);
}

.base-input-number--readonly {
    background: rgba(10, 16, 32, 0.4);
}

.base-input-number__field {
    flex: 1;
    min-width: 0;
    background: transparent;
    border: none;
    outline: none;
    color: #e2e8f0;
    font-size: 0.875rem;
    padding: 0.52rem 0.7rem;
}

/* Hide default spinner */
.base-input-number__field::-webkit-outer-spin-button,
.base-input-number__field::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.base-input-number__field[type="number"] {
    -moz-appearance: textfield;
}

.base-input-number__field::placeholder {
    color: rgba(148, 163, 184, 0.28);
}

.base-input-number__field:disabled {
    cursor: not-allowed;
}

.base-input-number__prefix,
.base-input-number__suffix {
    display: flex;
    align-items: center;
    flex-shrink: 0;
    padding: 0 0.7rem;
    color: rgba(148, 163, 184, 0.5);
    user-select: none;
}

.base-input-number__icon {
    font-size: 0.875rem;
    color: rgba(56, 189, 248, 0.5);
}

.base-input-number__text {
    font-size: 0.875rem;
    color: rgba(148, 163, 184, 0.6);
    font-weight: 500;
}

/* Spinner Buttons */
.base-input-number__buttons {
    display: flex;
    flex-direction: column;
    border-left: 1px solid rgba(56, 189, 248, 0.15);
    background: linear-gradient(
        180deg,
        rgba(10, 16, 32, 0.8) 0%,
        rgba(10, 16, 32, 0.6) 100%
    );
}

.base-input-number__button {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 50%;
    background: transparent;
    border: none;
    color: rgba(56, 189, 248, 0.55);
    cursor: pointer;
    transition: all 0.2s ease;
    padding: 0;
    overflow: hidden;
}

.base-input-number__button::before {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(
        135deg,
        rgba(56, 189, 248, 0.08) 0%,
        rgba(56, 189, 248, 0.04) 100%
    );
    opacity: 0;
    transition: opacity 0.2s ease;
}

.base-input-number__button:hover:not(:disabled) {
    color: #38bdf8;
}

.base-input-number__button:hover:not(:disabled)::before {
    opacity: 1;
}

.base-input-number__button:active:not(:disabled) {
    transform: scale(0.95);
}

.base-input-number__button:active:not(:disabled)::before {
    background: linear-gradient(
        135deg,
        rgba(56, 189, 248, 0.15) 0%,
        rgba(56, 189, 248, 0.1) 100%
    );
}

.base-input-number__button:disabled {
    opacity: 0.25;
    cursor: not-allowed;
    color: rgba(148, 163, 184, 0.3);
}

.base-input-number__button--up {
    border-bottom: 1px solid rgba(56, 189, 248, 0.12);
}

.base-input-number__button .pi {
    position: relative;
    z-index: 1;
    font-size: 0.7rem;
    font-weight: 600;
}

/* With buttons - adjust input padding */
.base-input-number--with-buttons .base-input-number__field {
    padding-right: 0.5rem;
}
</style>
