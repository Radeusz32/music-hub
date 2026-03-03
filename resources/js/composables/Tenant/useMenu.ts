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
