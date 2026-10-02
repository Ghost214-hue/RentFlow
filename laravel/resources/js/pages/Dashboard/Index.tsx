import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';
import { formatMoney } from '@/lib/money';

interface Props {
    stats: {
        properties: number;
        units: number;
        occupiedUnits: number;
        /** Money strings: formatted, never summed, in the browser. */
        expected: string;
        collected: string;
    };
}

/** Owner/caretaker landing page. Ported from frontend/pages/dashboard.php. */
export default function Dashboard({ stats }: Props) {
    const occupancy = stats.units > 0 ? Math.round((stats.occupiedUnits / stats.units) * 100) : 0;

    const cards = [
        { label: 'Properties', value: String(stats.properties), icon: 'fa-building', tint: 'from-blue-500 to-blue-600' },
        { label: 'Units', value: String(stats.units), icon: 'fa-home', tint: 'from-emerald-500 to-emerald-600' },
        { label: 'Occupancy', value: `${occupancy}%`, icon: 'fa-chart-pie', tint: 'from-amber-500 to-amber-600' },
        { label: 'Collected', value: formatMoney(stats.collected), icon: 'fa-money-bill-wave', tint: 'from-violet-500 to-violet-600' },
    ];

    return (
        <>
            <Head title="Dashboard" />
            <AppLayout title="Dashboard" actions={
            <div>
                <h1 className="text-2xl font-bold text-slate-900">Dashboard</h1>
                <p className="text-slate-500 mt-1">Overview of your portfolio</p>
            </div>
        }>
            <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                {cards.map((card) => (
                    <div key={card.label} className="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-6">
                        <div className={`w-10 h-10 rounded-xl bg-gradient-to-br ${card.tint} flex items-center justify-center text-white mb-4`}>
                            <i className={`fas ${card.icon}`} aria-hidden="true" />
                        </div>
                        <p className="text-sm text-slate-500">{card.label}</p>
                        <p className="text-2xl font-bold text-slate-900 mt-1">{card.value}</p>
                    </div>
                ))}
            </div>

            <div className="mt-6 bg-white rounded-2xl shadow-sm border border-blue-100/50 p-6">
                <h2 className="text-lg font-bold text-slate-900 mb-4">Financials</h2>
                <dl className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt className="text-sm text-slate-500">Total billed</dt>
                        <dd className="text-xl font-bold text-slate-900 mt-1">{formatMoney(stats.expected)}</dd>
                    </div>
                    <div>
                        <dt className="text-sm text-slate-500">Collected</dt>
                        <dd className="text-xl font-bold text-emerald-600 mt-1">{formatMoney(stats.collected)}</dd>
                    </div>
                </dl>
            </div>
        </AppLayout>
        </>
    );
}