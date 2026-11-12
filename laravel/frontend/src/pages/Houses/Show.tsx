import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';
import { formatMoney } from '@/lib/money';
import { initials } from './helpers';

interface Props {
    house: {
        id: number;
        unit: string;
        type: string | null;
        status: string;
        rent: string;
        water_meter: string | null;
        elec_meter: string | null;
        last_reading: string;
        property_id: number;
        property_name: string | null;
        property_address: string | null;
    };
    currentTenant: {
        id: number;
        name: string;
        email: string | null;
        phone: string | null;
        status: string;
        profile_picture: string | null;
    } | null;
    financials: { unpaid_total: string };
    bills: Array<{
        id: number;
        month: string;
        total: string;
        status: string;
        due_date: string | null;
    }>;
    maintenance: Array<{
        id: number;
        title: string;
        status: string;
        priority: string;
        cost: string;
        created_at: string | null;
    }>;
}

const BILL_TONE: Record<string, string> = {
    paid: 'bg-emerald-100 text-emerald-700',
    partial: 'bg-amber-100 text-amber-700',
    pending: 'bg-slate-100 text-slate-600',
    overdue: 'bg-red-100 text-red-700',
};

function Row({ label, value }: { label: string; value: string | null }) {
    return (
        <div className="py-3 border-b border-blue-50 last:border-0">
            <dt className="text-xs font-semibold text-slate-500 uppercase tracking-wider">{label}</dt>
            <dd className="text-sm text-slate-900 mt-1">{value ?? '-'}</dd>
        </div>
    );
}

/**
 * Unit detail.
 *
 * Ported from frontend/pages/house-details.php: unit facts, the current tenant,
 * meter numbers, recent bills and maintenance.
 */
