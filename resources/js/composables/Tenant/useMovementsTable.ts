import {
    buildMovementColumns,
    type FilterOption,
} from "@/Pages/Tenant/Inventory/movements.resource";
import { useTable, type TableFilters } from "./useTable";
import { useDate } from "@/composables/useDate";

export interface UseMovementsTableOptions {
    initialFilters?: TableFilters;
    typeOptions: FilterOption[];
}

export function useMovementsTable(options: UseMovementsTableOptions) {
    const table = useTable({
        routeName: "tenant.inventory.movements.index",
        initialFilters: options.initialFilters,
        defaultSort: "created_at",
        defaultDirection: "desc",
        defaultPerPage: 20,
    });

    const { formatDate } = useDate();

    const columns = buildMovementColumns({
        typeOptions: options.typeOptions,
    });

    /* ── Display helpers ── */

    function typeLabel(value: string): string {
        return (
            options.typeOptions.find((o) => o.value === value)?.label ?? value
        );
    }

    function typeBadgeStyle(value: string): Record<string, string> {
        const opt = options.typeOptions.find((o) => o.value === value);
        const color = opt?.color ?? "#94a3b8";
        return {
            color,
            borderColor: `${color}40`,
            background: `${color}12`,
        };
    }

    function deltaClass(delta: number): string {
        if (delta > 0) return "text-emerald-400";
        if (delta < 0) return "text-red-400";
        return "text-slate-400";
    }

    function formatDelta(delta: number): string {
        return delta > 0 ? `+${delta}` : String(delta);
    }

    return {
        /* table state + actions */
        ...table,

        /* column config */
        columns,

        /* display helpers */
        typeLabel,
        typeBadgeStyle,
        deltaClass,
        formatDelta,
        formatDate,
    };
}
