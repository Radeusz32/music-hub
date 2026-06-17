import type { ColumnDef, FilterOption } from "@/types/datatable";

export type { ColumnDef, FilterOption };

/* ── Types ── */

export interface TenantFeature {
    value: string;
    label: string;
}

export interface Tenant {
    id: string;
    company_name: string | null;
    is_active: boolean;
    tax_id: string | null;
    regon: string | null;
    krs_number: string | null;
    company_email: string | null;
    company_phone: string | null;
    website: string | null;
    street: string | null;
    building_number: string | null;
    apartment_number: string | null;
    postal_code: string | null;
    city: string | null;
    country: string | null;
    domains: string[];
    features: TenantFeature[];
    created_at: string;
    updated_at: string;
}

export interface TenantFormData {
    company_name: string;
    tax_id: string;
    regon: string;
    krs_number: string;
    company_email: string;
    company_phone: string;
    website: string;
    street: string;
    building_number: string;
    apartment_number: string;
    postal_code: string;
    city: string;
    country: string;
}

export const defaultTenantForm: TenantFormData = {
    company_name: "",
    tax_id: "",
    regon: "",
    krs_number: "",
    company_email: "",
    company_phone: "",
    website: "",
    street: "",
    building_number: "",
    apartment_number: "",
    postal_code: "",
    city: "",
    country: "",
};

export function formFromTenant(tenant: Tenant): TenantFormData {
    return {
        company_name: tenant.company_name ?? "",
        tax_id: tenant.tax_id ?? "",
        regon: tenant.regon ?? "",
        krs_number: tenant.krs_number ?? "",
        company_email: tenant.company_email ?? "",
        company_phone: tenant.company_phone ?? "",
        website: tenant.website ?? "",
        street: tenant.street ?? "",
        building_number: tenant.building_number ?? "",
        apartment_number: tenant.apartment_number ?? "",
        postal_code: tenant.postal_code ?? "",
        city: tenant.city ?? "",
        country: tenant.country ?? "",
    };
}

/* ── Column definitions ── */

export function buildTenantColumns(): ColumnDef[] {
    return [
        { key: "company_name", label: "Nazwa firmy", sortable: true },
        {
            key: "is_active",
            label: "Status",
            width: "140px",
            filter: {
                type: "boolean",
                trueLabel: "Aktywne",
                falseLabel: "Nieaktywne",
            },
        },
        { key: "tax_id", label: "NIP", width: "150px" },
        { key: "city", label: "Miasto", sortable: true, width: "160px" },
        { key: "country", label: "Kraj", sortable: true, width: "140px" },
        { key: "domains", label: "Domeny", width: "220px" },
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
    ];
}
