import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";

export function usePermissions() {
    const page = usePage();

    const permissions = computed<string[]>(
        () => page.props.auth?.permissions ?? [],
    );

    const hasPermission = (permission?: string): boolean =>
        !permission || permissions.value.includes(permission);

    return { permissions, hasPermission };
}
