export interface FilterOption {
    value: string;
    label: string;
    color?: string;
}

export interface SelectColumnFilter {
    type: "select";
    options: FilterOption[];
}

export interface DateRangeColumnFilter {
    type: "date-range";
    fromKey: string;
    toKey: string;
}

export interface NumberColumnFilter {
    type: "number";
    /** Render the input as a currency amount (zł, 2 decimals). */
    currency?: boolean;
    min?: number;
    max?: number;
    placeholder?: string;
}

export interface NumberRangeColumnFilter {
    type: "number-range";
    fromKey: string;
    toKey: string;
    /** Render the inputs as currency amounts (zł, 2 decimals). */
    currency?: boolean;
    min?: number;
    max?: number;
}

export interface BooleanColumnFilter {
    type: "boolean";
    /** Tooltip for the "true" (✓) toggle. */
    trueLabel?: string;
    /** Tooltip for the "false" (✗) toggle. */
    falseLabel?: string;
}

export type ColumnFilter =
    | SelectColumnFilter
    | DateRangeColumnFilter
    | NumberColumnFilter
    | NumberRangeColumnFilter
    | BooleanColumnFilter;

export interface ColumnDef {
    key: string;
    label: string;
    sortable?: boolean;
    width?: string;
    align?: "left" | "right" | "center";
    filter?: ColumnFilter;
}

export interface Pagination<T = Record<string, unknown>> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    filters: Record<string, string | number | undefined>;
}
