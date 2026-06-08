<script setup lang="ts">
import { computed } from "vue";

defineOptions({ name: "BaseTextArea" });

interface Props {
    modelValue?: string;
    placeholder?: string;
    disabled?: boolean;
    readonly?: boolean;
    error?: boolean;
    rows?: number;
    id?: string;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: "",
    placeholder: "",
    disabled: false,
    readonly: false,
    error: false,
    rows: 3,
});

const emit = defineEmits<{
    (e: "update:modelValue", value: string): void;
    (e: "focus", event: FocusEvent): void;
    (e: "blur", event: FocusEvent): void;
}>();

const inputValue = computed({
    get: () => props.modelValue,
    set: (value: string) => emit("update:modelValue", value),
});
</script>

<template>
    <div
        class="base-textarea"
        :class="{
            'base-textarea--disabled': disabled,
            'base-textarea--error': error,
            'base-textarea--readonly': readonly,
        }"
    >
        <textarea
            :id="id"
            v-model="inputValue"
            :placeholder="placeholder"
            :disabled="disabled"
            :readonly="readonly"
            :rows="rows"
            class="base-textarea__field"
            @focus="emit('focus', $event)"
            @blur="emit('blur', $event)"
        />
    </div>
</template>

<style scoped>
.base-textarea {
    position: relative;
    display: flex;
    background: rgba(10, 16, 32, 0.7);
    border: 1px solid rgba(56, 189, 248, 0.1);
    border-radius: 8px;
    transition: all 0.2s;
    overflow: hidden;
}

.base-textarea:focus-within {
    border-color: rgba(56, 189, 248, 0.38);
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.06);
}

.base-textarea--error {
    border-color: rgba(248, 113, 113, 0.38);
}

.base-textarea--error:focus-within {
    border-color: rgba(248, 113, 113, 0.5);
    box-shadow: 0 0 0 3px rgba(248, 113, 113, 0.1);
}

.base-textarea--disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background: rgba(10, 16, 32, 0.4);
}

.base-textarea--readonly {
    background: rgba(10, 16, 32, 0.4);
}

.base-textarea__field {
    flex: 1;
    min-width: 0;
    background: transparent;
    border: none;
    outline: none;
    color: #e2e8f0;
    font-size: 0.875rem;
    font-family: inherit;
    line-height: 1.5;
    padding: 0.52rem 0.7rem;
    resize: vertical;
    min-height: 76px;
}

.base-textarea__field::placeholder {
    color: rgba(148, 163, 184, 0.28);
}

.base-textarea__field:disabled {
    cursor: not-allowed;
}
</style>
