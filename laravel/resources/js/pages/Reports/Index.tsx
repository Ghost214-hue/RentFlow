import { Head, router } from '@inertiajs/react';
import { useState, type ReactNode } from 'react';
import AppLayout from '@/layouts/AppLayout';
import { formatMoney } from '@/lib/money';
import type { ReportsProps, ShareRow } from '@/types';

/**
 * Reports.
 *
 * One page consolidating the five report screens the legacy backend exposed
 * separately (portfolio, bills, financial, tenancy/vacancy, complaints), plus a
 * maintenance aggregate that never existed there at all.
 *
 * Every figure arrives from the server, computed from the bill and allocation
 * ledger. Nothing is calculated in the browser, so a report can never disagree
 * with what a renter was actually charged.
 */

type Tab = 'overview' | 'billing' | 'revenue' | 'occupancy' | 'issues';

const TABS: Array<{ id: Tab; label: string }> = [
    { id: 'overview', label: 'Overview' },
    { id: 'billing', label: 'Billing' },
    { id: 'revenue', label: 'Revenue' },
    { id: 'occupancy', label: 'Occupancy' },
    { id: 'issues', label: 'Complaints & Repairs' },
];

const panel = 'bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5';

function Tile({ label, value, tone = 'slate' }: { label: string; value: string; tone?: string }) {
    const tones: Record<string, string> = {
        slate: 'bg-slate-100 text-slate-600',
        blue: 'bg-blue-50 text-blue-700',
        emerald: 'bg-emerald-50 text-emerald-700',
        rose: 'bg-rose-50 text-rose-700',
        violet: 'bg-violet-50 text-violet-700',
        amber: 'bg-amber-50 text-amber-700',
    };

    return (
        <div className={`${panel} p-4`}>
            <div className={`inline-flex px-2.5 py-1 rounded-full text-xs font-medium ${tones[tone]}`}>{label}</div>
            <p className="mt-2 text-xl font-bold text-slate-900">{value}</p>
        </div>
    );
}

/**
 * A labelled proportion bar.
 *
 * The width is the only place a report touches arithmetic: it turns the server's
 * decimal percentage into a CSS length. The figure itself is never recomputed.
 */
function Bar({ label, amount, share, tone = 'blue' }: { label: string; amount: string; share: string; tone?: string }) {
    const tones: Record<string, string> = {
        blue: 'bg-blue-500',
        emerald: 'bg-emerald-500',
        violet: 'bg-violet-500',
        amber: 'bg-amber-500',
        rose: 'bg-rose-500',
    };
    const pct = Math.max(0, Math.min(100, Number(share.replace(/,/g, '')) || 0));

    return (
        <li>
            <div className="flex items-center justify-between text-xs mb-1">
                <span className="font-medium text-slate-700">{label}</span>
                <span className="text-slate-500">
                    {formatMoney(amount)} · {share}%
                </span>
            </div>
            <div className="h-2 rounded-full bg-slate-100 overflow-hidden">
                <div className={`h-full rounded-full ${tones[tone]}`} style={{ width: `${pct}%` }} />
            </div>
        </li>
    );
}

function Section({ title, children, action }: { title: string; children: ReactNode; action?: ReactNode }) {
    return (
        <section className={panel}>
            <div className="flex items-center justify-between mb-4">
                <h2 className="font-semibold text-slate-900">{title}</h2>
                {action}
            </div>
            {children}
        </section>
    );
}

function Empty({ children }: { children: ReactNode }) {
    return <p className="py-6 text-center text-slate-400 text-sm">{children}</p>;
}

