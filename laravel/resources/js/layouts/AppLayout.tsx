/**
 * App shell: sidebar + header.
 *
 * Ported from frontend/public/components/sidebar.php and header.php. The
 * gradient, spacing, colours and wording are preserved so the app looks the
 * same; the nav is driven by the authenticated role from the backend rather
 * than a PHP `match` in a template.
 */
import type { ReactNode } from 'react';
import { Shell } from './Shell';

interface NavItem {
    href: string;
    label: string;
    icon: string;
}

/**
 * Nav is per-role. "tenant" here means a RENTER, not an owner.
 * Entries whose module is not ported yet still appear so the navigation
 * matches the legacy app; they become live as each module lands.
 */
export const NAV: Record<string, NavItem[]> = {
    owner: [
        { href: '/', label: 'Dashboard', icon: 'fa-chart-line' },
        { href: '/properties', label: 'Properties', icon: 'fa-building' },
        { href: '/houses', label: 'Houses', icon: 'fa-home' },
        { href: '/tenants', label: 'Renters', icon: 'fa-users' },
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
        { href: '/documents', label: 'Rules', icon: 'fa-file-alt' },
        { href: '/reports', label: 'Reports', icon: 'fa-chart-pie' },
        {
            href: '/email-logs',
            label: 'Email Delivery',
            icon: 'fa-envelope-open-text',
        },
    ],
    caretaker: [
        { href: '/', label: 'Dashboard', icon: 'fa-chart-line' },
        { href: '/properties', label: 'Properties', icon: 'fa-building' },
        { href: '/houses', label: 'Houses', icon: 'fa-home' },
        { href: '/tenants', label: 'Renters', icon: 'fa-users' },
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
        { href: '/documents', label: 'Rules', icon: 'fa-file-alt' },
        {
            href: '/email-logs',
            label: 'Email Delivery',
            icon: 'fa-envelope-open-text',
        },
    ],
    tenant: [
        { href: '/', label: 'Dashboard', icon: 'fa-chart-line' },
        { href: '/profile', label: 'My Profile', icon: 'fa-user' },
        { href: '/payments', label: 'My Payments', icon: 'fa-money-bill-wave' },
        { href: '/bills', label: 'My Bills', icon: 'fa-file-invoice-dollar' },
        {
            href: '/complaints',
            label: 'My Complaints',
            icon: 'fa-exclamation-triangle',
        },
        { href: '/documents', label: 'Rules', icon: 'fa-file-alt' },
    ],
};

const PAGE_TITLES: Record<string, string> = {
    '/': 'Dashboard',
    '/houses': 'Houses & Units',
    '/properties': 'Properties',
    '/tenants': 'Renters',
    '/payments': 'Payments',
    '/bills': 'Bills',
};

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