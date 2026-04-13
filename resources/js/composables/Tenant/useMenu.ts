// resources/js/composables/Tenant/useMenu.ts

import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";

export interface MenuItem {
    label: string;
    name?: string;
    url?: string;
    permission?: string;
    feature?: string;
    children?: MenuItem[];
}

export function useMenu() {
    const page = usePage();
    console.log(usePage().props);
    console.log(page.props.auth);
    console.log(page.props.auth?.user);

    const permissions = computed<string[]>(
        () => page.props.auth?.permissions ?? [],
    );

    const features = computed<string[]>(() => page.props.auth?.features ?? []);

    const hasPermission = (permission?: string) =>
        !permission || permissions.value.includes(permission);

    const hasFeature = (feature?: string) =>
        !feature || features.value.includes(feature);

    const rawMenu: MenuItem[] = [
        {
            label: "Panel",
            name: "tenant.dashboard",
            url: route("tenant.dashboard"),
        },

        /*
    |------------------------------------------------------------------
    | Inventory (WMS)
    |------------------------------------------------------------------
    */
        {
            label: "Magazyn",
            feature: "inventory",
            children: [
                {
                    label: "Płyty",
                    name: "tenant.inventory.records.index",
                    url: route("tenant.inventory.records.index"),
                },
                {
                    label: "Dodaj płytę",
                    name: "tenant.inventory.records.create",
                    url: route("tenant.inventory.records.create"),
                    permission: "records.create",
                },
                {
                    label: "Stany magazynowe",
                    name: "tenant.inventory.stock.index",
                    url: route("tenant.inventory.stock.index"),
                },
                {
                    label: "Ruchy magazynowe",
                    name: "tenant.inventory.movements.index",
                    url: route("tenant.inventory.movements.index"),
                },
                {
                    label: "Alerty stanów",
                    name: "tenant.inventory.alerts",
                    url: route("tenant.inventory.alerts"),
                    feature: "inventory.alerts",
                },
            ],
        },

        /*
    |------------------------------------------------------------------
    | Trading (Giełdy)
    |------------------------------------------------------------------
    */
        {
            label: "Giełdy",
            feature: "trading",
            children: [
                {
                    label: "Wydarzenia",
                    name: "tenant.trading.events.index",
                    url: route("tenant.trading.events.index"),
                },
                {
                    label: "Nowe wydarzenie",
                    name: "tenant.trading.events.create",
                    url: route("tenant.trading.events.create"),
                    permission: "events.create",
                },
                {
                    label: "Listing płyt",
                    name: "tenant.trading.listings.index",
                    url: route("tenant.trading.listings.index"),
                },
                {
                    label: "Sprzedaż",
                    name: "tenant.trading.sales.index",
                    url: route("tenant.trading.sales.index"),
                },
                {
                    label: "Statystyki wydarzeń",
                    name: "tenant.trading.analytics",
                    url: route("tenant.trading.analytics"),
                    feature: "analytics.basic",
                },
            ],
        },

        /*
    |------------------------------------------------------------------
    | Analytics
    |------------------------------------------------------------------
    */
        {
            label: "Analityka",
            feature: "analytics",
            children: [
                {
                    label: "Podsumowanie",
                    name: "tenant.analytics.overview",
                    url: route("tenant.analytics.overview"),
                },
                {
                    label: "Sprzedaż",
                    name: "tenant.analytics.sales",
                    url: route("tenant.analytics.sales"),
                },
                {
                    label: "Top artyści",
                    name: "tenant.analytics.artists",
                    url: route("tenant.analytics.artists"),
                },
                {
                    label: "Raporty",
                    name: "tenant.analytics.reports",
                    url: route("tenant.analytics.reports"),
                    feature: "analytics.advanced",
                },
            ],
        },

        /*
    |------------------------------------------------------------------
    | Integracje (Pro+)
    |------------------------------------------------------------------
    */
        {
            label: "Integracje",
            feature: "integrations",
            children: [
                {
                    label: "Allegro",
                    name: "tenant.integrations.allegro",
                    url: route("tenant.integrations.allegro"),
                },
                {
                    label: "Discogs",
                    name: "tenant.integrations.discogs",
                    url: route("tenant.integrations.discogs"),
                },
                {
                    label: "API",
                    name: "tenant.integrations.api",
                    url: route("tenant.integrations.api"),
                    feature: "api",
                },
            ],
        },

        /*
    |------------------------------------------------------------------
    | Użytkownicy
    |------------------------------------------------------------------
    */
        {
            label: "Użytkownicy",
            feature: "users",
            children: [
                {
                    label: "Lista użytkowników",
                    name: "tenant.users.index",
                    url: route("tenant.users.index"),
                },
                {
                    label: "Zaproszenia",
                    name: "tenant.users.invites",
                    url: route("tenant.users.invites"),
                    permission: "users.invite",
                },
                {
                    label: "Role i uprawnienia",
                    name: "tenant.users.roles",
                    url: route("tenant.users.roles"),
                    permission: "roles.manage",
                },
            ],
        },

        /*
    |------------------------------------------------------------------
    | Ustawienia
    |------------------------------------------------------------------
    */
        {
            label: "Ustawienia",
            children: [
                {
                    label: "Profil",
                    name: "tenant.settings.profile",
                    url: route("tenant.settings.profile"),
                },
                {
                    label: "Organizacja",
                    name: "tenant.settings.organization",
                    url: route("tenant.settings.organization"),
                    permission: "tenant.update",
                },
                {
                    label: "Subskrypcja",
                    name: "tenant.settings.billing",
                    url: route("tenant.settings.billing"),
                    feature: "billing",
                },
            ],
        },
    ];

    const filterMenu = (items: MenuItem[]): MenuItem[] => {
        return items
            .filter(
                (item) =>
                    hasPermission(item.permission) && hasFeature(item.feature),
            )
            .map((item) => ({
                ...item,
                children: item.children ? filterMenu(item.children) : undefined,
            }))
            .filter(
                (item) => item.url || (item.children && item.children.length),
            );
    };

    const menu = computed(() => filterMenu(rawMenu));

    return { menu };
}
