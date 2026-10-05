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
    /** Decimal(12,2) as a string Ã¢â‚¬â€ never a number. */
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
    /** Money string Ã¢â‚¬â€ used to prefill the rent field, never recomputed. */
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
/** A bill as returned by BillResource. Money fields are strings. */
export interface Bill {
    id: number;
    month: string;
    due_date: string | null;

    /** Authoritative figures from BillSnapshot â€” never computed client-side. */
    amount: string;
    paid: string;
    balance: string;
    status: string;
    opening_balance: string;
    credit_applied: string;

    house_id: number;
    house_unit: string | null;
    property_name: string | null;

    tenant_id: number | null;
    tenant_name: string | null;

    items?: Array<{
        id: number;
        type: string;
        description: string | null;
        amount: string;
        paid: string;
        status: string;
    }>;
}

/** A payment as returned by PaymentResource. */
export interface Payment {
    id: number;
    receipt: string | null;
    /** What the payer handed over. */
    amount: string;
    /** What actually reached bill items; the rest is renter credit. */
    allocated: string;
    status: string;
    type: string;
    method: string | null;
    month: string | null;
    date: string | null;
    description: string | null;

    tenant_id: number | null;
    tenant_name: string | null;
    house_unit: string | null;

    tenant_confirmed: boolean;
    created_at: string | null;
}

export interface RenterOption {
    id: number;
    name: string;
}
/** A caretaker as returned by CaretakerResource. */
export interface Caretaker {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    id_number: string | null;
    avatar: string | null;
    /** Parsed from the CSV column into a real list. */
    assigned_properties: number[];
    assigned_count: number;
    created_at: string | null;
}

/** A complaint as returned by ComplaintResource. */
export interface Complaint {
    id: number;
    title: string;
    description: string | null;
    category: string | null;
    priority: string;
    status: string;
    date: string | null;
    sender_role: string | null;
    recipient_type: string | null;
    recipient_ids: number[];
    property_id: number | null;
    property_name: string | null;
    house_id: number | null;
    house_unit: string | null;
    tenant_id: number | null;
    tenant_name: string | null;
    timeline: Array<Record<string, unknown>>;
    comments: string | null;
    created_at: string | null;
    updated_at: string | null;
}
/** A maintenance request as returned by MaintenanceRecordResource. */
export interface MaintenanceRecord {
    id: number;
    title: string;
    description: string | null;
    category: string | null;
    priority: string;
    status: string;
    assigned_to: string | null;
    /** Decimal string, never a number. */
    cost: string | null;
    cost_notes: string | null;
    vendor_name: string | null;
    vendor_phone: string | null;
    scheduled_date: string | null;
    completed_date: string | null;
    notes: string | null;
    property_id: number | null;
    property_name: string | null;
    house_id: number | null;
    house_unit: string | null;
    tenant_id: number | null;
    tenant_name: string | null;
    recipient_ids: number[];
    created_at: string | null;
    updated_at: string | null;
}

/** Props for the renter's own dashboard. */
export interface RenterDashboardProps {
    renter: {
        id: number;
        name: string;
        email: string;
        phone: string | null;
        status: string;
        property_name: string | null;
        house_unit: string | null;
        lease_start: string | null;
        lease_end: string | null;
    };
    summary: {
        /** All money values are decimal strings computed server-side. */
        outstanding: string;
        credit: string;
        unpaid_bill_count: number;
        open_complaints: number;
        open_maintenance: number;
    };
    /** Tenancy status, so the dashboard offers the right action. */
    tenancy: {
        status: string;
        can_request_termination: boolean;
        pending_request: boolean;
        effective_date: string | null;
    };
    recentBills: Bill[];
    recentPayments: Array<{
        id: number;
        amount: string;
        method: string | null;
        status: string;
        paid_at: string | null;
    }>;
}

/** Props for the renter's own profile. */
export interface RenterProfileProps {
    renter: {
        id: number;
        name: string;
        email: string;
        phone: string | null;
        id_number: string | null;
        id_type: string | null;
        next_of_kin_name: string | null;
        next_of_kin_phone: string | null;
        next_of_kin_email: string | null;
        profile_picture: string | null;
        /** Read-only tenancy, shown for context only. */
        status: string;
        property_name: string | null;
        house_unit: string | null;
        lease_start: string | null;
        lease_end: string | null;
        deposit: string;
        balance: string;
        credit: string;
    };
    flash?: { success?: string | null };
}
/** A property as returned by PropertyResource. */
export interface Property {
    id: number;
    name: string;
    address: string;
    type: string | null;
    image: string | null;
    /** Decimal string, never a number. */
    rent: string;
    /** Cached columns, shown for reference only. */
    units_recorded: number;
    occupied_recorded: number;
    /** Authoritative counts derived from the house rows. */
    units: number;
    occupied: number;
    payment_method_type: string | null;
    paybill_number: string | null;
    paybill_account: string | null;
    till_number: string | null;
    bank_name: string | null;
    bank_account: string | null;
    bank_branch: string | null;
    mobile_money_number: string | null;
    caretaker_id: number | null;
    caretaker_name: string | null;
    created_at: string | null;
    updated_at: string | null;
}

