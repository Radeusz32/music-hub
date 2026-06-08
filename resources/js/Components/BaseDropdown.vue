<script setup lang="ts">
import { computed, ref, onMounted, onBeforeUnmount, nextTick } from "vue";

defineOptions({ name: "BaseDropdown" });

export interface DropdownOption {
    value: string | number;
    label: string;
    disabled?: boolean;
}

interface Props {
    modelValue?: string | number | null;
    options?: DropdownOption[];
    placeholder?: string;
    disabled?: boolean;
    error?: boolean;
    prefixIcon?: string;
    suffixIcon?: string;
    id?: string;
    emptyLabel?: string;
    clearable?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: null,
    options: () => [],
    placeholder: "",
    disabled: false,
    error: false,
    emptyLabel: "Select option",
    clearable: false,
});

const emit = defineEmits<{
    (e: "update:modelValue", value: string | number | null): void;
    (e: "change", value: string | number | null): void;
}>();

const slots = defineSlots<{
    prefix?: () => any;
    suffix?: () => any;
    option?: (props: { option: DropdownOption }) => any;
}>();

const isOpen = ref(false);
const isFocused = ref(false);
const focusedIndex = ref(-1);
const triggerRef = ref<HTMLDivElement | null>(null);
const listRef = ref<HTMLUListElement | null>(null);
const dropdownRef = ref<HTMLDivElement | null>(null);

const hasPrefix = computed(() => !!props.prefixIcon || !!slots.prefix);
const hasSuffix = computed(() => !!props.suffixIcon || !!slots.suffix);

const selectedOption = computed(
    () => props.options.find((o) => o.value === props.modelValue) ?? null,
);

const displayLabel = computed(
    () => selectedOption.value?.label ?? props.placeholder ?? props.emptyLabel,
);

const isPlaceholder = computed(() => !selectedOption.value);

const showClear = computed(
    () =>
        props.clearable &&
        !props.disabled &&
        props.modelValue !== null &&
        props.modelValue !== undefined &&
        props.modelValue !== "",
);

function clear(e: MouseEvent): void {
    e.stopPropagation();
    emit("update:modelValue", null);
    emit("change", null);
}

function open() {
    if (props.disabled) return;
    isOpen.value = true;
    isFocused.value = true;
    focusedIndex.value = props.options.findIndex(
        (o) => o.value === props.modelValue,
    );
    nextTick(() => scrollFocusedIntoView());
}

function close() {
    isOpen.value = false;
    isFocused.value = false;
    focusedIndex.value = -1;
}

function toggle() {
    isOpen.value ? close() : open();
}

function select(option: DropdownOption) {
    if (option.disabled) return;
    emit("update:modelValue", option.value);
    emit("change", option.value);
    close();
    triggerRef.value?.focus();
}

function onKeydown(e: KeyboardEvent) {
    if (props.disabled) return;

    const enabledIndices = props.options
        .map((o, i) => (!o.disabled ? i : -1))
        .filter((i) => i !== -1);

    if (!isOpen.value) {
        if (["Enter", " ", "ArrowDown", "ArrowUp"].includes(e.key)) {
            e.preventDefault();
            open();
        }
        return;
    }

    switch (e.key) {
        case "Escape":
            e.preventDefault();
            close();
            triggerRef.value?.focus();
            break;
        case "ArrowDown": {
            e.preventDefault();
            const next = enabledIndices.find((i) => i > focusedIndex.value);
            if (next !== undefined) {
                focusedIndex.value = next;
                scrollFocusedIntoView();
            }
            break;
        }
        case "ArrowUp": {
            e.preventDefault();
            const prev = [...enabledIndices]
                .reverse()
                .find((i) => i < focusedIndex.value);
            if (prev !== undefined) {
                focusedIndex.value = prev;
                scrollFocusedIntoView();
            }
            break;
        }
        case "Home": {
            e.preventDefault();
            if (enabledIndices.length) {
                focusedIndex.value = enabledIndices[0];
                scrollFocusedIntoView();
            }
            break;
        }
        case "End": {
            e.preventDefault();
            if (enabledIndices.length) {
                focusedIndex.value = enabledIndices[enabledIndices.length - 1];
                scrollFocusedIntoView();
            }
            break;
        }
        case "Enter":
        case " ": {
            e.preventDefault();
            if (focusedIndex.value >= 0) {
                select(props.options[focusedIndex.value]);
            }
            break;
        }
        case "Tab":
            close();
            break;
    }
}

