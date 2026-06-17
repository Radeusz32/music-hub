import { computed, ref, watch, type ComputedRef, type Ref } from "vue";

export interface StepDefinition {
    /** Unique key - also used as the slot name in StepByStep.vue. */
    key: string;
    label: string;
    description?: string;
    /** PrimeIcons class, e.g. "pi pi-building". */
    icon?: string;
    /** Form field names that belong to this step (used for persistence). */
    fields?: string[];
}

export interface UseStepByStepOptions<T extends object> {
    steps: StepDefinition[];
    /** Reactive form data (e.g. an Inertia `useForm` object). */
    data: T;
    /** localStorage key - when set, progress + field values are persisted. */
    storageKey?: string;
    /** Field names that must never be written to localStorage (e.g. passwords). */
    excludeFromStorage?: string[];
    /**
     * Server-side validation for a single step. Resolve `true` to advance,
     * `false` to stay (errors are surfaced by the callback itself).
     */
    onValidateStep?: (step: StepDefinition, data: T) => Promise<boolean>;
}

export interface UseStepByStepReturn {
    steps: StepDefinition[];
    currentIndex: Ref<number>;
    currentStep: ComputedRef<StepDefinition>;
    completed: Ref<number[]>;
    validating: Ref<boolean>;
    isFirst: ComputedRef<boolean>;
    isLast: ComputedRef<boolean>;
    progress: ComputedRef<number>;
    next: () => Promise<boolean>;
    prev: () => void;
    goTo: (index: number) => Promise<void>;
    canAccess: (index: number) => boolean;
    clearStorage: () => void;
}

export function useStepByStep<T extends object>(
    options: UseStepByStepOptions<T>,
): UseStepByStepReturn {
    const { steps, data, storageKey } = options;
    const exclude = options.excludeFromStorage ?? [];

    const persistableFields = steps
        .flatMap((step) => step.fields ?? [])
        .filter((field) => !exclude.includes(field));

    const currentIndex = ref(0);
    const completed = ref<number[]>([]);
    const validating = ref(false);

    /* ── Hydrate from localStorage ── */
    if (storageKey) {
        const raw = localStorage.getItem(storageKey);
        if (raw) {
            try {
                const parsed = JSON.parse(raw) as {
                    data?: Record<string, unknown>;
                    currentIndex?: number;
                    completed?: number[];
                };
                if (parsed.data) {
                    for (const field of persistableFields) {
                        if (field in parsed.data) {
                            (data as Record<string, unknown>)[field] =
                                parsed.data[field];
                        }
                    }
                }
                if (typeof parsed.currentIndex === "number") {
                    currentIndex.value = Math.min(
                        parsed.currentIndex,
                        steps.length - 1,
                    );
                }
                if (Array.isArray(parsed.completed)) {
                    completed.value = parsed.completed;
                }
            } catch {
                localStorage.removeItem(storageKey);
            }
        }
    }

    /* ── Persist on change ── */
    function persist(): void {
        if (!storageKey) {
            return;
        }
        const snapshot: Record<string, unknown> = {};
        for (const field of persistableFields) {
            snapshot[field] = (data as Record<string, unknown>)[field];
        }
        localStorage.setItem(
            storageKey,
            JSON.stringify({
                data: snapshot,
                currentIndex: currentIndex.value,
                completed: completed.value,
            }),
        );
    }

    if (storageKey) {
        watch(
            [
                () =>
                    persistableFields.map(
                        (f) => (data as Record<string, unknown>)[f],
                    ),
                currentIndex,
                completed,
            ],
            persist,
            { deep: true },
        );
    }

    const currentStep = computed(() => steps[currentIndex.value]);
    const isFirst = computed(() => currentIndex.value === 0);
    const isLast = computed(() => currentIndex.value === steps.length - 1);
    const progress = computed(() =>
        steps.length <= 1
            ? 100
            : (currentIndex.value / (steps.length - 1)) * 100,
    );

    function markCompleted(index: number): void {
        if (!completed.value.includes(index)) {
            completed.value = [...completed.value, index];
        }
    }

    async function next(): Promise<boolean> {
        if (options.onValidateStep) {
            validating.value = true;
            try {
                const ok = await options.onValidateStep(
                    currentStep.value,
                    data,
                );
                if (!ok) {
                    return false;
                }
            } finally {
                validating.value = false;
            }
        }

        markCompleted(currentIndex.value);

        if (!isLast.value) {
            currentIndex.value++;
        }

        return true;
    }

    function prev(): void {
        if (!isFirst.value) {
            currentIndex.value--;
        }
    }

    function canAccess(index: number): boolean {
        return index <= currentIndex.value || completed.value.includes(index);
    }

    async function goTo(index: number): Promise<void> {
        if (index === currentIndex.value) {
            return;
        }
        /* Going back (or to an already-completed step) is always allowed. */
        if (index < currentIndex.value || canAccess(index)) {
            currentIndex.value = index;
            return;
        }
        /* Going forward one step runs validation, like pressing "Dalej". */
        if (index === currentIndex.value + 1) {
            await next();
        }
    }

    function clearStorage(): void {
        if (storageKey) {
            localStorage.removeItem(storageKey);
        }
    }

    return {
        steps,
        currentIndex,
        currentStep,
        completed,
        validating,
        isFirst,
        isLast,
        progress,
        next,
        prev,
        goTo,
        canAccess,
        clearStorage,
    };
}
