<script setup lang="ts">
defineOptions({ name: "BaseMaskedInput" });

interface Props {
    modelValue?: string;
    mask: string;
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
    placeholder: "",
    disabled: false,
    readonly: false,
    error: false,
});

const emit = defineEmits<{
    (e: "update:modelValue", value: string): void;
    (e: "focus", event: FocusEvent): void;
    (e: "blur", event: FocusEvent): void;
}>();

/**
 * Mask tokens:
 *   #  → digit          [0-9]
 *   A  → letter         [A-Za-z]
 *   *  → alphanumeric   [A-Za-z0-9]
 * Any other character is treated as a literal (auto-inserted).
 */
const tokens: Record<string, RegExp> = {
    "#": /\d/,
    A: /[A-Za-z]/,
    "*": /[A-Za-z0-9]/,
};

function applyMask(value: string, mask: string): string {
    let result = "";
    let valueIndex = 0;

    for (const maskChar of mask) {
        if (valueIndex >= value.length) {
            break;
        }

        const rule = tokens[maskChar];
        if (rule) {
            while (valueIndex < value.length && !rule.test(value[valueIndex])) {
                valueIndex++;
            }
            if (valueIndex >= value.length) {
                break;
            }
            result += value[valueIndex];
            valueIndex++;
        } else {
            result += maskChar;
            if (value[valueIndex] === maskChar) {
                valueIndex++;
            }
        }
    }

    return result;
}

function onInput(event: Event): void {
    const target = event.target as HTMLInputElement;
    const masked = applyMask(target.value, props.mask);
    target.value = masked;
    emit("update:modelValue", masked);
}

const slots = defineSlots<{
    prefix?: () => unknown;
    suffix?: () => unknown;
}>();
</script>

<template>
    <div
        class="base-masked-input"
        :class="{
            'base-masked-input--disabled': disabled,
            'base-masked-input--error': error,
            'base-masked-input--readonly': readonly,
        }"
    >
        <!-- Prefix -->
        <div
            v-if="prefix || prefixIcon || slots.prefix"
            class="base-masked-input__prefix"
        >
            <slot name="prefix">
                <i
                    v-if="prefixIcon"
                    :class="prefixIcon"
                    class="base-masked-input__icon"
                />
                <span v-else-if="prefix" class="base-masked-input__text">{{
                    prefix
                }}</span>
            </slot>
        </div>

        <!-- Input -->
        <input
            :id="id"
            :value="modelValue"
            type="text"
            :placeholder="placeholder"
            :disabled="disabled"
            :readonly="readonly"
            class="base-masked-input__field"
            @input="onInput"
            @focus="emit('focus', $event)"
            @blur="emit('blur', $event)"
        />

        <!-- Suffix -->
        <div
            v-if="suffix || suffixIcon || slots.suffix"
            class="base-masked-input__suffix"
        >
            <slot name="suffix">
                <i
                    v-if="suffixIcon"
                    :class="suffixIcon"
                    class="base-masked-input__icon"
                />
                <span v-else-if="suffix" class="base-masked-input__text">{{
                    suffix
                }}</span>
            </slot>
        </div>
    </div>
</template>

<style scoped>
.base-masked-input {
    position: relative;
    display: flex;
    align-items: center;
    background: rgba(10, 16, 32, 0.7);
    border: 1px solid rgba(56, 189, 248, 0.1);
    border-radius: 8px;
    transition: all 0.2s;
    overflow: hidden;
}

.base-masked-input:focus-within {
    border-color: rgba(56, 189, 248, 0.38);
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.06);
}

.base-masked-input--error {
    border-color: rgba(248, 113, 113, 0.38);
}

.base-masked-input--error:focus-within {
    border-color: rgba(248, 113, 113, 0.5);
    box-shadow: 0 0 0 3px rgba(248, 113, 113, 0.1);
}

.base-masked-input--disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background: rgba(10, 16, 32, 0.4);
}

.base-masked-input--readonly {
    background: rgba(10, 16, 32, 0.4);
}

.base-masked-input__field {
    flex: 1;
    min-width: 0;
    background: transparent;
    border: none;
    outline: none;
    color: #e2e8f0;
    font-size: 0.875rem;
    padding: 0.52rem 0.7rem;
}

.base-masked-input__field::placeholder {
    color: rgba(148, 163, 184, 0.28);
}

.base-masked-input__field:disabled {
    cursor: not-allowed;
}

.base-masked-input__prefix,
.base-masked-input__suffix {
    display: flex;
    align-items: center;
    flex-shrink: 0;
    padding: 0 0.7rem;
    color: rgba(148, 163, 184, 0.5);
    user-select: none;
}

.base-masked-input__icon {
    font-size: 0.875rem;
    color: rgba(56, 189, 248, 0.5);
}

.base-masked-input__text {
    font-size: 0.875rem;
    color: rgba(148, 163, 184, 0.6);
    font-weight: 500;
}
</style>