function scrollFocusedIntoView() {
    nextTick(() => {
        const li = listRef.value?.children[focusedIndex.value] as
            | HTMLElement
            | undefined;
        li?.scrollIntoView({ block: "nearest" });
    });
}

function onClickOutside(e: MouseEvent) {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target as Node)) {
        close();
    }
}

onMounted(() => document.addEventListener("mousedown", onClickOutside));
onBeforeUnmount(() =>
    document.removeEventListener("mousedown", onClickOutside),
);
</script>

<template>
    <div
        ref="dropdownRef"
        class="bd"
        :class="{
            'bd--disabled': disabled,
            'bd--error': error,
            'bd--focused': isFocused,
            'bd--open': isOpen,
        }"
    >
        <!-- Trigger -->
        <div
            :id="id"
            ref="triggerRef"
            class="bd__trigger"
            role="combobox"
            :aria-expanded="isOpen"
            :aria-haspopup="'listbox'"
            :aria-disabled="disabled"
            tabindex="0"
            @click="toggle"
            @keydown="onKeydown"
        >
            <!-- Prefix -->
            <div v-if="hasPrefix" class="bd__prefix">
                <slot name="prefix">
                    <i
                        v-if="prefixIcon"
                        :class="prefixIcon"
                        class="bd__icon"
                        aria-hidden="true"
                    />
                </slot>
            </div>

            <!-- Value / Placeholder -->
            <span
                class="bd__value"
                :class="{ 'bd__value--placeholder': isPlaceholder }"
            >
                {{ displayLabel }}
            </span>

            <!-- Suffix -->
            <div v-if="hasSuffix" class="bd__suffix">
                <slot name="suffix">
                    <i
                        v-if="suffixIcon"
                        :class="suffixIcon"
                        class="bd__icon"
                        aria-hidden="true"
                    />
                </slot>
            </div>

            <!-- Clear button -->
            <Transition name="bd-clear">
                <button
                    v-if="showClear"
                    type="button"
                    class="bd__clear"
                    tabindex="-1"
                    aria-label="Wyczyść"
                    @click="clear"
                    @mousedown.stop
                >
                    <i class="pi pi-times" />
                </button>
            </Transition>

            <!-- Chevron -->
            <span
                class="bd__chevron"
                :class="{ 'bd__chevron--hidden': showClear }"
                aria-hidden="true"
            >
                <i class="pi pi-chevron-down" />
            </span>
        </div>

        <!-- Dropdown list -->
        <Transition name="bd-list">
            <ul
                v-if="isOpen"
                ref="listRef"
                class="bd__list"
                role="listbox"
                :aria-activedescendant="
                    focusedIndex >= 0 ? `bd-option-${focusedIndex}` : undefined
                "
            >
                <li
                    v-for="(option, index) in options"
                    :id="`bd-option-${index}`"
                    :key="String(option.value)"
                    class="bd__option"
                    role="option"
                    :aria-selected="option.value === modelValue"
                    :aria-disabled="option.disabled"
                    :class="{
                        'bd__option--selected': option.value === modelValue,
                        'bd__option--disabled': option.disabled,
                        'bd__option--focused': index === focusedIndex,
                    }"
                    @mousedown.prevent="select(option)"
                    @mouseenter="!option.disabled && (focusedIndex = index)"
                >
                    <slot name="option" :option="option">
                        <span class="bd__option-label">{{ option.label }}</span>
                    </slot>
                    <i
                        v-if="option.value === modelValue"
                        class="pi pi-check bd__option-check"
                        aria-hidden="true"
                    />
                </li>

                <li v-if="options.length === 0" class="bd__empty">
                    Brak opcji
                </li>
            </ul>
        </Transition>
    </div>
