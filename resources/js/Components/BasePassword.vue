<script setup lang="ts">
import { ref, computed } from "vue";

defineOptions({ name: "BasePassword" });

interface Props {
    modelValue?: string;
    placeholder?: string;
    disabled?: boolean;
    readonly?: boolean;
    prefix?: string;
    suffix?: string;
    prefixIcon?: string;
    suffixIcon?: string;
    error?: boolean;
    showToggle?: boolean;
    showStrength?: boolean;
    id?: string;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: "",
    placeholder: "",
    disabled: false,
    readonly: false,
    error: false,
    showToggle: true,
    showStrength: false,
});

const emit = defineEmits<{
    (e: "update:modelValue", value: string): void;
    (e: "focus", event: FocusEvent): void;
    (e: "blur", event: FocusEvent): void;
    (e: "prefixClick"): void;
    (e: "suffixClick"): void;
}>();

const showPassword = ref(false);

const inputValue = computed({
    get: () => props.modelValue,
    set: (value: string) => emit("update:modelValue", value),
});

const inputType = computed(() => (showPassword.value ? "text" : "password"));

function toggleVisibility(): void {
    if (!props.disabled && !props.readonly) {
        showPassword.value = !showPassword.value;
    }
}

const slots = defineSlots<{
    prefix?: () => any;
    suffix?: () => any;
}>();

const hasPrefix = computed(
    () => !!(props.prefix || props.prefixIcon || slots.prefix),
);
const hasSuffix = computed(
    () => !!(props.suffix || props.suffixIcon || slots.suffix),
);

// Password strength calculation
const strength = computed(() => {
    if (!props.showStrength || !props.modelValue) return null;

    const password = props.modelValue;
    let score = 0;

    if (password.length >= 8) score++;
    if (password.length >= 12) score++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
    if (/\d/.test(password)) score++;
    if (/[^a-zA-Z0-9]/.test(password)) score++;

    if (score <= 2) return { level: "weak", label: "Słabe", color: "#f87171" };
    if (score <= 3)
        return { level: "medium", label: "Średnie", color: "#fb923c" };
    return { level: "strong", label: "Silne", color: "#4ade80" };
});
</script>

<template>
    <div class="base-password">
        <div
            class="base-password__input-wrapper"
            :class="{
                'base-password__input-wrapper--disabled': disabled,
                'base-password__input-wrapper--error': error,
                'base-password__input-wrapper--readonly': readonly,
            }"
        >
            <div
                v-if="hasPrefix"
                class="base-password__prefix"
                @click="emit('prefixClick')"
            >
                <slot name="prefix">
                    <i
                        v-if="prefixIcon"
                        :class="prefixIcon"
                        class="base-password__icon"
                    />
                    <span v-else-if="prefix" class="base-password__text">
                        {{ prefix }}
                    </span>
                </slot>
            </div>

            <input
                :id="id"
                v-model="inputValue"
                :type="inputType"
                :placeholder="placeholder"
                :disabled="disabled"
                :readonly="readonly"
                class="base-password__field"
                @focus="emit('focus', $event)"
                @blur="emit('blur', $event)"
            />

            <div
                v-if="hasSuffix"
                class="base-password__suffix"
                @click="emit('suffixClick')"
            >
                <slot name="suffix">
                    <i
                        v-if="suffixIcon"
                        :class="suffixIcon"
                        class="base-password__icon"
                    />
                    <span v-else-if="suffix" class="base-password__text">
                        {{ suffix }}
                    </span>
                </slot>
            </div>

            <button
                v-if="showToggle"
                type="button"
                class="base-password__toggle"
                :disabled="disabled || readonly"
                @click="toggleVisibility"
            >
                <i
                    :class="showPassword ? 'pi pi-eye-slash' : 'pi pi-eye'"
                    class="base-password__toggle-icon"
                />
            </button>
        </div>

        <!-- Password Strength Indicator -->
        <div v-if="showStrength && modelValue" class="base-password__strength">
            <div class="base-password__strength-bars">
                <div
                    v-for="i in 3"
                    :key="i"
                    class="base-password__strength-bar"
                    :class="{
                        'base-password__strength-bar--active':
                            strength &&
                            ((strength.level === 'weak' && i === 1) ||
                                (strength.level === 'medium' && i <= 2) ||
                                (strength.level === 'strong' && i <= 3)),
                    }"
                    :style="{
                        backgroundColor:
                            strength &&
                            ((strength.level === 'weak' && i === 1) ||
                                (strength.level === 'medium' && i <= 2) ||
                                (strength.level === 'strong' && i <= 3))
                                ? strength.color
                                : undefined,
                    }"
                />
            </div>
            <span
                v-if="strength"
                class="base-password__strength-label"
                :style="{ color: strength.color }"
            >
                {{ strength.label }}
            </span>
        </div>
    </div>
</template>

<style scoped>
.base-password {
    width: 100%;
}

.base-password__input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    background: rgba(10, 16, 32, 0.7);
    border: 1px solid rgba(56, 189, 248, 0.1);
    border-radius: 8px;
    transition: all 0.2s;
    overflow: hidden;
}

.base-password__input-wrapper:focus-within {
    border-color: rgba(56, 189, 248, 0.38);
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.06);
}

.base-password__input-wrapper--error {
    border-color: rgba(248, 113, 113, 0.38);
}

.base-password__input-wrapper--error:focus-within {
    border-color: rgba(248, 113, 113, 0.5);
    box-shadow: 0 0 0 3px rgba(248, 113, 113, 0.1);
}

.base-password__input-wrapper--disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background: rgba(10, 16, 32, 0.4);
}

.base-password__input-wrapper--readonly {
    background: rgba(10, 16, 32, 0.4);
}

.base-password__field {
    flex: 1;
    min-width: 0;
    background: transparent;
    border: none;
    outline: none;
    color: #e2e8f0;
    font-size: 0.875rem;
    padding: 0.52rem 0.7rem;
}

.base-password__field::placeholder {
    color: rgba(148, 163, 184, 0.28);
}

.base-password__field:disabled {
    cursor: not-allowed;
}

.base-password__toggle {
    position: absolute;
    right: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 100%;
    background: transparent;
    border: none;
    cursor: pointer;
    transition: all 0.15s;
    color: rgba(148, 163, 184, 0.5);
}

.base-password__toggle:hover:not(:disabled) {
    color: rgba(56, 189, 248, 0.7);
}

.base-password__toggle:disabled {
    cursor: not-allowed;
}

.base-password__toggle-icon {
    font-size: 0.875rem;
}

/* Password Strength */
.base-password__strength {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-top: 0.5rem;
}

.base-password__strength-bars {
    display: flex;
    gap: 0.25rem;
    flex: 1;
}

.base-password__strength-bar {
    height: 4px;
    flex: 1;
    background: rgba(148, 163, 184, 0.15);
    border-radius: 2px;
    transition: background-color 0.3s;
}

.base-password__strength-bar--active {
    /* Color set inline via :style */
}

.base-password__strength-label {
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
}

.base-password__prefix,
.base-password__suffix {
    display: flex;
    align-items: center;
    flex-shrink: 0;
    padding: 0 0.7rem;
    color: rgba(148, 163, 184, 0.5);
    user-select: none;
}

.base-password__icon {
    font-size: 0.875rem;
    color: rgba(56, 189, 248, 0.5);
}

.base-password__text {
    font-size: 0.875rem;
    color: rgba(148, 163, 184, 0.6);
    font-weight: 500;
}
</style>
