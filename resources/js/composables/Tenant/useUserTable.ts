import {
    buildUserColumns,
    defaultUserForm,
    type FilterOption,
    type UserFormData,
    type UserRow,
} from "@/Pages/Tenant/Users/users.resource";
import { useTable, type TableFilters } from "./useTable";
import { useDate } from "@/composables/useDate";

export interface UseUserTableOptions {
    initialFilters?: TableFilters;
    roleOptions: FilterOption[];
}

export function useUserTable(options: UseUserTableOptions) {
    const table = useTable({
        routeName: "tenant.users.index",
        initialFilters: options.initialFilters,
        defaultSort: "created_at",
        defaultDirection: "desc",
        defaultPerPage: 20,
    });

    const { formatDate } = useDate();

    const columns = buildUserColumns({ roleOptions: options.roleOptions });

    /* ── Display helpers ── */

    function roleLabel(value: string | null): string {
        if (!value) {
            return "—";
        }
        return (
            options.roleOptions.find((o) => o.value === value)?.label ?? value
        );
    }

    function roleBadgeStyle(value: string | null): Record<string, string> {
        const color =
            options.roleOptions.find((o) => o.value === value)?.color ??
            "#94a3b8";
        return {
            color,
            borderColor: `${color}40`,
            background: `${color}12`,
        };
    }

    function activeBadgeStyle(isActive: boolean): Record<string, string> {
        const color = isActive ? "#4ade80" : "#f87171";
        return {
            color,
            borderColor: `${color}40`,
            background: `${color}12`,
        };
    }

    /* ── Form helpers ── */

    function formFromUser(user: UserRow): UserFormData {
        return {
            first_name: user.first_name,
            last_name: user.last_name,
            email: user.email,
            phone: user.phone ?? "",
            street: user.street ?? "",
            building_number: user.building_number ?? "",
            apartment_number: user.apartment_number ?? "",
            postal_code: user.postal_code ?? "",
            city: user.city ?? "",
            pesel: user.pesel ?? "",
            is_active: user.is_active,
            password: "",
            role: user.role ?? "user",
        };
    }

    return {
        /* table state + actions */
        ...table,

        /* column config */
        columns,

        /* display helpers */
        roleLabel,
        roleBadgeStyle,
        activeBadgeStyle,
        formatDate,

        /* form helpers */
        defaultUserForm,
        formFromUser,
    };
}
