import type { App } from "vue";

import Button from "primevue/button";
import InputText from "primevue/inputtext";
import Password from "primevue/password";
import Checkbox from "primevue/checkbox";
import Card from "primevue/card";

export function registerPrimeComponents(app: App): void {
    app.component("Button", Button);
    app.component("InputText", InputText);
    app.component("Password", Password);
    app.component("Checkbox", Checkbox);
    app.component("Card", Card);
}