export default function ReportsIndex(props: ReportsProps) {
    const {
        month,
        months,
        summary,
        monthly,
        topDebtors,
        propertyPerformance,
        billing,
        composition,
        byHouse,
        revenue,
        occupancy,
        occupancyByProperty,
        vacantUnits,
        complaints,
        maintenance,
    } = props;

    const [tab, setTab] = useState<Tab>('overview');
    const { counts, money } = summary;

    // Widest bar in the series, so the chart scales relatively.
    const peak = monthly.reduce((max, row) => Math.max(max, Number(row.billed.replace(/,/g, ''))), 0);

    return (
        <>
            <Head title="Reports" />

            <AppLayout
                title="Reports"
                actions={
                    <div className="flex flex-wrap items-end gap-3">
                        <div>
                            <h1 className="text-2xl font-bold text-slate-900">Reports</h1>
                            <p className="text-slate-500 mt-1">Portfolio performance</p>
                        </div>

                        {/*
                         * One month selector drives the tiles, the chart and the
                         * per-unit table at once. Letting each section pick its
                         * own month is how a report ends up showing figures that
                         * describe different periods without saying so.
                         */}
                        {months.length > 0 && (
                            <label className="text-xs text-slate-500">
                                <span className="block mb-1">Month</span>
                                <select
                                    value={month}
                                    onChange={(e) => router.get('/reports', { month: e.target.value }, { preserveState: true })}
                                    className="rounded-lg border border-slate-300 px-3 py-1.5 text-sm text-slate-700 bg-white"
                                >
                                    {months.map((m) => (
                                        <option key={m} value={m}>
                                            {m}
                                        </option>
                                    ))}
                                </select>
                            </label>
                        )}
                    </div>
                }
            >
                <nav className="flex flex-wrap gap-1 mb-6 border-b border-slate-200">
                    {TABS.map((t) => (
                        <button
                            key={t.id}
                            type="button"
                            onClick={() => setTab(t.id)}
                            className={`px-4 py-2 text-sm font-medium -mb-px border-b-2 ${
                                tab === t.id
                                    ? 'border-blue-600 text-blue-700'
                                    : 'border-transparent text-slate-500 hover:text-slate-700'
                            }`}
                        >
                            {t.label}
                        </button>
                    ))}
                </nav>

                {tab === 'overview' && (
                    <>
                        <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
                            <Tile label="Billed to date" value={formatMoney(money.billed_to_date)} tone="blue" />
                            <Tile label="Received" value={formatMoney(money.received_to_date)} tone="emerald" />
                            <Tile
                                label="Outstanding"
                                value={formatMoney(money.outstanding)}
                                tone={money.outstanding === '0.00' ? 'emerald' : 'rose'}
                            />
                            <Tile label="Collection rate" value={`${money.collection_rate}%`} tone="violet" />
                        </div>

                        <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                            {[
                                { label: 'Properties', value: counts.properties },
                                { label: 'Units', value: counts.houses },
                                { label: 'Occupied', value: counts.occupied },
                                { label: 'Vacant', value: counts.vacant },
                                { label: 'Renters', value: counts.renters },
                                { label: 'Active renters', value: counts.active_renters },
                                { label: 'Open complaints', value: counts.open_complaints },
                                { label: 'Open maintenance', value: counts.open_maintenance },
                            ].map((item) => (
                                <div key={item.label} className={`${panel} p-4 flex items-center justify-between`}>
                                    <span className="text-sm text-slate-500">{item.label}</span>
                                    <span className="text-lg font-bold text-slate-900">{item.value}</span>
                                </div>
                            ))}
                        </div>

                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                            <Section title="Billed vs received">
                                {monthly.length === 0 ? (
                                    <Empty>No billing history yet.</Empty>
                                ) : (
                                    <ul className="space-y-3">
                                        {monthly.map((row) => {
                                            const billed = Number(row.billed.replace(/,/g, ''));
                                            const width = peak === 0 ? 0 : Math.round((billed / peak) * 100);

                                            return (
                                                <li key={row.month}>
                                                    <div className="flex items-center justify-between text-xs mb-1">
                                                        <span className="font-medium text-slate-700">{row.month}</span>
                                                        <span className="text-slate-500">
                                                            {formatMoney(row.received)} / {formatMoney(row.billed)}
                                                        </span>
                                                    </div>
                                                    <div className="h-2 rounded-full bg-slate-100 overflow-hidden">
                                                        <div className="h-full rounded-full bg-blue-500" style={{ width: `${width}%` }} />
                                                    </div>
                                                </li>
                                            );
                                        })}
                                    </ul>
                                )}
                            </Section>

                            <Section title="Top debtors">
                                {topDebtors.length === 0 ? (
                                    <Empty>Everyone is settled up.</Empty>
                                ) : (
                                    <ul className="divide-y divide-blue-50">
                                        {topDebtors.map((row) => (
                                            <li key={row.id} className="py-3 flex items-center justify-between">
                                                <span className="text-sm text-slate-800">{row.name}</span>
                                                <span className="text-sm font-semibold text-rose-600">
                                                    {formatMoney(row.outstanding)}
                                                </span>
                                            </li>
                                        ))}
                                    </ul>
                                )}
                            </Section>
                        </div>

                        {/* Legacy ReportController's per-property view. */}
                        <Section title={`Property performance · ${month}`}>
                            {propertyPerformance.length === 0 ? (
                                <Empty>No properties yet.</Empty>
                            ) : (
                                <div className="overflow-x-auto">
                                    <table className="w-full text-sm">
                                        <thead>
                                            <tr className="text-left text-xs uppercase tracking-wide text-slate-400 border-b">
                                                <th className="pb-2 pr-3">Property</th>
                                                <th className="pb-2 pr-3">Units</th>
                                                <th className="pb-2 pr-3">Billed</th>
                                                <th className="pb-2 pr-3">Collected</th>
                                                <th className="pb-2 pr-3">Outstanding</th>
                                                <th className="pb-2">Rate</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-blue-50">
                                            {propertyPerformance.map((row) => (
                                                <tr key={row.property_id}>
                                                    <td className="py-2 pr-3 text-slate-800">{row.name}</td>
                                                    <td className="py-2 pr-3 text-slate-600">
                                                        {row.occupied}/{row.units}
                                                    </td>
                                                    <td className="py-2 pr-3">{formatMoney(row.billed)}</td>
                                                    <td className="py-2 pr-3 text-emerald-700">{formatMoney(row.collected)}</td>
                                                    <td className="py-2 pr-3 text-rose-600">{formatMoney(row.outstanding)}</td>
                                                    <td className="py-2 text-slate-600">{row.collection_rate}%</td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            )}
                        </Section>
                    </>
                )}

                {tab === 'billing' && (
                    <>
                        <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
                            <Tile label="Billed" value={formatMoney(billing.money.billed)} tone="blue" />
                            <Tile label="Paid" value={formatMoney(billing.money.paid)} tone="emerald" />
                            <Tile label="Part paid" value={formatMoney(billing.money.partial)} tone="amber" />
                            <Tile label="Unpaid" value={formatMoney(billing.money.pending)} tone="rose" />
                        </div>

                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                            {/*
                             * Overdue is DERIVED from the due date, not read from
                             * the stored status. A bill nobody got round to
                             * flipping is just as overdue as one somebody did.
                             */}
                            <Section
                                title="Arrears"
                                action={
                                    <span className="text-sm font-semibold text-rose-600">
                                        {formatMoney(billing.arrears.amount)}
                                    </span>
                                }
                            >
                                {billing.arrears.count === 0 ? (
                                    <Empty>Nothing is overdue. </Empty>
                                ) : (
                                    <>
                                        <ul className="space-y-3">
                                            {billing.arrears.aging.map((bucket) => (
                                                <Bar
                                                    key={bucket.label}
                                                    label={`${bucket.label} · ${bucket.count} bill${bucket.count === 1 ? '' : 's'}`}
                                                    amount={bucket.amount}
                                                    share={bucket.share}
                                                    tone="rose"
                                                />
                                            ))}
                                        </ul>
                                        {billing.arrears.oldest_month && (
                                            <p className="mt-4 text-xs text-slate-500">
                                                Oldest unpaid bill:{' '}
                                                <span className="font-medium text-slate-700">{billing.arrears.oldest_month}</span>
                                            </p>
                                        )}
                                    </>
                                )}
                            </Section>

                            <Section title={`What the bill is made of · ${month}`}>
                                <ul className="space-y-3">
                                    {composition.map((row) => (
                                        <Bar key={row.label} label={row.label} amount={row.amount} share={row.share} tone="violet" />
                                    ))}
                                </ul>
                                <p className="mt-4 text-xs text-slate-500">
                                    Utilities are recovered from the renter; rent is income.
                                </p>
                            </Section>
                        </div>

                        <Section title={`Per-unit collection · ${month}`}>
                            {byHouse.length === 0 ? (
                                <Empty>No bills for this month.</Empty>
                            ) : (
                                <div className="overflow-x-auto">
                                    <table className="w-full text-sm">
                                        <thead>
                                            <tr className="text-left text-xs uppercase tracking-wide text-slate-400 border-b">
                                                <th className="pb-2 pr-3">Unit</th>
                                                <th className="pb-2 pr-3">Property</th>
                                                <th className="pb-2 pr-3">Billed</th>
                                                <th className="pb-2 pr-3">Collected</th>
                                                <th className="pb-2">Outstanding</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-blue-50">
                                            {byHouse.map((row) => (
                                                <tr key={row.house_id}>
                                                    <td className="py-2 pr-3 font-medium text-slate-800">{row.unit}</td>
                                                    <td className="py-2 pr-3 text-slate-600">{row.property ?? '--'}</td>
                                                    <td className="py-2 pr-3">{formatMoney(row.billed)}</td>
                                                    <td className="py-2 pr-3 text-emerald-700">{formatMoney(row.collected)}</td>
                                                    <td className="py-2 text-rose-600">{formatMoney(row.outstanding)}</td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            )}
                        </Section>
                    </>
                )}

                {tab === 'revenue' && (
                    <>
                        <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
                            <Tile label="Collected" value={formatMoney(revenue.collected)} tone="emerald" />
                            <Tile label="Pending" value={formatMoney(revenue.pending)} tone="amber" />
                            <Tile label="Failed" value={formatMoney(revenue.failed)} tone="rose" />
                            <Tile label="Still in flight" value={formatMoney(revenue.in_flight)} tone="violet" />
                        </div>

                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            {/*
                             * The method split decides where collection effort
                             * goes: M-Pesa reconciles on its own, cash does not.
                             */}
                            <Section title="How rent was paid">
                                {revenue.method_share.length === 0 ? (
                                    <Empty>No settled payments yet.</Empty>
                                ) : (
                                    <ul className="space-y-3">
                                        {revenue.method_share.map((row: ShareRow) => (
                                            <Bar
                                                key={row.label}
                                                label={`${row.label} · ${row.count} payment${row.count === 1 ? '' : 's'}`}
                                                amount={row.amount}
                                                share={row.share}
                                                tone="emerald"
                                            />
                                        ))}
                                    </ul>
                                )}
                            </Section>

                            <Section title="What the money was for">
                                {revenue.type_breakdown.length === 0 ? (
                                    <Empty>No settled payments yet.</Empty>
                                ) : (
                                    <ul className="space-y-3">
                                        {revenue.type_breakdown.map((row: ShareRow) => (
                                            <Bar
                                                key={row.label}
                                                label={`${row.label} · ${row.count} payment${row.count === 1 ? '' : 's'}`}
                                                amount={row.amount}
                                                share={row.share}
                                                tone="blue"
                                            />
                                        ))}
                                    </ul>
                                )}
                            </Section>
                        </div>

                        <div className="mt-6">
                            <Tile
                                label="Unreconciled payments"
                                value={`${revenue.counts.pending + revenue.counts.failed}`}
                                tone={revenue.counts.pending + revenue.counts.failed === 0 ? 'emerald' : 'amber'}
                            />
                        </div>
                    </>
                )}

                {tab === 'occupancy' && (
                    <>
                        <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
                            <Tile label="Occupancy" value={`${occupancy.units.occupancy_rate}%`} tone="emerald" />
                            <Tile label="Vacancy" value={`${occupancy.units.vacancy_rate}%`} tone="amber" />
                            <Tile
                                label="Rent lost to vacancy"
                                value={formatMoney(occupancy.money.lost_monthly)}
                                tone={occupancy.units.vacant === 0 ? 'emerald' : 'rose'}
                            />
                            <Tile
                                label="Pending terminations"
                                value={String(occupancy.terminations.pending)}
                                tone={occupancy.terminations.pending === 0 ? 'emerald' : 'amber'}
                            />
                        </div>

                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                            <Section title="Occupancy by property">
                                {occupancyByProperty.length === 0 ? (
                                    <Empty>No properties yet.</Empty>
                                ) : (
                                    <ul className="space-y-3">
                                        {occupancyByProperty.map((row) => (
                                            <Bar
                                                key={row.property_id}
                                                label={`${row.name} · ${row.occupied}/${row.units}`}
                                                amount={row.lost_monthly}
                                                share={row.occupancy_rate}
                                                tone={Number(row.occupancy_rate) < 50 ? 'rose' : 'blue'}
                                            />
                                        ))}
                                    </ul>
                                )}
                            </Section>

                            <Section title="Vacant units">
                                {vacantUnits.length === 0 ? (
                                    <Empty>Every unit is occupied.</Empty>
                                ) : (
                                    <ul className="divide-y divide-blue-50">
                                        {vacantUnits.map((row) => (
                                            <li key={row.house_id} className="py-3 flex items-center justify-between">
                                                <span className="text-sm text-slate-800">
                                                    {row.property ? `${row.property} · ` : ''}
                                                    {row.unit}
                                                    {row.vacant_since && (
                                                        <span className="text-xs text-slate-400 ml-2">
                                                            since {row.vacant_since}
                                                        </span>
                                                    )}
                                                </span>
                                                <span className="text-sm font-semibold text-rose-600">
                                                    {formatMoney(row.rent)}/mo
                                                </span>
                                            </li>
                                        ))}
                                    </ul>
                                )}
                            </Section>
                        </div>

                        <Section title="Tenancies">
                            <dl className="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                {[
                                    { label: 'Active', value: occupancy.renters.active },
                                    { label: 'Pending termination', value: occupancy.renters.pending_termination },
                                    { label: 'Terminated', value: occupancy.renters.terminated },
                                    { label: 'Awaiting approval', value: occupancy.terminations.pending },
                                ].map((item) => (
                                    <div key={item.label}>
                                        <dt className="text-xs text-slate-500">{item.label}</dt>
                                        <dd className="text-lg font-bold text-slate-900">{item.value}</dd>
                                    </div>
                                ))}
                            </dl>
                        </Section>
                    </>
                )}

                {tab === 'issues' && (
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <Section
                            title="Complaints"
                            action={
                                <span className="text-sm font-semibold text-slate-700">
                                    {complaints.resolution_rate}% resolved
                                </span>
                            }
                        >
                            <dl className="grid grid-cols-3 gap-4 mb-4">
                                {[
                                    { label: 'Total', value: complaints.total },
                                    { label: 'Unresolved', value: complaints.unresolved },
                                    { label: 'High priority open', value: complaints.high_priority_open },
                                ].map((item) => (
                                    <div key={item.label}>
                                        <dt className="text-xs text-slate-500">{item.label}</dt>
                                        <dd
                                            className={`text-lg font-bold ${
                                                item.label === 'High priority open' && item.value > 0
                                                    ? 'text-rose-600'
                                                    : 'text-slate-900'
                                            }`}
                                        >
                                            {item.value}
                                        </dd>
                                    </div>
                                ))}
                            </dl>

                            {Object.keys(complaints.by_priority).length === 0 ? (
                                <Empty>No complaints raised.</Empty>
                            ) : (
                                <ul className="space-y-2">
                                    {Object.entries(complaints.by_priority).map(([key, value]) => (
                                        <li key={key} className="flex items-center justify-between text-sm">
                                            <span className="capitalize text-slate-600">{key}</span>
                                            <span className="font-medium text-slate-900">{value}</span>
                                        </li>
                                    ))}
                                </ul>
                            )}

                            {Object.keys(complaints.by_category).length > 0 && (
                                <>
                                    <h3 className="text-xs font-semibold uppercase tracking-wide text-slate-400 mt-5 mb-2">
                                        By category
                                    </h3>
                                    <ul className="space-y-2">
                                        {Object.entries(complaints.by_category).map(([key, value]) => (
                                            <li key={key} className="flex items-center justify-between text-sm">
                                                <span className="text-slate-600">{key}</span>
                                                <span className="font-medium text-slate-900">{value}</span>
                                            </li>
                                        ))}
                                    </ul>
                                </>
                            )}
                        </Section>

                        {/* New in this port: the legacy app had no maintenance aggregate. */}
                        <Section
                            title="Repairs"
                            action={
                                <span className="text-sm font-semibold text-slate-700">
                                    {formatMoney(maintenance.money.spent)} spent
                                </span>
                            }
                        >
                            <dl className="grid grid-cols-3 gap-4 mb-4">
                                {[
                                    { label: 'Jobs', value: maintenance.counts.total },
                                    { label: 'Open', value: maintenance.counts.open },
                                    { label: 'Avg per job', value: formatMoney(maintenance.money.per_job) },
                                ].map((item) => (
                                    <div key={item.label}>
                                        <dt className="text-xs text-slate-500">{item.label}</dt>
                                        <dd className="text-lg font-bold text-slate-900">{item.value}</dd>
                                    </div>
                                ))}
                            </dl>

                            <p className="text-xs text-slate-500 mb-4">
                                {formatMoney(maintenance.money.committed)} committed to jobs that are not finished yet.
                            </p>

                            {maintenance.by_category.length === 0 ? (
                                <Empty>No maintenance recorded.</Empty>
                            ) : (
                                <ul className="space-y-2">
                                    {maintenance.by_category.map((row) => (
                                        <li key={row.label} className="flex items-center justify-between text-sm">
                                            <span className="text-slate-600">
                                                {row.label} <span className="text-xs text-slate-400">· {row.count}</span>
                                            </span>
                                            <span className="font-medium text-slate-900">{formatMoney(row.amount)}</span>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </Section>
                    </div>
                )}
            </AppLayout>
        </>
    );
}
