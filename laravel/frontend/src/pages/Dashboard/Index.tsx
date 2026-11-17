import { Head } from '@inertiajs/react';
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
        { label: 'Properties', value: String(stats.properties), icon: 'fa-building', tone: 'primary' as const },
        { label: 'Units', value: String(stats.units), icon: 'fa-home', tone: 'success' as const },
        { label: 'Occupancy', value: `${occupancy}%`, icon: 'fa-chart-pie', tone: 'warning' as const },
        { label: 'Collected this month', value: formatMoney(monthly.current.collected), icon: 'fa-money-bill-wave', tone: 'info' as const },
    ];

    return (
        <>
            <Head title="Dashboard" />
            <AppLayout
                title="Dashboard"
                subtitle="Your portfolio, at a glance"
                actions={
                    <div className="inline-flex items-center gap-2 rounded-full border border-border bg-surface-raised px-3 py-1.5 text-sm text-text-muted shadow-card">
                        <i className="fas fa-chart-line text-primary" aria-hidden="true" />
                        Monthly overview
                    </div>
                }
            >
                <section aria-label="Portfolio statistics" className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    {cards.map((card) => (
                        <StatCard
                            key={card.label}
                            label={card.label}
                            value={card.value}
                            icon={card.icon}
                            tone={card.tone}
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
                            <div className="inline-flex items-center gap-4 text-xs font-medium text-text-muted">
                                <span className="inline-flex items-center gap-2">
                                    <span className="h-2.5 w-2.5 rounded-sm bg-blue-500" aria-hidden="true" />
                                    Billed
                                </span>
                                <span className="inline-flex items-center gap-2">
                                    <span className="h-2.5 w-2.5 rounded-sm bg-cyan-400" aria-hidden="true" />
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
                                        <span className="mt-2 truncate text-center text-xs font-medium text-text-muted">
                                            {month.label}
                                        </span>
                                    </div>
                                ))}
                            </div>

                            <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                                <MonthlyMetric label="Billed" value={formatMoney(monthly.current.billed)} icon="fa-file-invoice-dollar" />
                                <MonthlyMetric label="Collected" value={formatMoney(monthly.current.collected)} icon="fa-circle-check" tone="success" />
                                <MonthlyMetric label="Outstanding" value={formatMoney(monthly.current.outstanding)} icon="fa-hourglass-half" tone="warning" />
                                <MonthlyMetric label="Collection rate" value={`${monthly.current.collectionRate}%`} icon="fa-bullseye" tone="info" />
                            </div>
                        </CardBody>
                    </Card>
                </section>

                <section className="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2" aria-label="Portfolio financial totals">
                    <Card>
                        <CardBody>
                            <div className="flex items-center gap-3">
                                <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                    <i className="fas fa-layer-group" aria-hidden="true" />
                                </span>
                                <div>
                                    <p className="text-sm text-text-muted">All-time billed</p>
                                    <p className="mt-1 text-xl font-bold text-text">{formatMoney(stats.expected)}</p>
                                </div>
                            </div>
                        </CardBody>
                    </Card>
                    <Card>
                        <CardBody>
                            <div className="flex items-center gap-3">
                                <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-success/10 text-success">
                                    <i className="fas fa-wallet" aria-hidden="true" />
                                </span>
                                <div>
                                    <p className="text-sm text-text-muted">All-time collected</p>
                                    <p className="mt-1 text-xl font-bold text-text">{formatMoney(stats.collected)}</p>
                                </div>
                            </div>
                        </CardBody>
                    </Card>
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
}: {
    label: string;
    value: string;
    icon: string;
    tone?: 'primary' | 'success' | 'warning' | 'info';
}) {
    const toneClasses = {
        primary: 'bg-primary/10 text-primary',
        success: 'bg-success/10 text-success',
        warning: 'bg-warning/10 text-warning',
        info: 'bg-info/10 text-info',
    }[tone];

    return (
        <div className="rounded-[var(--radius-control)] border border-border bg-surface p-4">
            <div className="flex items-center gap-3">
                <span className={`flex h-9 w-9 shrink-0 items-center justify-center rounded-lg ${toneClasses}`}>
                    <i className={`fas ${icon}`} aria-hidden="true" />
                </span>
                <div className="min-w-0">
                    <p className="text-xs font-medium text-text-muted">{label}</p>
                    <p className="mt-1 truncate text-sm font-bold text-text">{value}</p>
                </div>
            </div>
        </div>
    );
}
