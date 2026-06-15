import type { ColumnDef, FilterOption } from "@/types/datatable";
import type { InventoryMovement } from "./movements.resource";

export type { ColumnDef, FilterOption };

/* ── Types ── */

export interface InventoryRecordUser {
    id: number;
    name: string;
}

export interface InventoryRecord {
    id: number;
    name: string;
    artist: string;
    genre: string;
    format: string;
    condition: string;
    label: string | null;
    catalog_number: string | null;
    barcode: string | null;
    country: string | null;
    year: number | null;
    quantity: number;
    purchase_price_per_unit: string | null;
    cover_image: string | null;
    notes: string | null;
    user: InventoryRecordUser | null;
    movements?: InventoryMovement[];
    created_at: string;
    updated_at: string;
}

export interface InventoryFormData {
    name: string;
    artist: string;
    genre: string;
    format: string;
    condition: string;
    label: string;
    catalog_number: string;
    barcode: string;
    country: string;
    year: number | string;
    quantity: number;
    purchase_price_per_unit: number | string;
    notes: string;
}

export const defaultInventoryForm: InventoryFormData = {
    name: "",
    artist: "",
    genre: "",
    format: "",
    condition: "NM",
    label: "",
    catalog_number: "",
    barcode: "",
    country: "",
    year: "",
    quantity: 1,
    purchase_price_per_unit: "",
    notes: "",
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
    formatOptions: FilterOption[];
    conditionOptions: FilterOption[];
}

export function buildInventoryColumns(
    options: BuildColumnsOptions,
): ColumnDef[] {
    return [
        { key: "name", label: "Tytuł", sortable: true },
        { key: "artist", label: "Artysta", sortable: true },
        { key: "genre", label: "Gatunek", sortable: true },
        {
            key: "format",
            label: "Format",
            sortable: true,
            width: "110px",
            filter: { type: "select", options: options.formatOptions },
        },
        {
            key: "condition",
            label: "Stan",
            sortable: true,
            width: "120px",
            filter: { type: "select", options: options.conditionOptions },
        },
        {
            key: "year",
            label: "Rok",
            sortable: true,
            width: "120px",
            align: "right",
            filter: {
                type: "number-range",
                fromKey: "year_from",
                toKey: "year_to",
                min: 0,
            },
        },
        {
            key: "quantity",
            label: "Ilość",
            sortable: true,
            width: "120px",
            align: "right",
            filter: {
                type: "number-range",
                fromKey: "quantity_from",
                toKey: "quantity_to",
                min: 0,
            },
        },
        { key: "user", label: "Właściciel", width: "130px" },
        {
            key: "created_at",
            label: "Dodano",
            sortable: true,
            width: "180px",
            filter: {
                type: "date-range",
                fromKey: "created_at_from",
                toKey: "created_at_to",
            },
        },
        {
            key: "updated_at",
            label: "Zaktualizowano",
            sortable: true,
            width: "180px",
            filter: {
                type: "date-range",
                fromKey: "updated_at_from",
                toKey: "updated_at_to",
            },
        },
    ];
}
