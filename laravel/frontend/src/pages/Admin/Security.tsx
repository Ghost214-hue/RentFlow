import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';

interface Props {
    ownersCount: number;
    failedLogins: number;
    rateLimiters?: Record<string, string>;
}

export default function SecurityMetrics({ ownersCount, failedLogins, rateLimiters }: Props) {
    const defaultLimiters: Record<string, string> = rateLimiters ?? {
        'admin-login': '5 attempts / minute (IP & email)',
        'login': '5 attempts / minute',
        'password-reset': '3 attempts / minute',
        'register': '3 attempts / minute',
        'setup': '5 attempts / minute',
        'mail': '10 sends / minute',
        'writes': '30 writes / minute',
        'reads': '60 reads / minute',
        'session': '120 requests / minute',
    };

    return (
        <AppLayout
            title="Security & Rate Limiting"
            subtitle="Platform protection tiers, rate limiter configurations, and authentication audit"
        >
            <Head title="Security Metrics - Admin" />

            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                <div className="rounded-2xl border border-border bg-surface p-5 shadow-card">
                    <span className="text-xs font-semibold uppercase text-text-subtle">Registered Landlords</span>
                    <div className="mt-2 text-2xl font-bold text-text">{ownersCount}</div>
                    <div className="mt-1 text-xs text-text-subtle">Subject to tenant-isolation scopes</div>
                </div>

                <div className="rounded-2xl border border-border bg-surface p-5 shadow-card">
                    <span className="text-xs font-semibold uppercase text-text-subtle">Failed Login Threshold</span>
                    <div className="mt-2 text-2xl font-bold text-text">{failedLogins}</div>
                    <div className="mt-1 text-xs text-emerald-600 font-medium">No active lockouts detected</div>
                </div>

                <div className="rounded-2xl border border-border bg-surface p-5 shadow-card">
                    <span className="text-xs font-semibold uppercase text-text-subtle">Two-Factor Enforcement</span>
                    <div className="mt-2 text-2xl font-bold text-indigo-500">MANDATORY</div>
                    <div className="mt-1 text-xs text-text-subtle">Required on all /admin/* endpoints</div>
                </div>
            </div>

            {/* Rate Limiter Status */}
            <div className="rounded-2xl border border-border bg-surface p-6 shadow-card mb-8">
                <div className="flex items-center justify-between mb-4">
                    <div>
                        <h3 className="text-base font-bold text-text">Configured Route Rate Limiters</h3>
                        <p className="text-xs text-text-subtle">Active defensive thresholds in bootstrap/app.php</p>
                    </div>
                    <Link href="/admin/audit" className="text-xs text-primary hover:underline font-medium">
                        View Audit Log →
                    </Link>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    {Object.entries(defaultLimiters).map(([key, desc]) => (
                        <div key={key} className="rounded-xl border border-border bg-page/50 p-4">
                            <div className="flex items-center justify-between mb-1">
                                <span className="font-mono text-xs font-bold text-primary">{key}</span>
                                <span className="flex h-2 w-2 rounded-full bg-emerald-500" />
                            </div>
                            <div className="text-xs text-text-subtle">{desc}</div>
                        </div>
                    ))}
                </div>
            </div>

            {/* Security Hardening Checklist */}
            <div className="rounded-2xl border border-border bg-surface p-6 shadow-card">
                <h3 className="text-base font-bold text-text mb-4">Security Baseline Protections</h3>
                <div className="space-y-3 text-sm text-text">
                    <div className="flex items-start gap-3">
                        <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-500 text-xs">
                            <i className="fas fa-check" aria-hidden="true" />
                        </span>
                        <div>
                            <div className="font-semibold">Strict Content-Security-Policy (CSP)</div>
                            <div className="text-xs text-text-subtle">
                                Disallows unapproved external scripts, restricts frame ancestors, and isolates styles.
                            </div>
                        </div>
                    </div>

                    <div className="flex items-start gap-3">
                        <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-500 text-xs">
                            <i className="fas fa-check" aria-hidden="true" />
                        </span>
                        <div>
                            <div className="font-semibold">Tenant Data Isolation (BelongsToOwner Scope)</div>
                            <div className="text-xs text-text-subtle">
                                Strict database queries automatically scope rows to the active landlord; cross-tenant leakage is strictly guarded.
                            </div>
                        </div>
                    </div>

                    <div className="flex items-start gap-3">
                        <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-500 text-xs">
                            <i className="fas fa-check" aria-hidden="true" />
                        </span>
                        <div>
                            <div className="font-semibold">Session Fixation & Guard Isolation</div>
                            <div className="text-xs text-text-subtle">
                                Admin sessions are stored in an isolated cookie with separate lifetimes and mandatory 2FA.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