</template>

<style scoped>
/* ── Base wrapper ── */
.bd {
    position: relative;
    display: flex;
    flex-direction: column;
}

/* ── Trigger ── */
.bd__trigger {
    position: relative;
    display: flex;
    align-items: center;
    min-height: 36px;

    background: rgba(10, 16, 32, 0.72);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);

    border: 1px solid rgba(56, 189, 248, 0.12);
    border-radius: 8px;

    overflow: hidden;
    cursor: pointer;
    user-select: none;
    outline: none;

    transition:
        border-color 0.2s ease,
        box-shadow 0.2s ease,
        background 0.2s ease,
        border-radius 0.15s ease;

    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.03),
        0 4px 12px rgba(0, 0, 0, 0.2);
}

.bd__trigger::before {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.03), transparent);
    pointer-events: none;
}

.bd:not(.bd--disabled) .bd__trigger:hover {
    border-color: rgba(56, 189, 248, 0.22);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.04),
        0 6px 18px rgba(0, 0, 0, 0.25);
}

.bd--focused .bd__trigger {
    border-color: rgba(56, 189, 248, 0.4);
    background: rgba(10, 16, 32, 0.85);
    box-shadow:
        0 0 0 3px rgba(56, 189, 248, 0.07),
        0 8px 24px rgba(0, 0, 0, 0.3);
}

.bd--open .bd__trigger {
    border-bottom-left-radius: 0;
    border-bottom-right-radius: 0;
    border-bottom-color: transparent;
}

.bd--error .bd__trigger {
    border-color: rgba(248, 113, 113, 0.45);
}

.bd--error.bd--focused .bd__trigger {
    border-color: rgba(248, 113, 113, 0.6);
    box-shadow:
        0 0 0 3px rgba(248, 113, 113, 0.08),
        0 8px 24px rgba(0, 0, 0, 0.3);
}

.bd--disabled .bd__trigger {
    opacity: 0.5;
    cursor: not-allowed;
}

/* ── Value ── */
.bd__value {
    flex: 1;
    min-width: 0;
    padding: 0.5rem 0.4rem 0.5rem 0.8rem;
    color: #e2e8f0;
    font-size: 0.8125rem;
    font-weight: 400;
    letter-spacing: 0.01em;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: color 0.15s ease;
}

.bd__value--placeholder {
    color: rgba(148, 163, 184, 0.5);
}

/* ── Prefix / Suffix ── */
.bd__prefix,
.bd__suffix {
    display: flex;
    align-items: center;
    flex-shrink: 0;
    color: rgba(148, 163, 184, 0.7);
    z-index: 1;
}

.bd__prefix {
    padding-left: 0.8rem;
}

.bd__prefix + .bd__value {
    padding-left: 0.4rem;
}

.bd__suffix {
    padding-right: 0.4rem;
}

.bd__icon {
    color: rgba(56, 189, 248, 0.55);
    font-size: 0.8rem;
}

/* ── Clear button ── */
.bd__clear {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    width: 20px;
    height: 20px;
    margin-right: 0.15rem;
    padding: 0;
    border: none;
    border-radius: 50%;
    background: rgba(148, 163, 184, 0.12);
    color: rgba(148, 163, 184, 0.6);
    font-size: 0.6rem;
    cursor: pointer;
    transition:
        background 0.15s ease,
        color 0.15s ease;
}

.bd__clear:hover {
    background: rgba(248, 113, 113, 0.18);
    color: rgba(248, 113, 113, 0.9);
}

