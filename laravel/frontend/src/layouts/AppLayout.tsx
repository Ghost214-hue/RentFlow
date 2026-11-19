/**
 * App shell: sidebar + header.
 *
 * Ported from frontend/public/components/sidebar.php and header.php. The
 * gradient, spacing, colours and wording are preserved so the app looks the
 * same; the nav is driven by the authenticated role from the backend rather
 * than a PHP `match` in a template.
 */
import type { ReactNode } from 'react';
import { usePage } from '@inertiajs/react';
import { Shell } from './Shell';

interface NavItem {
    href: string;
    label: string;
    icon: string;

    /**
     * False while the module has not been ported from the legacy app.
     *
     * Such an entry is rendered disabled rather than as a link. Showing a live
     * link to a route that does not exist produces a 404 that reads as a
     * broken application, which is worse than admitting the gap.
     */
    ready?: boolean;

    /**
     * Ability required to see this entry, as resolved by PermissionService and
     * shared from HandleInertiaRequests. When the shared `abilities` list is
     * absent the entry is shown, so the nav keeps working before the backend
     * starts sharing abilities.
     */
    ability?: string;
}

/**
 * Nav is per-role. "tenant" here means a RENTER, not an owner.
 *
 * Entries marked `ready: false` belong to modules still being migrated. They
 * stay listed so the navigation still reflects the intended shape of the
 * product, but are not clickable until their route exists.
 */
export const NAV: Record<string, NavItem[]> = {
    owner: [
        { href: '/', label: 'Dashboard', icon: 'fa-chart-line' },
        {
            href: '/properties',
            label: 'Properties',
            icon: 'fa-building',
        },
        { href: '/houses', label: 'Houses', icon: 'fa-home' },
        { href: '/renters', label: 'Renters', icon: 'fa-users' },
        {
            href: '/renters/former',
            label: 'Former Renters',
            icon: 'fa-user-slash',
        },
        { href: '/caretakers', label: 'Caretakers', icon: 'fa-user-shield' },
        { href: '/payments', label: 'Payments', icon: 'fa-money-bill-wave' },
        { href: '/bills', label: 'Bills', icon: 'fa-file-invoice-dollar' },
        {
            href: '/maintenance',
            label: 'Maintenance',
            icon: 'fa-tools',
        },
        {
            href: '/complaints',
            label: 'Complaints',
            icon: 'fa-exclamation-triangle',
        },
        {
            href: '/documents',
            label: 'Rules',
            icon: 'fa-file-alt',
        },
        {
            href: '/reports',
            label: 'Reports',
            icon: 'fa-chart-pie',
        },
        {
            href: '/terminations',
            label: 'Terminations',
            icon: 'fa-door-open',
        },
        {
            href: '/email-logs',
            label: 'Email Delivery',
            icon: 'fa-envelope-open-text',
        },
    ],
    caretaker: [
        { href: '/caretaker/dashboard', label: 'Dashboard', icon: 'fa-chart-line' },
        {
            href: '/properties',
            label: 'Properties',
            icon: 'fa-building',
        },
        { href: '/houses', label: 'Houses', icon: 'fa-home' },
        { href: '/renters', label: 'Renters', icon: 'fa-users' },
        {
            href: '/renters/former',
            label: 'Former Renters',
            icon: 'fa-user-slash',
        },
        { href: '/payments', label: 'Payments', icon: 'fa-money-bill-wave' },
        { href: '/bills', label: 'Bills', icon: 'fa-file-invoice-dollar' },
        {
            href: '/maintenance',
            label: 'Maintenance',
            icon: 'fa-tools',
        },
        {
            href: '/complaints',
            label: 'Complaints',
            icon: 'fa-exclamation-triangle',
        },
        {
            href: '/documents',
            label: 'Rules',
            icon: 'fa-file-alt',
        },
        // Email Delivery is deliberately absent: the controller scopes the log
        // to the owner, not to the caretaker's assigned properties, so listing
        // it here exposed the whole portfolio's mail. Re-add once the log is
        // scoped to assigned properties.
    ],
    tenant: [
        // A renter's pages live under /renter. Pointing at '/' or '/profile'
        // sent them to an owner dashboard or a 404.
        {
            href: '/renter/dashboard',
            label: 'Dashboard',
            icon: 'fa-chart-line',
        },
        {
            href: '/renter/profile',
            label: 'My Profile',
            icon: 'fa-user',
        },
        { href: '/payments', label: 'My Payments', icon: 'fa-money-bill-wave' },
        { href: '/bills', label: 'My Bills', icon: 'fa-file-invoice-dollar' },
        {
            href: '/maintenance',
            label: 'My Requests',
            icon: 'fa-tools',
        },
        {
            href: '/complaints',
            label: 'My Complaints',
            icon: 'fa-exclamation-triangle',
        },
        {
            href: '/documents',
            label: 'Rules',
            icon: 'fa-file-alt',
        },
    ],
    admin: [
        { href: '/admin', label: 'Platform Overview', icon: 'fa-chart-pie' },
        { href: '/admin/owners', label: 'Owners', icon: 'fa-user-tie' },
        { href: '/admin/support/access', label: 'Support Access', icon: 'fa-key' },
        { href: '/admin/email-health', label: 'Email Health', icon: 'fa-envelope-circle-check' },
        { href: '/admin/security', label: 'Security', icon: 'fa-shield-halved' },
        { href: '/admin/system', label: 'System', icon: 'fa-server' },
        { href: '/admin/audit', label: 'Audit Logs', icon: 'fa-clipboard-list' },
    ],
};