/** Props for the properties index. */
export interface PropertiesIndexProps {
    properties: Paginated<Property>;
    caretakers: RenterOption[];
    canManage: boolean;
    filters: { search: string | null; payment_method_type: string | null };
    flash?: { success?: string | null; error?: string | null };
}
/** A rules/regulations document. */
export interface PropertyDocument {
    id: number;
    title: string;
    content: string;
    type: string;
    version: string | null;
    is_active: boolean;
    property_id: number | null;
    property_name: string | null;
    published_at: string | null;
    updated_at: string | null;
}

/** One row of the email delivery log. */
export interface EmailLogEntry {
    id: number;
    to_email: string;
    to_name: string | null;
    subject: string;
    status: string;
    error: string | null;
    sent_at: string | null;
}

/** One share-of-total row: a payment method, income type or bill component. */
export interface ShareRow {
    label: string;
    /** Decimal string, never a number. */
    amount: string;
    count: number;
    /** Percentage of the total, as a decimal string. */
    share: string;
}

/**
 * Props for the consolidated reports page.
 *
 * Five legacy report screens (portfolio, bills, financial, tenancy/vacancy,
 * complaints) were merged into this one page. Every figure is computed
 * server-side from the bill and allocation ledger -- nothing is calculated in
 * the browser, so a report can never disagree with what a renter was charged.
 */
export interface ReportsProps {
    /** The month every time-scoped figure describes. */
    month: string;
    /** Months that actually have bills, for the filter. */
    months: string[];
    summary: {
        counts: {
            properties: number;
            houses: number;
            occupied: number;
            vacant: number;
            renters: number;
            active_renters: number;
            open_complaints: number;
            open_maintenance: number;
        };
        money: {
            /** All decimal strings. */
            billed_to_date: string;
            received_to_date: string;
            outstanding: string;
            collection_rate: string;
        };
    };
    monthly: Array<{
        month: string;
        billed: string;
        received: string;
        outstanding: string;
    }>;
    topDebtors: Array<{ id: number; name: string; outstanding: string }>;
    /** Legacy ReportController: per-property collection for `month`. */
    propertyPerformance: Array<{
        property_id: number;
        name: string;
        units: number;
        occupied: number;
        billed: string;
        collected: string;
        outstanding: string;
        collection_rate: string;
    }>;
    /** Legacy BillsReportController: status split and arrears. */
    billing: {
        counts: { total: number; paid: number; partial: number; pending: number; overdue: number };
        money: { billed: string; paid: string; partial: string; pending: string };
        arrears: {
            count: number;
            amount: string;
            oldest_month: string | null;
            aging: Array<{ label: string; count: number; amount: string; share: string }>;
        };
    };
    /** Rent against utilities for `month`. */
    composition: ShareRow[];
    /** Per-unit collection for `month`, worst first. */
    byHouse: Array<{
        house_id: number;
        unit: string;
        property: string | null;
        billed: string;
        collected: string;
        outstanding: string;
    }>;
    /** Legacy FinancialReportController: money in. */
    revenue: {
        collected: string;
        pending: string;
        failed: string;
        in_flight: string;
        counts: { settled: number; pending: number; failed: number };
        method_share: ShareRow[];
        type_breakdown: ShareRow[];
    };
    /** Legacy TenancyVacancyReportController. */
    occupancy: {
        units: {
            total: number;
            occupied: number;
            vacant: number;
            occupancy_rate: string;
            vacancy_rate: string;
        };
        money: { potential_monthly: string; collecting_monthly: string; lost_monthly: string };
        renters: { active: number; pending_termination: number; terminated: number };
        terminations: { pending: number; approved: number; by_actor: Record<string, number> };
    };
    occupancyByProperty: Array<{
        property_id: number;
        name: string;
        units: number;
        occupied: number;
        vacant: number;
        occupancy_rate: string;
        lost_monthly: string;
    }>;
    vacantUnits: Array<{
        house_id: number;
        unit: string;
        property: string | null;
        rent: string;
        vacant_since: string | null;
    }>;
    /** Legacy ComplaintsReportController. */
    complaints: {
        total: number;
        resolved: number;
        unresolved: number;
        resolution_rate: string;
        high_priority_open: number;
        by_status: Record<string, number>;
        by_priority: Record<string, number>;
        by_category: Record<string, number>;
    };
    /** New: the legacy app had no maintenance aggregate at all. */
    maintenance: {
        counts: { total: number; open: number; completed: number };
        money: { spent: string; committed: string; per_job: string };
        by_status: Record<string, number>;
        by_priority: Record<string, number>;
        by_category: Array<{ label: string; count: number; amount: string }>;
    };
}
