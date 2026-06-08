import { readonly, ref } from "vue";

type ToastType = "success" | "error";

interface ToastItem {
    id: number;
    type: ToastType;
    message: string;
}

const toasts = ref<ToastItem[]>([]);
let counter = 0;

export function useToast() {
    function add(type: ToastType, message: string, duration = 5000): void {
        const id = ++counter;
        toasts.value.push({ id, type, message });
        setTimeout(() => dismiss(id), duration);
    }

    function dismiss(id: number): void {
        const idx = toasts.value.findIndex((t) => t.id === id);
        if (idx !== -1) toasts.value.splice(idx, 1);
    }

    return {
        toasts: readonly(toasts),
        success: (message: string) => add("success", message),
        error: (message: string) => add("error", message),
        dismiss,
    };
}