const PAGE_TITLES: Record<string, string> = {
    '/': 'Dashboard',
    '/admin': 'Platform Overview',
    '/admin/owners': 'Owner Management',
    '/admin/support/access': 'Support Access Grants',
    '/admin/email-health': 'Email & Queue Health',
    '/admin/security': 'Security Metrics',
    '/admin/system': 'System Status',
    '/admin/audit': 'Platform Audit Logs',
    '/renter/dashboard': 'My Dashboard',
    '/renter/profile': 'My Profile',
    '/houses': 'Houses & Units',
    '/properties': 'Properties',
    '/renters': 'Renters',
    '/renters/former': 'Former Renters',
    '/payments': 'Payments',
    '/bills': 'Bills',
    '/caretakers': 'Caretakers',
    '/maintenance': 'Maintenance',
    '/complaints': 'Complaints',
    '/documents': 'Rules',
    '/reports': 'Reports',
    '/terminations': 'Terminations',
    '/email-logs': 'Email Delivery',
};

/**
 * Filter a role's nav down to what the actor is actually allowed to use.
 *
 * When `abilities` is null/undefined the full role nav is returned. That is
 * deliberate: the backend does not share abilities yet, and failing closed
 * here would blank the sidebar for every user until it does.
 */
export function visibleNav(
    role: string | null | undefined,
    abilities: string[] | null | undefined,
): NavItem[] {
    const items = (role && NAV[role]) || [];

    if (!abilities) {
        return items;
    }

    return items.filter(
        (item) => !item.ability || abilities.includes(item.ability),
    );
}

/** Convenience hook for components that render their own nav. */
export function useVisibleNav(): NavItem[] {
    const page = usePage();
    const auth = page.props.auth as
        | { user: { role: string } | null }
        | undefined;
    const abilities = page.props.abilities as string[] | null | undefined;

    return visibleNav(auth?.user?.role ?? null, abilities);
}

interface LayoutProps {
    children: ReactNode;
    title?: string;
    subtitle?: string;
    actions?: ReactNode;
}

export default function AppLayout({
    children,
    title,
    subtitle,
    actions,
}: LayoutProps) {
    return (
        <Shell title={title} subtitle={subtitle}>
            {actions && (
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                    {actions}
                </div>
            )}
            {children}
        </Shell>
    );
}

export { PAGE_TITLES };
