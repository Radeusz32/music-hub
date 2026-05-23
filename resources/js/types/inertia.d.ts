import type { PageProps as InertiaPageProps } from "@inertiajs/core";

export interface AuthUser {
    id: number;
    first_name: string;
    last_name: string;
    name: string;
    email: string;
}

export interface Auth {
    user: AuthUser;
    permissions: string[];
    roles: string[];
    features: string[];
}

declare module "@inertiajs/core" {
    interface PageProps extends InertiaPageProps {
        auth: Auth | null;
        tenant: { company_name: string | null } | null;
    }
}
