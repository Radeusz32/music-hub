import type { ColumnDef, FilterOption } from "@/types/datatable";

export type { ColumnDef, FilterOption };

/* ── Types ── */

export interface MovementRecordRef {
    id: number;
    name: string;
    artist: string;
}

export interface MovementUser {
    id: number;
    name: string;
}

export interface InventoryMovement {
    id: number;
    inventory_record_id: number;
    type: string;
    quantity: number;
    quantity_before: number;
    quantity_after: number;
    delta: number;
    note: string | null;
    inventoryRecord: MovementRecordRef | null;
    user: MovementUser | null;
    created_at: string;
}

export interface RecordOption {
    value: number;
    label: string;
    quantity: number;
}

export interface MovementFormData {
    inventory_record_id: number | string;
    type: string;
    quantity: number;
    note: string;
}

export const defaultMovementForm: MovementFormData = {
    inventory_record_id: "",
    type: "in",
    quantity: 1,
    note: "",
};

/* ── Display helpers ── */

const FALLBACK_COLOR = "#94a3b8";

export function resolveOptionLabel(
    options: FilterOption[],
    value: string,
): string {
    return options.find((o) => o.value === value)?.label ?? value;
}

export function resolveOptionColor(
    options: FilterOption[],
    value: string,
): string {
    return options.find((o) => o.value === value)?.color ?? FALLBACK_COLOR;
}

/* ── Column definitions ── */

export interface BuildColumnsOptions {
    typeOptions: FilterOption[];
}

export function buildMovementColumns(
    options: BuildColumnsOptions,
): ColumnDef[] {
    return [
        {
            key: "created_at",
            label: "Data",
            sortable: true,
            width: "180px",
            filter: {
                type: "date-range",
                fromKey: "created_at_from",
                toKey: "created_at_to",
            },
        },
        { key: "inventoryRecord", label: "Płyta" },
        {
            key: "type",
            label: "Typ",
            sortable: true,
            width: "150px",
            filter: { type: "select", options: options.typeOptions },
        },
        { key: "delta", label: "Zmiana", width: "120px", align: "right" },
        {
            key: "quantity_after",
            label: "Stan po",
            sortable: true,
            width: "120px",
            align: "right",
        },
        { key: "note", label: "Powód" },
        { key: "user", label: "Użytkownik", width: "150px" },
    ];
}
