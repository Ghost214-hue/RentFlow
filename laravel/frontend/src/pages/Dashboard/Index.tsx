import { Head, Link } from '@inertiajs/react';
import { Card, CardBody, StatCard } from '@/components/ui';
import AppLayout from '@/layouts/AppLayout';
import { formatMoney } from '@/lib/money';

interface MonthlyPoint {
    label: string;
    billed: string;
    collected: string;
    billedHeight: string;
    collectedHeight: string;
}

interface Props {
    stats: {
        properties: number;
        units: number;
        occupiedUnits: number;
        /** Lifetime totals remain formatted strings; browser code never sums money. */
        expected: string;
        collected: string;
        expenses: string;
        monthly: {
            months: MonthlyPoint[];
            current: {
                label: string;
                billed: string;
                collected: string;
                outstanding: string;
                collectionRate: string;
            };
        };
    };
}

/** Owner/caretaker landing page. Ported from frontend/pages/dashboard.php. */
export default function Dashboard({ stats }: Props) {
    const occupancy = stats.units > 0 ? Math.round((stats.occupiedUnits / stats.units) * 100) : 0;
    const monthly = stats.monthly;

    const cards = [
        { label: 'Properties', value: String(stats.properties), icon: 'fa-building', tone: 'primary' as const, href: '/properties' },
        { label: 'Units', value: String(stats.units), icon: 'fa-home', tone: 'success' as const, href: '/houses' },
        { label: 'Occupancy', value: `${occupancy}%`, icon: 'fa-chart-pie', tone: 'warning' as const, href: '/reports?tab=occupancy' },
        { label: 'Collected this month', value: formatMoney(monthly.current.collected), icon: 'fa-money-bill-wave', tone: 'info' as const, href: '/reports?tab=revenue' },
        { label: 'Expenses', value: formatMoney(stats.expenses), icon: 'fa-wallet', tone: 'rose' as const, href: '/reports?tab=revenue' },
        { label: 'Outstanding', value: formatMoney(monthly.current.outstanding), icon: 'fa-hourglass-half', tone: 'rose' as const, href: '/reports?tab=billing' },
    ];

    return (
        <>
            <Head title="Dashboard" />
            <AppLayout
                title="Dashboard"
                subtitle="Your portfolio, at a glance"
                actions={
                    <Link
                        href="/reports"
                        className="inline-flex items-center gap-2 rounded-full border border-border bg-surface-raised px-4 py-2 text-sm font-medium text-text shadow-sm hover:border-primary/50 hover:text-primary transition-all"
                    >
                        <i className="fas fa-chart-line text-primary" aria-hidden="true" />
                        Detailed Reports
                    </Link>
                }
            >
                <section aria-label="Portfolio statistics" className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                    {cards.map((card) => (
                        <StatCard
                            key={card.label}
                            label={card.label}
                            value={card.value}
                            icon={card.icon}
                            tone={card.tone}
                            href={card.href}
                        />
                    ))}
                </section>

                <section className="mt-6" aria-labelledby="monthly-performance-heading">
                    <Card className="overflow-hidden">
                        <div className="flex flex-col gap-3 border-b border-border px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            <div>
                                <h2 id="monthly-performance-heading" className="text-lg font-bold text-text">
                                    Monthly performance
                                </h2>
                                <p className="mt-1 text-sm text-text-muted">
                                    Billed by bill month and collections by payment date
                                </p>
                            </div>
                            <div className="inline-flex items-center gap-4 text-xs font-semibold text-text-muted">
                                <span className="inline-flex items-center gap-2">
                                    <span className="h-2.5 w-2.5 rounded-sm bg-blue-500 shadow-sm" aria-hidden="true" />
                                    Billed
                                </span>
                                <span className="inline-flex items-center gap-2">
                                    <span className="h-2.5 w-2.5 rounded-sm bg-cyan-400 shadow-sm" aria-hidden="true" />
                                    Collected
                                </span>
                            </div>
                        </div>

                        <CardBody className="space-y-6">
                            <div
                                className="grid h-56 grid-cols-6 items-end gap-3 border-b border-border bg-[linear-gradient(to_bottom,transparent_24.7%,var(--color-border)_25%,transparent_25.3%,transparent_49.7%,var(--color-border)_50%,transparent_50.3%,transparent_74.7%,var(--color-border)_75%,transparent_75.3%)] px-1 sm:gap-6 sm:px-4"
                                role="img"
                                aria-label={`Six-month billed and collected chart, most recent month ${monthly.current.label}`}
                            >
                                {monthly.months.map((month) => (
                                    <div key={month.label} className="flex h-full min-w-0 flex-col justify-end">
                                        <div className="flex h-[calc(100%-1.75rem)] items-end justify-center gap-1.5 sm:gap-2">
                                            <div
                                                className="w-3 rounded-t-md bg-gradient-to-t from-blue-700 to-blue-400 shadow-[0_0_16px_rgba(59,130,246,0.2)] transition-[height] duration-500 sm:w-5"
                                                style={{ height: `${month.billedHeight}%` }}
                                                title={`Billed: ${formatMoney(month.billed)}`}
                                            />
                                            <div
                                                className="w-3 rounded-t-md bg-gradient-to-t from-cyan-600 to-cyan-300 shadow-[0_0_16px_rgba(34,211,238,0.2)] transition-[height] duration-500 sm:w-5"
                                                style={{ height: `${month.collectedHeight}%` }}
                                                title={`Collected: ${formatMoney(month.collected)}`}
                                            />
                                        </div>
                                        <span className="mt-2 truncate text-center text-xs font-semibold text-text-muted">
                                            {month.label}
                                        </span>
                                    </div>
                                ))}
                            </div>

                            <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                                <MonthlyMetric label="Billed this month" value={formatMoney(monthly.current.billed)} icon="fa-file-invoice-dollar" tone="primary" href="/reports?tab=billing" />
                                <MonthlyMetric label="Collected this month" value={formatMoney(monthly.current.collected)} icon="fa-circle-check" tone="success" href="/reports?tab=revenue" />
                                <MonthlyMetric label="Outstanding balance" value={formatMoney(monthly.current.outstanding)} icon="fa-hourglass-half" tone="warning" href="/reports?tab=billing" />
                                <MonthlyMetric label="Collection rate" value={`${monthly.current.collectionRate}%`} icon="fa-bullseye" tone="info" href="/reports?tab=overview" />
                            </div>
                        </CardBody>
                    </Card>
                </section>

                <section className="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3" aria-label="Portfolio financial totals">
                    <Link href="/reports?tab=billing" className="block transition-transform duration-200 hover:-translate-y-0.5">
                        <Card className="h-full hover:border-primary/40 transition-colors">
                            <CardBody>
                                <div className="flex items-center gap-3">
                                    <span className="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400">
                                        <i className="fas fa-layer-group text-lg" aria-hidden="true" />
                                    </span>
                                    <div>
                                        <p className="text-xs font-medium text-text-muted">All-time billed</p>
                                        <p className="mt-1 text-xl font-bold text-text">{formatMoney(stats.expected)}</p>
                                    </div>
                                </div>
                            </CardBody>
                        </Card>
                    </Link>

                    <Link href="/reports?tab=revenue" className="block transition-transform duration-200 hover:-translate-y-0.5">
                        <Card className="h-full hover:border-emerald-500/40 transition-colors">
                            <CardBody>
                                <div className="flex items-center gap-3">
                                    <span className="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                        <i className="fas fa-money-bill-trend-up text-lg" aria-hidden="true" />
                                    </span>
                                    <div>
                                        <p className="text-xs font-medium text-text-muted">All-time collected</p>
                                        <p className="mt-1 text-xl font-bold text-text">{formatMoney(stats.collected)}</p>
                                    </div>
                                </div>
                            </CardBody>
                        </Card>
                    </Link>

                    <Link href="/expenses" className="block transition-transform duration-200 hover:-translate-y-0.5">
                        <Card className="h-full hover:border-rose-500/40 transition-colors">
                            <CardBody>
                                <div className="flex items-center gap-3">
                                    <span className="flex h-11 w-11 items-center justify-center rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400">
                                        <i className="fas fa-wallet text-lg" aria-hidden="true" />
                                    </span>
                                    <div>
                                        <p className="text-xs font-medium text-text-muted">All-time expenses</p>
                                        <p className="mt-1 text-xl font-bold text-text">{formatMoney(stats.expenses)}</p>
                                    </div>
                                </div>
                            </CardBody>
                        </Card>
                    </Link>
                </section>
            </AppLayout>
        </>
    );
}

