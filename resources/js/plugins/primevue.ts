import type { App } from "vue";
import PrimeVue from "primevue/config";
import ToastService from "primevue/toastservice";
import ConfirmationService from "primevue/confirmationservice";

export function registerPrimeVue(app: App): void {
    app.use(PrimeVue, {
        unstyled: true,
        ripple: true,
    });
    app.use(ToastService);
    app.use(ConfirmationService);
}