/* ── Chevron ── */
.bd__chevron {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    padding: 0 0.7rem 0 0.3rem;
    color: rgba(56, 189, 248, 0.45);
    font-size: 0.7rem;
    transition:
        transform 0.2s cubic-bezier(0.4, 0, 0.2, 1),
        color 0.2s ease,
        opacity 0.15s ease;
    will-change: transform;
}

.bd--open .bd__chevron {
    transform: rotate(180deg);
    color: #38bdf8;
}

/* Shrink chevron slightly when clear is showing so it doesn't crowd */
.bd__chevron--hidden {
    opacity: 0.3;
}

/* ── Clear transition ── */
.bd-clear-enter-active,
.bd-clear-leave-active {
    transition:
        opacity 0.15s ease,
        transform 0.15s ease;
}

.bd-clear-enter-from,
.bd-clear-leave-to {
    opacity: 0;
    transform: scale(0.6);
}

/* ── Dropdown list ── */
.bd__list {
    position: absolute;
    top: calc(100% - 1px);
    left: 0;
    right: 0;
    z-index: 9999;

    background: rgba(8, 14, 28, 0.97);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);

    border: 1px solid rgba(56, 189, 248, 0.4);
    border-top: 1px solid rgba(56, 189, 248, 0.08);
    border-radius: 0 0 8px 8px;

    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.55);

    margin: 0;
    padding: 3px;
    list-style: none;

    max-height: 210px;
    overflow-y: auto;
    overflow-x: hidden;

    scrollbar-width: thin;
    scrollbar-color: rgba(56, 189, 248, 0.2) transparent;
}

.bd--error .bd__list {
    border-color: rgba(248, 113, 113, 0.45);
    border-top-color: rgba(248, 113, 113, 0.1);
}

.bd__list::-webkit-scrollbar {
    width: 4px;
}

.bd__list::-webkit-scrollbar-track {
    background: transparent;
}

.bd__list::-webkit-scrollbar-thumb {
    background: rgba(56, 189, 248, 0.25);
    border-radius: 2px;
}

/* ── Option ── */
.bd__option {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.4rem;

    padding: 0.4rem 0.65rem;
    border-radius: 6px;

    cursor: pointer;
    color: rgba(226, 232, 240, 0.8);
    font-size: 0.8125rem;
    font-weight: 400;
    letter-spacing: 0.01em;

    transition:
        background 0.1s ease,
        color 0.1s ease;
}

.bd__option--focused {
    background: rgba(56, 189, 248, 0.08);
    color: #e2e8f0;
}

.bd__option--selected {
    background: rgba(56, 189, 248, 0.12);
    color: #38bdf8;
    font-weight: 500;
}

.bd__option--selected.bd__option--focused {
    background: rgba(56, 189, 248, 0.18);
}

.bd__option--disabled {
    opacity: 0.35;
    cursor: not-allowed;
}

.bd__option-label {
    flex: 1;
    min-width: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.bd__option-check {
    font-size: 0.65rem;
    color: #38bdf8;
    flex-shrink: 0;
}

/* ── Empty state ── */
.bd__empty {
    padding: 0.5rem;
    text-align: center;
    font-size: 0.75rem;
    color: rgba(148, 163, 184, 0.35);
    font-style: italic;
}

/* ── List transition ── */
.bd-list-enter-active {
    transition:
        opacity 0.18s ease,
        transform 0.18s cubic-bezier(0.16, 1, 0.3, 1);
}

.bd-list-leave-active {
    transition:
        opacity 0.14s ease,
        transform 0.14s ease;
}

.bd-list-enter-from {
    opacity: 0;
    transform: translateY(-6px) scaleY(0.94);
    transform-origin: top center;
}

.bd-list-leave-to {
    opacity: 0;
    transform: translateY(-4px) scaleY(0.96);
    transform-origin: top center;
}
</style>
