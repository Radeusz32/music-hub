import { buildTenantColumns } from "@/Pages/Central/Tenants/tenants.resource";
import { useTable, type TableFilters } from "@/composables/Tenant/useTable";
import { useDate } from "@/composables/useDate";

export interface UseTenantTableOptions {
    initialFilters?: TableFilters;
}

export function useTenantTable(options: UseTenantTableOptions) {
    const table = useTable({
        routeName: "central.tenants.index",
        initialFilters: options.initialFilters,
        defaultSort: "company_name",
        defaultDirection: "asc",
        defaultPerPage: 20,
    });

    const { formatDate } = useDate();

    const columns = buildTenantColumns();

    return {
        ...table,
        columns,
        formatDate,
    };
}
