import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';
import { formatMoney } from '@/lib/money';

interface Props {
    ownerCount: number;
    activeOwnerCount: number;
    suspendedOwnerCount: number;
    newSignupCount: number;
    propertyCount: number;
    unitCount: number;
    occupiedUnits: number;
    occupancyRate: number;
    activeRenterCount: number;
    billedTotal: string;
    collectedTotal: string;
    outstandingTotal: string;
    collectionRate: number;
    emailFailuresLast24h: number;
    emailSuccessLast24h: number;
}

export default function AdminDashboard(props: Props) {
    const ratePct = (props.collectionRate * 100).toFixed(1);
    const occupancyPct = (props.occupancyRate * 100).toFixed(1);

    return (
        <AppLayout
            title="Platform Overview"
            subtitle="Real-time multi-tenant health, financials, and system operations"
        >
            <Head title="Admin Dashboard" />

            {/* Financial Performance */}
            <div className="mb-8">
                <div className="flex items-center justify-between mb-4">
                    <h2 className="text-sm font-semibold uppercase tracking-wider text-text-subtle">
                        Financial Aggregates (Current Month)
                    </h2>
                    <span className="text-xs text-text-subtle">Cross-tenant live data</span>
                </div>
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div className="rounded-2xl border border-border bg-surface p-5 shadow-card">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold uppercase text-text-subtle">Billed This Month</span>
                            <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500/10 text-blue-500">
                                <i className="fas fa-receipt text-sm" aria-hidden="true" />
                            </span>
                        </div>
                        <div className="mt-3 text-2xl font-bold text-text">{formatMoney(props.billedTotal)}</div>
                        <div className="mt-1 text-xs text-text-subtle">Total charges across all properties</div>
                    </div>

                    <div className="rounded-2xl border border-border bg-surface p-5 shadow-card">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold uppercase text-text-subtle">Collected</span>
                            <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-500">
                                <i className="fas fa-check-circle text-sm" aria-hidden="true" />
                            </span>
                        </div>
                        <div className="mt-3 text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                            {formatMoney(props.collectedTotal)}
                        </div>
                        <div className="mt-1 text-xs text-emerald-600/80">Reconciled payments</div>
                    </div>

                    <div className="rounded-2xl border border-border bg-surface p-5 shadow-card">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold uppercase text-text-subtle">Outstanding Balance</span>
                            <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-500/10 text-amber-500">
                                <i className="fas fa-clock text-sm" aria-hidden="true" />
                            </span>
                        </div>
                        <div className="mt-3 text-2xl font-bold text-amber-600 dark:text-amber-400">
                            {formatMoney(props.outstandingTotal)}
                        </div>
                        <div className="mt-1 text-xs text-text-subtle">Pending or overdue rent</div>
                    </div>

                    <div className="rounded-2xl border border-border bg-surface p-5 shadow-card">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold uppercase text-text-subtle">Collection Rate</span>
                            <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-500/10 text-purple-500">
                                <i className="fas fa-percent text-sm" aria-hidden="true" />
                            </span>
                        </div>
                        <div className="mt-3 text-2xl font-bold text-purple-600 dark:text-purple-400">{ratePct}%</div>
                        <div className="mt-2 w-full bg-slate-200 dark:bg-slate-700 h-1.5 rounded-full overflow-hidden">
                            <div
                                className="bg-purple-500 h-full rounded-full transition-all duration-500"
                                style={{ width: `${Math.min(100, Math.max(0, props.collectionRate * 100))}%` }}
                            />
                        </div>
                    </div>
                </div>
            </div>

            {/* Landlord & Tenant Ecosystem */}
            <div className="mb-8">
                <div className="flex items-center justify-between mb-4">
                    <h2 className="text-sm font-semibold uppercase tracking-wider text-text-subtle">
                        Platform Tenants & Occupancy
                    </h2>
                    <Link href="/admin/owners" className="text-xs text-primary hover:underline font-medium">
                        Manage all landlords →
                    </Link>
                </div>
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div className="rounded-2xl border border-border bg-surface p-5 shadow-card">
                        <span className="text-xs font-semibold uppercase text-text-subtle">Landlords / Owners</span>
                        <div className="mt-2 text-2xl font-bold text-text">{props.ownerCount}</div>
                        <div className="mt-2 flex items-center gap-2 text-xs">
                            <span className="text-emerald-600 font-semibold">{props.activeOwnerCount} active</span>
                            <span className="text-slate-400">•</span>
                            <span className="text-amber-600 font-semibold">{props.suspendedOwnerCount} suspended</span>
                        </div>
                    </div>

                    <div className="rounded-2xl border border-border bg-surface p-5 shadow-card">
                        <span className="text-xs font-semibold uppercase text-text-subtle">Managed Properties</span>
                        <div className="mt-2 text-2xl font-bold text-text">{props.propertyCount}</div>
                        <div className="mt-2 text-xs text-text-subtle">Across all client portfolios</div>
                    </div>

                    <div className="rounded-2xl border border-border bg-surface p-5 shadow-card">
                        <span className="text-xs font-semibold uppercase text-text-subtle">Total Units</span>
                        <div className="mt-2 text-2xl font-bold text-text">{props.unitCount}</div>
                        <div className="mt-2 flex items-center gap-2 text-xs text-text-subtle">
                            <span className="text-emerald-600 font-semibold">{props.occupiedUnits} occupied</span>
                            <span>({occupancyPct}% occupancy)</span>
                        </div>
                    </div>

                    <div className="rounded-2xl border border-border bg-surface p-5 shadow-card">
                        <span className="text-xs font-semibold uppercase text-text-subtle">Active Renters</span>
                        <div className="mt-2 text-2xl font-bold text-text">{props.activeRenterCount}</div>
                        <div className="mt-2 text-xs text-text-subtle">Currently active leases</div>
                    </div>
                </div>
            </div>

            {/* System Health & Operations Shortcuts */}
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {/* Email Health Box */}
                <div className="rounded-2xl border border-border bg-surface p-6 shadow-card">
                    <div className="flex items-center justify-between mb-4">
                        <h3 className="text-base font-bold text-text flex items-center gap-2">
                            <i className="fas fa-envelope text-primary text-sm" aria-hidden="true" />
                            Email Delivery Health
                        </h3>
                        <Link href="/admin/email-health" className="text-xs text-primary hover:underline">
                            View Log
                        </Link>
                    </div>
                    <p className="text-xs text-text-subtle mb-4">
                        Status of automated receipts, rent reminders, and notices sent in the last 24 hours.
                    </p>
                    <div className="grid grid-cols-2 gap-3">
                        <div className="rounded-xl bg-emerald-500/10 border border-emerald-500/20 p-3 text-center">
                            <div className="text-xl font-bold text-emerald-600 dark:text-emerald-400">
                                {props.emailSuccessLast24h}
                            </div>
                            <div className="text-xs text-emerald-700 dark:text-emerald-300 font-medium mt-0.5">
                                Sent Successfully
                            </div>
                        </div>
                        <div className="rounded-xl bg-red-500/10 border border-red-500/20 p-3 text-center">
                            <div className="text-xl font-bold text-red-600 dark:text-red-400">
                                {props.emailFailuresLast24h}
                            </div>
                            <div className="text-xs text-red-700 dark:text-red-300 font-medium mt-0.5">
                                Delivery Failures
                            </div>
                        </div>
                    </div>
                </div>

                {/* Quick Navigation Cards */}
                <div className="lg:col-span-2 rounded-2xl border border-border bg-surface p-6 shadow-card">
                    <h3 className="text-base font-bold text-text mb-4 flex items-center gap-2">
                        <i className="fas fa-screwdriver-wrench text-primary text-sm" aria-hidden="true" />
                        Administrative Capabilities
                    </h3>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <Link
                            href="/admin/owners"
                            className="flex items-center gap-3 p-3.5 rounded-xl border border-border hover:border-primary/40 hover:bg-page transition-all group"
                        >
                            <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-500/10 text-blue-500 group-hover:scale-105 transition-transform">
                                <i className="fas fa-user-tie" aria-hidden="true" />
                            </span>
                            <div>
                                <div className="text-sm font-semibold text-text group-hover:text-primary transition-colors">
                                    Landlord Management
                                </div>
                                <div className="text-xs text-text-subtle">Activate, suspend or reset owner credentials</div>
                            </div>
                        </Link>

                        <Link
                            href="/admin/support/access"
                            className="flex items-center gap-3 p-3.5 rounded-xl border border-border hover:border-primary/40 hover:bg-page transition-all group"
                        >
                            <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-500/10 text-indigo-500 group-hover:scale-105 transition-transform">
                                <i className="fas fa-key" aria-hidden="true" />
                            </span>
                            <div>
                                <div className="text-sm font-semibold text-text group-hover:text-primary transition-colors">
                                    Support Access Grants
                                </div>
                                <div className="text-xs text-text-subtle">Temporary scoped access to landlord accounts</div>
                            </div>
                        </Link>

                        <Link
                            href="/admin/security"
                            className="flex items-center gap-3 p-3.5 rounded-xl border border-border hover:border-primary/40 hover:bg-page transition-all group"
                        >
                            <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-red-500/10 text-red-500 group-hover:scale-105 transition-transform">
                                <i className="fas fa-shield-halved" aria-hidden="true" />
                            </span>
                            <div>
                                <div className="text-sm font-semibold text-text group-hover:text-primary transition-colors">
                                    Security & Throttle Audit
                                </div>
                                <div className="text-xs text-text-subtle">Review rate limits and failed attempt logs</div>
                            </div>
                        </Link>

                        <Link
                            href="/admin/system"
                            className="flex items-center gap-3 p-3.5 rounded-xl border border-border hover:border-primary/40 hover:bg-page transition-all group"
                        >
                            <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-500/10 text-slate-500 group-hover:scale-105 transition-transform">
                                <i className="fas fa-server" aria-hidden="true" />
                            </span>
                            <div>
                                <div className="text-sm font-semibold text-text group-hover:text-primary transition-colors">
                                    System Status & Queue
                                </div>
                                <div className="text-xs text-text-subtle">Application version, database and session status</div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
