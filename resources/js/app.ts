import "../css/app.css";
import "./bootstrap";

import { createApp, h, type DefineComponent } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "ziggy-js";

import { registerPrimeVue } from "./plugins/primevue";
import { registerPrimeComponents } from "./plugins/prime-components";
import { registerBaseComponents } from "./plugins/base-components";

const appName = import.meta.env.VITE_APP_NAME || "MusicHub";

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),

    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>("./Pages/**/*.vue"),
        ),

    setup({ el, App, props, plugin }) {
        const vueApp = createApp({
            render: () => h(App, props),
        });

        vueApp.use(plugin);
        vueApp.use(ZiggyVue);

        registerPrimeVue(vueApp);
        registerPrimeComponents(vueApp);
        registerBaseComponents(vueApp);

        vueApp.mount(el);
    },

    progress: {
        color: "#4B5563",
    },
});
