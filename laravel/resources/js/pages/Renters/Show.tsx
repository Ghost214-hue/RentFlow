import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';
import { formatMoney } from '@/lib/money';
import type { Renter } from '@/types';
import { initials } from '@/pages/Houses/helpers';

interface Props {
    renter: Renter;
}

/** Label + value row, matching the legacy detail page layout. */
function Detail({ label, value }: { label: string; value: string | null }) {
    return (
        <div className="py-3 border-b border-blue-50 last:border-0">
            <dt className="text-xs font-semibold text-slate-500 uppercase tracking-wider">{label}</dt>
            <dd className="text-sm text-slate-900 mt-1">{value ?? '-'}</dd>
        </div>
    );
}

/** Renter detail. Ported from frontend/pages/tenant-details.php. */
export default function RenterShow({ renter }: Props) {
    const owes = Number(renter.balance) > 0;

    return (
        <>
            <Head title={renter.name} />

            <AppLayout title="Renter Details">
                <Link href="/renters" className="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-blue-600 mb-4">
                    <i className="fas fa-arrow-left" aria-hidden="true" />
                    Back to renters
                </Link>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div className="lg:col-span-1 bg-white rounded-2xl shadow-sm border border-blue-100/50 p-6 text-center">
                        <div className="w-20 h-20 rounded-full bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 flex items-center justify-center text-2xl font-bold mx-auto">
                            {initials(renter.name)}
                        </div>
                        <h1 className="text-xl font-bold text-slate-900 mt-4">{renter.name}</h1>
                        <p className="text-sm text-slate-500 mt-1">{renter.phone}</p>
                        <p className="text-sm text-slate-500">{renter.email ?? '-'}</p>

                        <span className={`inline-block mt-4 px-3 py-1 rounded-full text-xs font-medium ${
                            renter.status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'
                        }`}>
                            {renter.status}
                        </span>

                        <dl className="mt-6 text-left space-y-1">
                            <Detail label="Property" value={renter.property_name} />
                            <Detail label="Unit" value={renter.house_unit} />
                        </dl>
                    </div>

                    <div className="lg:col-span-2 space-y-6">
                        <div className="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-6">
                            <h2 className="text-lg font-bold text-slate-900 mb-2">Financials</h2>
                            <dl className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <dt className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Rent</dt>
                                    <dd className="text-lg font-bold text-slate-900 mt-1">{formatMoney(renter.rent)}</dd>
                                </div>
                                <div>
                                    <dt className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Deposit</dt>
                                    <dd className="text-lg font-bold text-slate-900 mt-1">{formatMoney(renter.deposit)}</dd>
                                </div>
                                <div>
                                    <dt className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Balance</dt>
                                    <dd className={`text-lg font-bold mt-1 ${owes ? 'text-red-600' : 'text-emerald-600'}`}>
                                        {formatMoney(renter.balance)}
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <div className="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-6">
                            <h2 className="text-lg font-bold text-slate-900 mb-2">Identity &amp; Lease</h2>
                            <dl>
                                <Detail label="ID Type" value={renter.id_type} />
                                <Detail label="ID Number" value={renter.id_number} />
                                <Detail label="Lease Start" value={renter.lease_start} />
                                <Detail label="Lease End" value={renter.lease_end} />
                            </dl>
                        </div>

                        <div className="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-6">
                            <h2 className="text-lg font-bold text-slate-900 mb-2">Next of Kin</h2>
                            <dl>
                                <Detail label="Name" value={renter.next_of_kin_name} />
                                <Detail label="Phone" value={renter.next_of_kin_phone} />
                                <Detail label="Email" value={renter.next_of_kin_email} />
                            </dl>
                        </div>
                    </div>
                </div>
            </AppLayout>
        </>
    );
}