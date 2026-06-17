import { computed } from "vue";
import { route } from "ziggy-js";
import { usePermissions } from "./usePermissions";
import { useFeatures } from "./useFeatures";

export interface MenuItem {
    label: string;
    name?: string;
    url?: string;
    icon?: string;
    color?: string;
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
            icon: "pi-home",
        },
        {
            label: "Magazyn",
            feature: "inventory",
            icon: "pi-box",
            color: "#f97316",
            children: [
                {
                    label: "Płyty",
                    name: "tenant.inventory.records.index",
                    url: route("tenant.inventory.records.index"),
                    icon: "pi-database",
                },
                {
                    label: "Ruchy magazynowe",
                    name: "tenant.inventory.movements.index",
                    url: route("tenant.inventory.movements.index"),
                    icon: "pi-sort-alt",
                },
                {
                    label: "Sprzedaż",
                    name: "tenant.inventory.sales.index",
                    url: route("tenant.inventory.sales.index"),
                    icon: "pi-shopping-cart",
                    permission: "inventory-sales-read",
                },
            ],
        },
        {
            label: "Giełdy",
            feature: "trading",
            icon: "pi-shop",
            color: "#4ade80",
            children: [
                {
                    label: "Wydarzenia",
                    name: "tenant.trading.events.index",
                    url: route("tenant.trading.events.index"),
                    icon: "pi-calendar",
                },
                {
                    label: "Nowe wydarzenie",
                    name: "tenant.trading.events.create",
                    url: route("tenant.trading.events.create"),
                    icon: "pi-plus-circle",
                    permission: "events.create",
                },
                {
                    label: "Listing płyt",
                    name: "tenant.trading.listings.index",
                    url: route("tenant.trading.listings.index"),
                    icon: "pi-list",
                },
                {
                    label: "Sprzedaż",
                    name: "tenant.trading.sales.index",
                    url: route("tenant.trading.sales.index"),
                    icon: "pi-shopping-cart",
                },
                {
                    label: "Statystyki wydarzeń",
                    name: "tenant.trading.analytics",
                    url: route("tenant.trading.analytics"),
                    icon: "pi-chart-line",
                    feature: "analytics.basic",
                },
            ],
        },
        {
            label: "Analityka",
            feature: "analytics",
            permission: "analytics-read",
            icon: "pi-chart-bar",
            color: "#a78bfa",
            children: [
                {
                    label: "Podsumowanie",
                    name: "tenant.analytics.overview",
                    url: route("tenant.analytics.overview"),
                    icon: "pi-chart-pie",
                },
                {
                    label: "Sprzedaż",
                    name: "tenant.analytics.sales",
                    url: route("tenant.analytics.sales"),
                    icon: "pi-dollar",
                },
                {
                    label: "Top artyści",
                    name: "tenant.analytics.artists",
                    url: route("tenant.analytics.artists"),
                    icon: "pi-star",
                },
                {
                    label: "Raporty",
                    name: "tenant.analytics.reports",
                    url: route("tenant.analytics.reports"),
                    icon: "pi-file",
                    feature: "analytics",
                },
            ],
        },
        {
            label: "Integracje",
            feature: "integrations",
            icon: "pi-link",
            color: "#60a5fa",
            children: [
                {
                    label: "Allegro",
                    name: "tenant.integrations.allegro",
                    url: route("tenant.integrations.allegro"),
                    icon: "pi-shopping-bag",
                },
                {
                    label: "KSeF",
                    name: "tenant.integrations.ksef",
                    url: route("tenant.integrations.ksef"),
                    icon: "pi-receipt",
                },
                {
                    label: "API",
                    name: "tenant.integrations.api",
                    url: route("tenant.integrations.api"),
                    icon: "pi-code",
                    feature: "api",
                },
            ],
        },
        {
            label: "Użytkownicy",
            feature: "users",
            icon: "pi-users",
            color: "#f472b6",
            children: [
                {
                    label: "Lista użytkowników",
                    name: "tenant.users.index",
                    url: route("tenant.users.index"),
                    icon: "pi-users",
                    permission: "users-read",
                },
                {
                    label: "Zaproszenia",
                    name: "tenant.users.invites",
                    url: route("tenant.users.invites"),
                    icon: "pi-user-plus",
                    permission: "users.invite",
                },
            ],
        },
        {
            label: "Ustawienia",
            icon: "pi-cog",
            color: "#94a3b8",
            children: [
                {
                    label: "Profil",
                    name: "tenant.settings.profile",
                    url: route("tenant.settings.profile"),
                    icon: "pi-user",
                },
                {
                    label: "Organizacja",
                    name: "tenant.settings.organization",
                    url: route("tenant.settings.organization"),
                    icon: "pi-briefcase",
                    feature: "settings",
                    permission: "setting-organization",
                },
                {
                    label: "Subskrypcja",
                    name: "tenant.settings.billing",
                    url: route("tenant.settings.billing"),
                    icon: "pi-credit-card",
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
