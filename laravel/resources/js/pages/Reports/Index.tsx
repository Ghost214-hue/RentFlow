import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';
import { formatMoney } from '@/lib/money';
import type { ReportsProps } from '@/types';

/**
 * Reports.
 *
 * Ported from frontend/pages/reports.php. Every figure arrives from the server,
 * computed from the bill and allocation ledger. Nothing here is calculated in
 * the browser, so a report can never disagree with what a renter was charged.
 */
export default function ReportsIndex({ summary, monthly, topDebtors, complaints }: ReportsProps) {
    const { counts, money } = summary;

    const tiles = [
        { label: 'Billed to date', value: formatMoney(money.billed_to_date), tone: 'blue' },
        { label: 'Received', value: formatMoney(money.received_to_date), tone: 'emerald' },
        { label: 'Outstanding', value: formatMoney(money.outstanding), tone: money.outstanding === '0.00' ? 'emerald' : 'rose' },
        { label: 'Collection rate', value: `${money.collection_rate}%`, tone: 'violet' },
    ] as const;

    const tone = {
        blue: 'bg-blue-50 text-blue-700',
        emerald: 'bg-emerald-50 text-emerald-700',
        rose: 'bg-rose-50 text-rose-700',
        violet: 'bg-violet-50 text-violet-700',
    } as const;

    // Widest bar in the series, so the chart scales relatively.
    const peak = monthly.reduce(
        (max, row) => Math.max(max, Number(row.billed.replace(/,/g, ''))),
        0,
    );

    return (
        <>
            <Head title="Reports" />

            <AppLayout
                title="Reports"
                actions={
                    <div>
                        <h1 className="text-2xl font-bold text-slate-900">Reports</h1>
                        <p className="text-slate-500 mt-1">Portfolio performance</p>
                    </div>
                }
            >
                <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
                    {tiles.map((tile) => (
                        <div key={tile.label} className="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                            <div className={`inline-flex px-2.5 py-1 rounded-full text-xs font-medium ${tone[tile.tone]}`}>
                                {tile.label}
                            </div>
                            <p className="mt-3 text-2xl font-bold text-slate-900">{tile.value}</p>
                        </div>
                    ))}
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
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
                        <div key={item.label} className="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-4 flex items-center justify-between">
                            <span className="text-sm text-slate-500">{item.label}</span>
                            <span className="text-lg font-bold text-slate-900">{item.value}</span>
                        </div>
                    ))}
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <section className="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                        <h2 className="font-semibold text-slate-900 mb-4">Billed vs received</h2>

                        {monthly.length === 0 ? (
                            <p className="py-8 text-center text-slate-400 text-sm">No billing history yet.</p>
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
                    </section>

                    <section className="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                        <h2 className="font-semibold text-slate-900 mb-4">Top debtors</h2>

                        {topDebtors.length === 0 ? (
                            <p className="py-8 text-center text-slate-400 text-sm">Everyone is settled up.</p>
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
                    </section>

                    <section className="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                        <h2 className="font-semibold text-slate-900 mb-4">Complaints by status</h2>
                        <p className="text-3xl font-bold text-slate-900 mb-3">{complaints.total}</p>
                        <ul className="space-y-2">
                            {Object.entries(complaints.by_status).map(([key, value]) => (
                                <li key={key} className="flex items-center justify-between text-sm">
                                    <span className="capitalize text-slate-600">{key}</span>
                                    <span className="font-medium text-slate-900">{value}</span>
                                </li>
                            ))}
                        </ul>
                    </section>

                    <section className="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5 lg:col-span-2">
                        <h2 className="font-semibold text-slate-900 mb-4">Complaints by category</h2>
                        {Object.keys(complaints.by_category).length === 0 ? (
                            <p className="py-6 text-center text-slate-400 text-sm">No categorised complaints.</p>
                        ) : (
                            <ul className="space-y-2">
                                {Object.entries(complaints.by_category).map(([key, value]) => (
                                    <li key={key} className="flex items-center justify-between text-sm">
                                        <span className="text-slate-600">{key}</span>
                                        <span className="font-medium text-slate-900">{value}</span>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </section>
                </div>
            </AppLayout>
        </>
    );
}