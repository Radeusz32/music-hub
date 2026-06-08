<script setup lang="ts">
import { computed, useId } from "vue";

defineOptions({ name: "BaseCheckbox" });

interface Props {
    modelValue?: boolean;
    label?: string;
    disabled?: boolean;
    error?: boolean;
    id?: string;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: false,
    label: "",
    disabled: false,
    error: false,
});

const emit = defineEmits<{
    (e: "update:modelValue", value: boolean): void;
}>();

const checkboxId = computed(() => props.id || `checkbox-${useId()}`);

const isChecked = computed({
    get: () => props.modelValue,
    set: (value: boolean) => emit("update:modelValue", value),
});

const slots = defineSlots<{
    default?: () => any;
}>();
</script>

<template>
    <label
        :for="checkboxId"
        class="base-checkbox"
        :class="{
            'base-checkbox--disabled': disabled,
            'base-checkbox--error': error,
        }"
    >
        <input
            :id="checkboxId"
            v-model="isChecked"
            type="checkbox"
            :disabled="disabled"
            class="base-checkbox__input"
        />

        <span class="base-checkbox__box">
            <svg
                v-if="isChecked"
                class="base-checkbox__check"
                viewBox="0 0 12 10"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    d="M1 5L4.5 8.5L11 1.5"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
        </span>

        <span v-if="label || slots.default" class="base-checkbox__label">
            <slot>{{ label }}</slot>
        </span>
    </label>
</template>

<style scoped>
.base-checkbox {
    display: inline-flex;
    align-items: center;
    gap: 0.65rem;
    cursor: pointer;
    user-select: none;
    position: relative;
}

.base-checkbox--disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.base-checkbox__input {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border-width: 0;
}

.base-checkbox__box {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 18px;
    height: 18px;
    border-radius: 4px;
    border: 1.5px solid rgba(56, 189, 248, 0.25);
    background: rgba(10, 16, 32, 0.6);
    transition: all 0.2s;
    flex-shrink: 0;
}

.base-checkbox:hover:not(.base-checkbox--disabled) .base-checkbox__box {
    border-color: rgba(56, 189, 248, 0.45);
    background: rgba(10, 16, 32, 0.8);
}

.base-checkbox__input:focus-visible + .base-checkbox__box {
    outline: none;
    border-color: rgba(56, 189, 248, 0.5);
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.15);
}

/* Checked state */
.base-checkbox__input:checked + .base-checkbox__box {
    background: linear-gradient(135deg, rgba(56, 189, 248, 0.25), rgba(56, 189, 248, 0.15));
    border-color: #38bdf8;
}

.base-checkbox:hover:not(.base-checkbox--disabled) .base-checkbox__input:checked + .base-checkbox__box {
    background: linear-gradient(135deg, rgba(56, 189, 248, 0.35), rgba(56, 189, 248, 0.2));
    border-color: #38bdf8;
}

/* Error state */
.base-checkbox--error .base-checkbox__box {
    border-color: rgba(248, 113, 113, 0.4);
}

.base-checkbox--error .base-checkbox__input:checked + .base-checkbox__box {
    border-color: #f87171;
}

/* Check icon */
.base-checkbox__check {
    width: 12px;
    height: 10px;
    color: #38bdf8;
    opacity: 0;
    transform: scale(0.8);
    transition: all 0.15s ease-out;
}

.base-checkbox__input:checked ~ .base-checkbox__box .base-checkbox__check {
    opacity: 1;
    transform: scale(1);
}

/* Label */
.base-checkbox__label {
    font-size: 0.875rem;
    color: rgba(203, 213, 225, 0.9);
    transition: color 0.15s;
}

.base-checkbox:hover:not(.base-checkbox--disabled) .base-checkbox__label {
    color: #e2e8f0;
}

.base-checkbox--disabled .base-checkbox__label {
    color: rgba(148, 163, 184, 0.5);
}
</style>
