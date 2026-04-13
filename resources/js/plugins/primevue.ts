import type { App } from "vue";
import PrimeVue from "primevue/config";

export function registerPrimeVue(app: App): void {
    app.use(PrimeVue, {
        unstyled: true,
        ripple: true,
    });
}
