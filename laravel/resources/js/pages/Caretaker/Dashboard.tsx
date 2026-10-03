import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';
import { formatMoney } from '@/lib/money';

/** Props for the caretaker's own dashboard. */
export interface CaretakerDashboardProps {
    caretaker: {
        name: string;
        email: string;
        phone: string | null;
        assigned_count: number;
    };
    stats: {
        properties: number;
        units: number;
        occupiedUnits: number;
        vacantUnits: number;
        renters: number;
        openComplaints: number;
        myTasks: number;
        /** All money arrives as a decimal string computed server-side. */
        expected: string;
        collected: string;
        outstanding: string;
    };
    /** Only the properties this caretaker is assigned to. */
    properties: Array<{
        id: number;
        name: string;
        address: string;
        units: number;
        rent: string;
    }>;
}

/**
 * The caretaker's dashboard.
 *
 * Ported from frontend/pages/caretaker-dashboard.php. Every figure is scoped to
 * the caretaker's ASSIGNED properties on the server, so nothing here needs
 * filtering in the browser.
 */
export default function CaretakerDashboard({
    caretaker,
    stats,
    properties,
}: CaretakerDashboardProps) {
    const tiles = [
        {
            label: 'Expected',
            value: formatMoney(stats.expected),
            hint: 'Billed on your properties',
            tone: 'bg-blue-50 text-blue-700',
        },
        {
            label: 'Collected',
            value: formatMoney(stats.collected),
            hint: 'Payments received',
            tone: 'bg-emerald-50 text-emerald-700',
        },
        {
            label: 'Outstanding',
            value: formatMoney(stats.outstanding),
            hint: 'Still to collect',
            tone: stats.outstanding === '0.00'
                ? 'bg-emerald-50 text-emerald-700'
                : 'bg-rose-50 text-rose-700',
        },
        {
            label: 'Occupancy',
            value: `${stats.occupiedUnits}/${stats.units}`,
            hint: `${stats.vacantUnits} vacant`,
            tone: 'bg-violet-50 text-violet-700',
        },
    ];

    return (
        <>
            <Head title="Dashboard" />

            <AppLayout
                title="Dashboard"
                actions={
                    <div>
                        <h1 className="text-2xl font-bold text-slate-900">
                            Welcome, {caretaker.name}
                        </h1>
                        <p className="text-slate-500 mt-1">
                            {caretaker.assigned_count === 0
                                ? 'No properties assigned to you yet'
                                : `Managing ${caretaker.assigned_count} propert${caretaker.assigned_count === 1 ? 'y' : 'ies'}`}
                        </p>
                    </div>
                }
            >
                <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
                    {tiles.map((tile) => (
                        <div
                            key={tile.label}
                            className="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5"
                        >
                            <div
                                className={`inline-flex px-2.5 py-1 rounded-full text-xs font-medium ${tile.tone}`}
                            >
                                {tile.label}
                            </div>
                            <p className="mt-3 text-2xl font-bold text-slate-900">{tile.value}</p>
                            <p className="mt-1 text-xs text-slate-500">{tile.hint}</p>
                        </div>
                    ))}
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    {[
                        { label: 'Renters', value: stats.renters, href: '/renters', icon: 'fa-users' },
                        { label: 'Open complaints', value: stats.openComplaints, href: '/complaints', icon: 'fa-exclamation-triangle' },
                        { label: 'My open tasks', value: stats.myTasks, href: '/maintenance', icon: 'fa-tools' },
                    ].map((item) => (
                        <Link
                            key={item.label}
                            href={item.href}
                            className="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5 flex items-center gap-4 hover:shadow-md transition-shadow"
                        >
                            <div className="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                                <i className={`fas ${item.icon} text-blue-600`} aria-hidden="true" />
                            </div>
                            <div>
                                <p className="text-xl font-bold text-slate-900">{item.value}</p>
                                <p className="text-xs text-slate-500">{item.label}</p>
                            </div>
                        </Link>
                    ))}
                </div>

                <section className="bg-white rounded-2xl shadow-sm border border-blue-100/50 overflow-hidden">
                    <div className="p-5 border-b border-blue-50">
                        <h2 className="font-semibold text-slate-900">Your Properties</h2>
                        <p className="text-xs text-slate-500 mt-0.5">
                            Only the properties assigned to you
                        </p>
                    </div>

                    {properties.length === 0 ? (
                        <p className="px-5 py-12 text-center text-slate-400">
                            You have not been assigned any properties yet.
                        </p>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead>
                                    <tr className="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50 bg-blue-50/50">
                                        <th className="px-6 py-4">Property</th>
                                        <th className="px-6 py-4">Address</th>
                                        <th className="px-6 py-4">Units</th>
                                        <th className="px-6 py-4">Default Rent</th>
                                        <th className="px-6 py-4"></th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-blue-50">
                                    {properties.map((property) => (
                                        <tr key={property.id} className="hover:bg-blue-50/30 transition-colors">
                                            <td className="px-6 py-4 text-sm font-medium text-slate-900">
                                                {property.name}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-slate-600">
                                                {property.address}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-slate-600">
                                                {property.units}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-slate-600">
                                                {formatMoney(property.rent)}
                                            </td>
                                            <td className="px-6 py-4 text-right">
                                                <Link
                                                    href={`/houses?property_id=${property.id}`}
                                                    className="text-xs font-medium text-blue-600 hover:text-blue-700"
                                                >
                                                    View units
                                                </Link>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </section>
            </AppLayout>
        </>
    );
}