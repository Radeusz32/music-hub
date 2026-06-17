<script setup lang="ts">
import { computed } from "vue";
import type { StepDefinition } from "@/composables/useStepByStep";

defineOptions({ name: "StepByStep" });

interface Props {
    steps: StepDefinition[];
    currentIndex: number;
    completed: number[];
    validating?: boolean;
    isFirst: boolean;
    isLast: boolean;
    nextLabel?: string;
    finalLabel?: string;
    finalProcessing?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    validating: false,
    nextLabel: "Dalej",
    finalLabel: "Zakończ",
    finalProcessing: false,
});

const emit = defineEmits<{
    (e: "next"): void;
    (e: "prev"): void;
    (e: "go", index: number): void;
    (e: "finish"): void;
}>();

const current = computed(() => props.steps[props.currentIndex]);

type Status = "completed" | "current" | "pending";

function statusOf(index: number): Status {
    if (props.completed.includes(index) && index !== props.currentIndex) {
        return "completed";
    }
    return index === props.currentIndex ? "current" : "pending";
}

function canAccess(index: number): boolean {
    return index <= props.currentIndex || props.completed.includes(index);
}

function onStepClick(index: number): void {
    if (index !== props.currentIndex && canAccess(index)) {
        emit("go", index);
    }
}
</script>

<template>
    <div class="flex flex-col gap-7">
        <!-- Stepper header -->
        <ol class="flex items-start">
            <li
                v-for="(step, index) in steps"
                :key="step.key"
                class="relative flex flex-1 flex-col items-center text-center"
            >
                <!-- Connector -->
                <div
                    v-if="index < steps.length - 1"
                    class="absolute left-1/2 top-5 h-0.5 w-full"
                    :class="
                        completed.includes(index)
                            ? 'bg-gradient-to-r from-fuchsia-500 to-indigo-500'
                            : 'bg-slate-700/40'
                    "
                    aria-hidden="true"
                />

                <!-- Circle -->
                <button
                    type="button"
                    class="step-circle"
                    :class="[
                        `step-circle--${statusOf(index)}`,
                        canAccess(index) && index !== currentIndex
                            ? 'cursor-pointer'
                            : 'cursor-default',
                    ]"
                    :disabled="!canAccess(index)"
                    @click="onStepClick(index)"
                >
                    <i
                        v-if="statusOf(index) === 'completed'"
                        class="pi pi-check text-sm"
                    />
                    <i
                        v-else-if="step.icon"
                        :class="step.icon"
                        class="text-sm"
                    />
                    <span v-else class="text-sm font-semibold">{{
                        index + 1
                    }}</span>
                </button>

                <!-- Labels -->
                <div class="mt-2.5 flex flex-col gap-0.5 px-1">
                    <span
                        class="text-[0.62rem] font-semibold uppercase tracking-wider text-slate-500"
                    >
                        Krok {{ index + 1 }}
                    </span>
                    <span
                        class="text-xs font-semibold leading-tight transition-colors sm:text-sm"
                        :class="
                            statusOf(index) === 'pending'
                                ? 'text-slate-500'
                                : 'text-slate-100'
                        "
                    >
                        {{ step.label }}
                    </span>
                </div>
            </li>
        </ol>

        <!-- Active step body -->
        <div class="flex flex-col gap-1">
            <div
                v-if="current?.description"
                class="text-sm text-slate-400"
            >
                {{ current.description }}
            </div>
            <slot :name="current?.key" :step="current" />
        </div>

        <!-- Footer navigation -->
        <div class="flex items-center justify-between gap-3">
            <button
                v-if="!isFirst"
                type="button"
                class="nav-btn nav-btn--ghost"
                :disabled="validating || finalProcessing"
                @click="emit('prev')"
            >
                <i class="pi pi-arrow-left" />
                Wstecz
            </button>
            <span v-else />

            <button
                v-if="!isLast"
                type="button"
                class="nav-btn nav-btn--primary"
                :disabled="validating"
                @click="emit('next')"
            >
                {{ validating ? "Sprawdzanie..." : nextLabel }}
                <i
                    :class="
                        validating
                            ? 'pi pi-spin pi-spinner'
                            : 'pi pi-arrow-right'
                    "
                />
            </button>
            <button
                v-else
                type="button"
                class="nav-btn nav-btn--primary"
                :disabled="finalProcessing"
                @click="emit('finish')"
            >
                <i
                    :class="
                        finalProcessing
                            ? 'pi pi-spin pi-spinner'
                            : 'pi pi-check'
                    "
                />
                {{ finalProcessing ? "Wysyłanie..." : finalLabel }}
            </button>
        </div>
    </div>
</template>

<style scoped>
.step-circle {
    position: relative;
    z-index: 10;
    display: inline-flex;
    height: 2.5rem;
    width: 2.5rem;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    border-radius: 9999px;
    transition: all 0.2s ease;
}
.step-circle:disabled {
    pointer-events: none;
}
.step-circle--completed,
.step-circle--current {
    background: linear-gradient(135deg, #d946ef 0%, #818cf8 100%);
    color: #fff;
}
.step-circle--current {
    box-shadow: 0 0 0 4px rgba(217, 70, 239, 0.16);
}
.step-circle--completed:hover {
    box-shadow: 0 0 0 4px rgba(217, 70, 239, 0.12);
}
.step-circle--pending {
    background: rgba(2, 6, 23, 0.6);
    border: 1px solid rgba(71, 85, 105, 0.6);
    color: rgba(148, 163, 184, 0.7);
}

.nav-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.62rem 1.4rem;
    border-radius: 10px;
    font-size: 0.88rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.18s;
}
.nav-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
.nav-btn--ghost {
    background: rgba(148, 163, 184, 0.07);
    border: 1px solid rgba(148, 163, 184, 0.18);
    color: #cbd5e1;
}
.nav-btn--ghost:hover:not(:disabled) {
    background: rgba(148, 163, 184, 0.13);
}
.nav-btn--primary {
    background: linear-gradient(135deg, #d946ef 0%, #818cf8 100%);
    border: none;
    color: #fff;
    box-shadow: 0 0 22px rgba(217, 70, 239, 0.22);
}
.nav-btn--primary:hover:not(:disabled) {
    opacity: 0.92;
}
</style>
