export interface SalesTotals {
    revenue: number;
    units: number;
    transactions: number;
    avg: number;
}

export interface StockTotals {
    units: number;
    value: number;
    titles: number;
    lowStock: number;
    outOfStock: number;
}

export interface TrendPoint {
    date: string;
    label: string;
    revenue: number;
    units: number;
}

export interface MonthPoint {
    month: string;
    label: string;
    revenue: number;
    units: number;
}

export interface FormatSlice {
    format: string;
    label: string;
    color: string;
    units: number;
    revenue: number;
}

export interface ArtistSales {
    artist: string;
    units: number;
    revenue: number;
    titles: number;
}

export interface ArtistStock {
    artist: string;
    titles: number;
    units: number;
    value: number;
}

export interface RecentSale {
    id: number;
    name: string | null;
    artist: string | null;
    quantity: number;
    sale_price: number;
    total: number;
    created_at: string | null;
}

export interface Kpi<T = number> {
    value: T;
    delta: number | null;
}

export interface ConditionSlice {
    condition: string;
    label: string;
    color: string;
    titles: number;
    units: number;
}

export interface GenreRow {
    genre: string;
    titles: number;
    units: number;
}

export interface FormatReportRow {
    format: string;
    label: string;
    color: string;
    titles: number;
    stockUnits: number;
    stockValue: number;
    soldUnits: number;
    revenue: number;
}
