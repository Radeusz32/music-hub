import type { ColumnDef, FilterOption } from "@/types/datatable";

export type { ColumnDef, FilterOption };

/* ── Types ── */

export interface UserRow {
    id: number;
    first_name: string;
    last_name: string;
    name: string;
    email: string;
    phone: string | null;
    street: string | null;
    building_number: string | null;
    apartment_number: string | null;
    postal_code: string | null;
    city: string | null;
    pesel: string | null;
    is_active: boolean;
    role: string | null;
    roles: string[];
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface UserFormData {
    first_name: string;
    last_name: string;
    email: string;
    phone: string;
    street: string;
    building_number: string;
    apartment_number: string;
    postal_code: string;
    city: string;
    pesel: string;
    is_active: boolean;
    password: string;
    role: string;
}

export const defaultUserForm: UserFormData = {
    first_name: "",
    last_name: "",
    email: "",
    phone: "",
    street: "",
    building_number: "",
    apartment_number: "",
    postal_code: "",
    city: "",
    pesel: "",
    is_active: true,
    password: "",
    role: "user",
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
    roleOptions: FilterOption[];
}

export function buildUserColumns(options: BuildColumnsOptions): ColumnDef[] {
    return [
        { key: "name", label: "Użytkownik", sortable: false },
        { key: "email", label: "E-mail", sortable: true },
        { key: "phone", label: "Telefon", width: "150px" },
        {
            key: "role",
            label: "Rola",
            width: "150px",
            filter: { type: "select", options: options.roleOptions },
        },
        {
            key: "email_verified_at",
            label: "Weryfikacja",
            width: "160px",
            filter: {
                type: "boolean",
                trueLabel: "Zweryfikowany",
                falseLabel: "Niezweryfikowany",
            },
        },
        {
            key: "is_active",
            label: "Status",
            sortable: true,
            width: "150px",
            filter: {
                type: "boolean",
                trueLabel: "Aktywny",
                falseLabel: "Nieaktywny",
            },
        },
        {
            key: "created_at",
            label: "Dołączył",
            sortable: true,
            width: "180px",
            filter: {
                type: "date-range",
                fromKey: "created_at_from",
                toKey: "created_at_to",
            },
        },
    ];
}
