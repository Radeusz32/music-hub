import {
    buildInventoryColumns,
    type FilterOption,
    type InventoryFormData,
    type InventoryRecord,
    defaultInventoryForm,
} from "@/Pages/Tenant/Inventory/inventory.resource";
import { useTable, type TableFilters } from "./useTable";
import { useMoney } from "@/composables/useMoney";
import { useDate } from "@/composables/useDate";
export interface UseInventoryTableOptions {
    initialFilters?: TableFilters;
    formatOptions: FilterOption[];
    conditionOptions: FilterOption[];
}

export function useInventoryTable(options: UseInventoryTableOptions) {
    const table = useTable({
        routeName: "tenant.inventory.records.index",
        initialFilters: options.initialFilters,
        defaultSort: "name",
        defaultDirection: "asc",
        defaultPerPage: 20,
    });

    const { formatPrice } = useMoney();
    const { formatDate } = useDate();

    const columns = buildInventoryColumns({
        formatOptions: options.formatOptions,
        conditionOptions: options.conditionOptions,
    });

    /* ── Display helpers ── */

    function formatLabel(value: string): string {
        return (
            options.formatOptions.find((o) => o.value === value)?.label ?? value
        );
    }

    function conditionLabel(value: string): string {
        return (
            options.conditionOptions.find((o) => o.value === value)?.label ??
            value
        );
    }

    function formatBadgeStyle(value: string): Record<string, string> {
        const opt = options.formatOptions.find((o) => o.value === value);
        const color = opt?.color ?? "#94a3b8";
        return {
            color,
            borderColor: `${color}40`,
            background: `${color}12`,
        };
    }

    function conditionDotStyle(value: string): Record<string, string> {
        const opt = options.conditionOptions.find((o) => o.value === value);
        const color = opt?.color ?? "#94a3b8";
        return {
            background: color,
            boxShadow: `0 0 6px ${color}80`,
        };
    }

    function quantityClass(quantity: number): string {
        if (quantity === 0) return "text-red-400";
        if (quantity <= 3) return "text-orange-400";
        return "text-emerald-400";
    }

    /* ── Form helpers ── */

    function formFromRecord(record: InventoryRecord): InventoryFormData {
        return {
            name: record.name,
            artist: record.artist,
            genre: record.genre,
            format: record.format,
            condition: record.condition,
            label: record.label ?? "",
            catalog_number: record.catalog_number ?? "",
            barcode: record.barcode ?? "",
            country: record.country ?? "",
            year: record.year ?? "",
            quantity: record.quantity,
            purchase_price: record.purchase_price ?? "",
            sale_price: record.sale_price ?? "",
            notes: record.notes ?? "",
        };
    }

    return {
        /* table state + actions */
        ...table,

        /* column config */
        columns,

        /* display helpers */
        formatLabel,
        conditionLabel,
        formatBadgeStyle,
        conditionDotStyle,
        quantityClass,
        formatPrice,
        formatDate,

        /* form helpers */
        defaultInventoryForm,
        formFromRecord,
    };
}
