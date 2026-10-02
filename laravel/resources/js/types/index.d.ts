/**
 * Inertia shared prop types.
 *
 * These mirror the backend contract. Pages add their own props on top; see
 * resources/js/types/generated.d.ts for types produced from PHP enums and
 * DTOs by spatie/laravel-typescript-transformer.
 */

/** The three actor kinds. NOTE: "tenant" in the API means a RENTER. */
export type Role = 'owner' | 'caretaker' | 'tenant';

export interface AuthUser {
    id: number;
    name: string;
    email: string;
    role: Role;
    roleLabel: string;
}

export interface Auth {
    user: AuthUser | null;
    ownerId: number | null;
}

/** Laravel's paginator shape as Inertia serialises it. */
export interface Paginated<T> {
    data: T[];
    links: {
        first: string | null;
        last: string | null;
        prev: string | null;
        next: string | null;
    };
    meta: {
        current_page: number;
        from: number | null;
        last_page: number;
        per_page: number;
        to: number | null;
        total: number;
    };
}

/** A house as returned by HouseResource. Money fields are strings. */
export interface House {
    id: number;
    unit: string;
    type: string;
    /** Decimal(12,2) as a string — never a number. */
    rent: string;
    status: string;
    property_id: number;
    property_name: string | null;
    tenant_id: number | null;
    tenant_name: string | null;
    water_meter: string | null;
    elec_meter: string | null;
    created_at: string | null;
    updated_at: string | null;
}

export interface PropertyOption {
    id: number;
    name: string;
}

/** A renter as returned by RenterResource. All money fields are strings. */
export interface Renter {
    id: number;
    name: string;
    email: string | null;
    phone: string;

    id_type: string;
    id_number: string | null;

    status: string;
    profile_picture: string | null;

    property_id: number | null;
    property_name: string | null;

    house_id: number | null;
    house_unit: string | null;

    /** Snapshot at onboarding. Future bills use houses.rent. */
    rent: string;
    deposit: string;
    balance: string;
    credit: string;

    lease_start: string | null;
    lease_end: string | null;

    next_of_kin_name: string | null;
    next_of_kin_phone: string | null;
    next_of_kin_email: string | null;

    created_at: string | null;
    updated_at: string | null;
}

/** A vacant unit offered in the onboarding form. */
export interface VacantHouse {
    id: number;
    property_id: number;
    unit: string;
    /** Money string — used to prefill the rent field, never recomputed. */
    rent: string;
}

export interface SharedProps {
    auth: Auth;
    flash: {
        success: string | null;
        error: string | null;
    };
}

declare global {
    interface PageProps {
        errors: Record<string, string>;
    }
}