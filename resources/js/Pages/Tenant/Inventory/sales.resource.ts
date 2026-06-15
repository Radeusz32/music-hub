/* ── Types ── */

export interface SellableRecord {
    id: number;
    name: string;
    artist: string;
    format: string;
    condition: string;
    quantity: number;
    cover_image: string | null;
}

export interface SaleUser {
    id: number;
    name: string;
}

export interface RecentSale {
    id: number;
    quantity: number;
    quantity_after: number;
    sale_price: string | null;
    sale_total: string | null;
    note: string | null;
    inventoryRecord: { id: number; name: string; artist: string } | null;
    user: SaleUser | null;
    created_at: string;
}

export interface SaleTodayStats {
    transactions: number;
    units: number;
}

export interface SaleFormData {
    inventory_record_id: number | string;
    quantity: number;
    sale_price: number | string;
    note: string;
}

export const defaultSaleForm: SaleFormData = {
    inventory_record_id: "",
    quantity: 1,
    sale_price: "",
    note: "",
};

/* ── Display helpers ── */

export function filterRecords(
    records: SellableRecord[],
    query: string,
): SellableRecord[] {
    const term = query.trim().toLowerCase();

    if (term === "") {
        return records;
    }

    return records.filter((record) =>
        `${record.artist} ${record.name} ${record.format}`
            .toLowerCase()
            .includes(term),
    );
}