export default function HouseShow({ house, currentTenant, financials, bills, maintenance }: Props) {
    const occupied = house.status === 'occupied';

    return (
        <>
            <Head title={`Unit ${house.unit}`} />

            <AppLayout title={`Unit ${house.unit}`}>
                <Link href="/houses" className="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-blue-600 mb-4">
                    <i className="fas fa-arrow-left" aria-hidden="true" />
                    Back to units
                </Link>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Unit summary */}
                    <div className="lg:col-span-1 space-y-6">
                        <div className="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-6">
                            <div className="flex items-start justify-between">
                                <div>
                                    <h1 className="text-xl font-bold text-slate-900">Unit {house.unit}</h1>
                                    <p className="text-sm text-slate-500 mt-1">{house.type ?? 'Unit'}</p>
                                </div>
                                <i className="fas fa-door-open text-blue-500 text-xl" aria-hidden="true" />
                            </div>

                            <span className={`inline-block mt-4 px-3 py-1 rounded-full text-xs font-medium ${
                                occupied ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'
                            }`}>
                                {house.status}
                            </span>

                            {house.property_name && (
                                <Link href={`/properties`}
                                    className="mt-4 block text-sm text-blue-600 hover:text-blue-700">
                                    <i className="fas fa-building mr-1.5" aria-hidden="true" />
                                    {house.property_name}
                                </Link>
                            )}
                            {house.property_address && (
                                <p className="text-xs text-slate-500 mt-1">{house.property_address}</p>
                            )}

                            <dl className="mt-4">
                                <Row label="Monthly Rent" value={formatMoney(house.rent)} />
                                <Row label="Unpaid Total" value={formatMoney(financials.unpaid_total)} />
                            </dl>
                        </div>

                        {/* Meters */}
                        <div className="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-6">
                            <h2 className="flex items-center gap-2 font-semibold text-slate-900 mb-2">
                                <i className="fas fa-tachometer-alt text-blue-600" aria-hidden="true" />
                                Meters
                            </h2>
                            <dl>
                                <Row label="Water Meter" value={house.water_meter} />
                                <Row label="Electric Meter" value={house.elec_meter} />
                                <Row label="Last Reading" value={formatMoney(house.last_reading)} />
                            </dl>
                        </div>
                    </div>

                    <div className="lg:col-span-2 space-y-6">
                        {/* Current tenant */}
                        <div className="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-6">
                            <h2 className="flex items-center gap-2 font-semibold text-slate-900 mb-4">
                                <i className="fas fa-user text-blue-600" aria-hidden="true" />
                                Current Tenant
                            </h2>

                            {currentTenant === null ? (
                                <p className="text-sm text-slate-400 py-4 text-center">
                                    This unit is vacant.
                                </p>
                            ) : (
                                <div className="flex items-center justify-between gap-4">
                                    <div className="flex items-center gap-3">
                                        <div className="w-11 h-11 rounded-full bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 flex items-center justify-center text-sm font-bold overflow-hidden">
                                            {currentTenant.profile_picture ? (
                                                <img src={currentTenant.profile_picture}
                                                    className="w-full h-full object-cover" alt="" />
                                            ) : (
                                                initials(currentTenant.name)
                                            )}
                                        </div>
                                        <div>
                                            <Link href={`/renters/${currentTenant.id}`}
                                                className="text-sm font-semibold text-slate-900 hover:text-blue-600">
                                                {currentTenant.name}
                                            </Link>
                                            <p className="text-xs text-slate-500">{currentTenant.phone ?? '-'}</p>
                                            <p className="text-xs text-slate-400">{currentTenant.email ?? '-'}</p>
                                        </div>
                                    </div>

                                    <Link href={`/renters/${currentTenant.id}`}
                                        className="px-3 py-1.5 text-xs font-medium bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors">
                                        View profile
                                    </Link>
                                </div>
                            )}
                        </div>

                        {/* Bill history */}
                        <div className="bg-white rounded-2xl shadow-sm border border-blue-100/50 overflow-hidden">
                            <div className="p-5 border-b border-blue-50 flex items-center justify-between">
                                <h2 className="flex items-center gap-2 font-semibold text-slate-900">
                                    <i className="fas fa-receipt text-blue-600" aria-hidden="true" />
                                    Bill History
                                </h2>
                                <Link href="/bills" className="text-sm text-blue-600 hover:text-blue-700">
                                    View all
                                </Link>
                            </div>

                            {bills.length === 0 ? (
                                <p className="px-5 py-10 text-center text-slate-400 text-sm">No bills raised yet.</p>
                            ) : (
                                <div className="overflow-x-auto">
                                    <table className="w-full">
                                        <thead>
                                            <tr className="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50 bg-blue-50/50">
                                                <th className="px-5 py-3">Month</th>
                                                <th className="px-5 py-3">Due</th>
                                                <th className="px-5 py-3">Amount</th>
                                                <th className="px-5 py-3">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-blue-50">
                                            {bills.map((bill) => (
                                                <tr key={bill.id} className="hover:bg-blue-50/30 transition-colors">
                                                    <td className="px-5 py-3 text-sm font-medium text-slate-900">{bill.month}</td>
                                                    <td className="px-5 py-3 text-sm text-slate-500">{bill.due_date ?? '-'}</td>
                                                    <td className="px-5 py-3 text-sm font-medium text-slate-900">
                                                        {formatMoney(bill.total)}
                                                    </td>
                                                    <td className="px-5 py-3">
                                                        <span className={`px-2 py-1 rounded-full text-xs font-medium ${BILL_TONE[bill.status] ?? BILL_TONE.pending}`}>
                                                            {bill.status}
                                                        </span>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            )}
                        </div>

                        {/* Maintenance */}
                        <div className="bg-white rounded-2xl shadow-sm border border-blue-100/50 overflow-hidden">
                            <div className="p-5 border-b border-blue-50 flex items-center justify-between">
                                <h2 className="flex items-center gap-2 font-semibold text-slate-900">
                                    <i className="fas fa-tools text-amber-600" aria-hidden="true" />
                                    Maintenance
                                </h2>
                                <Link href="/maintenance" className="text-sm text-blue-600 hover:text-blue-700">
                                    View all
                                </Link>
                            </div>

                            {maintenance.length === 0 ? (
                                <p className="px-5 py-10 text-center text-slate-400 text-sm">No maintenance requests.</p>
                            ) : (
                                <ul className="divide-y divide-blue-50">
                                    {maintenance.map((record) => (
                                        <li key={record.id} className="px-5 py-4 flex items-center justify-between gap-4">
                                            <div>
                                                <p className="text-sm font-medium text-slate-900">{record.title}</p>
                                                <p className="text-xs text-slate-500 mt-0.5 capitalize">
                                                    {record.status} · {record.priority}
                                                </p>
                                            </div>
                                            <p className="text-sm font-medium text-slate-700">{formatMoney(record.cost)}</p>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </div>
                    </div>
                </div>
            </AppLayout>
        </>
    );
}