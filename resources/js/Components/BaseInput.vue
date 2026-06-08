<script setup lang="ts">
import { computed } from "vue";

defineOptions({ name: "BaseInput" });

interface Props {
    modelValue?: string | number;
    type?: string;
    placeholder?: string;
    disabled?: boolean;
    readonly?: boolean;
    prefix?: string;
    suffix?: string;
    prefixIcon?: string;
    suffixIcon?: string;
    error?: boolean;
    id?: string;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: "",
    type: "text",
    placeholder: "",
    disabled: false,
    readonly: false,
    error: false,
});

const emit = defineEmits<{
    (e: "update:modelValue", value: string): void;
    (e: "focus", event: FocusEvent): void;
    (e: "blur", event: FocusEvent): void;
    (e: "prefixClick"): void;
    (e: "suffixClick"): void;
}>();

const inputValue = computed({
    get: () => props.modelValue,
    set: (value: string | number) => emit("update:modelValue", String(value)),
});

const hasPrefix = computed(() => !!(props.prefix || props.prefixIcon || slots.prefix));
const hasSuffix = computed(() => !!(props.suffix || props.suffixIcon || slots.suffix));

const slots = defineSlots<{
    prefix?: () => any;
    suffix?: () => any;
}>();
</script>

<template>
    <div
        class="base-input"
        :class="{
            'base-input--disabled': disabled,
            'base-input--error': error,
            'base-input--readonly': readonly,
        }"
    >
        <!-- Prefix -->
        <div
            v-if="hasPrefix"
            class="base-input__prefix"
            @click="emit('prefixClick')"
        >
            <slot name="prefix">
                <i v-if="prefixIcon" :class="prefixIcon" class="base-input__icon" />
                <span v-else-if="prefix" class="base-input__text">{{ prefix }}</span>
            </slot>
        </div>

        <!-- Input -->
        <input
            :id="id"
            v-model="inputValue"
            :type="type"
            :placeholder="placeholder"
            :disabled="disabled"
            :readonly="readonly"
            class="base-input__field"
            @focus="emit('focus', $event)"
            @blur="emit('blur', $event)"
        />

        <!-- Suffix -->
        <div
            v-if="hasSuffix"
            class="base-input__suffix"
            @click="emit('suffixClick')"
        >
            <slot name="suffix">
                <i v-if="suffixIcon" :class="suffixIcon" class="base-input__icon" />
                <span v-else-if="suffix" class="base-input__text">{{ suffix }}</span>
            </slot>
        </div>
    </div>
</template>

<style scoped>
.base-input {
    position: relative;
    display: flex;
    align-items: center;
    background: rgba(10, 16, 32, 0.7);
    border: 1px solid rgba(56, 189, 248, 0.1);
    border-radius: 8px;
    transition: all 0.2s;
    overflow: hidden;
}

.base-input:focus-within {
    border-color: rgba(56, 189, 248, 0.38);
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.06);
}

.base-input--error {
    border-color: rgba(248, 113, 113, 0.38);
}

.base-input--error:focus-within {
    border-color: rgba(248, 113, 113, 0.5);
    box-shadow: 0 0 0 3px rgba(248, 113, 113, 0.1);
}

.base-input--disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background: rgba(10, 16, 32, 0.4);
}

.base-input--readonly {
    background: rgba(10, 16, 32, 0.4);
}

.base-input__field {
    flex: 1;
    min-width: 0;
    background: transparent;
    border: none;
    outline: none;
    color: #e2e8f0;
    font-size: 0.875rem;
    padding: 0.52rem 0.7rem;
}

.base-input__field::placeholder {
    color: rgba(148, 163, 184, 0.28);
}

.base-input__field:disabled {
    cursor: not-allowed;
}

.base-input__prefix,
.base-input__suffix {
    display: flex;
    align-items: center;
    flex-shrink: 0;
    padding: 0 0.7rem;
    color: rgba(148, 163, 184, 0.5);
    user-select: none;
}

.base-input__icon {
    font-size: 0.875rem;
    color: rgba(56, 189, 248, 0.5);
}

.base-input__text {
    font-size: 0.875rem;
    color: rgba(148, 163, 184, 0.6);
    font-weight: 500;
}

/* Clickable prefix/suffix */
.base-input__prefix:has(~ .base-input__field:not(:disabled)):hover,
.base-input__suffix:has(+ .base-input__field:not(:disabled)):hover {
    cursor: pointer;
    color: rgba(56, 189, 248, 0.7);
}
</style>