function MonthlyMetric({
    label,
    value,
    icon,
    tone = 'primary',
    href,
}: {
    label: string;
    value: string;
    icon: string;
    tone?: 'primary' | 'success' | 'warning' | 'info' | 'rose';
    href?: string;
}) {
    const toneClasses = {
        primary: 'bg-blue-500/10 text-blue-600 dark:text-blue-400',
        success: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
        warning: 'bg-amber-500/10 text-amber-600 dark:text-amber-400',
        info: 'bg-purple-500/10 text-purple-600 dark:text-purple-400',
        rose: 'bg-rose-500/10 text-rose-600 dark:text-rose-400',
    }[tone];

    const content = (
        <div className="flex items-center gap-3">
            <span className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ${toneClasses}`}>
                <i className={`fas ${icon} text-lg`} aria-hidden="true" />
            </span>
            <div className="min-w-0">
                <p className="text-xs font-medium text-text-muted">{label}</p>
                <p className="mt-1 truncate text-sm font-bold text-text">{value}</p>
            </div>
        </div>
    );

    if (href) {
        return (
            <Link
                href={href}
                className="block rounded-[var(--radius-control)] border border-border bg-surface p-4 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md hover:border-primary/40"
            >
                {content}
            </Link>
        );
    }

    return (
        <div className="rounded-[var(--radius-control)] border border-border bg-surface p-4">
            {content}
        </div>
    );
}
