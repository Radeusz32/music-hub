import { computed } from "vue";
import { route } from "ziggy-js";
import { usePermissions } from "./usePermissions";
import { useFeatures } from "./useFeatures";

export interface MenuItem {
    label: string;
    name?: string;
    url?: string;
    permission?: string;
    feature?: string;
    visible?: boolean | (() => boolean);
    children?: MenuItem[];
}

export function useMenu() {
    const { hasPermission } = usePermissions();
    const { hasFeature } = useFeatures();

    const isVisible = (visible?: boolean | (() => boolean)): boolean => {
        if (typeof visible === "function") {
            return visible();
        }

        return visible ?? true;
    };

    const rawMenu: MenuItem[] = [
        {
            label: "Panel",
            name: "tenant.dashboard",
            url: route("tenant.dashboard"),
        },
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
                    label: "Ruchy magazynowe",
                    name: "tenant.inventory.movements.index",
                    url: route("tenant.inventory.movements.index"),
                },
            ],
        },
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
            ],
        },
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
                    visible: true,
                },
            ],
        },
    ];

    const filterMenu = (items: MenuItem[]): MenuItem[] => {
        return items
            .filter(
                (item) =>
                    hasFeature(item.feature) &&
                    hasPermission(item.permission) &&
                    isVisible(item.visible),
            )
            .map((item) => ({
                ...item,
                children: item.children ? filterMenu(item.children) : undefined,
            }))
            .filter(
                (item) => item.url || (item.children && item.children.length),
            );
    };

    const menu = computed<MenuItem[]>(() => filterMenu(rawMenu));

    return { menu };
}
