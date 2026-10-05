import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';
import { formatMoney } from '@/lib/money';
import type { Renter } from '@/types';
import { initials } from '@/pages/Houses/helpers';

interface History {
    bills: Array<{ id: number; month: string; total: string; status: string; due_date: string | null }>;
    payments: Array<{ id: number; amount: string; method: string | null; status: string; date: string | null }>;
    complaints: Array<{ id: number; title: string; status: string; created_at: string | null }>;
    maintenance: Array<{ id: number; title: string; status: string; priority: string; created_at: string | null }>;
    unpaid_total: string;
}

interface Props {
    renter: Renter;
    history: History;
    flash?: { success?: string | null };
}

const BILL_TONE: Record<string, string> = {
    paid: 'bg-emerald-100 text-emerald-700',
    partial: 'bg-amber-100 text-amber-700',
    pending: 'bg-slate-100 text-slate-600',
    overdue: 'bg-red-100 text-red-700',
};

function Detail({ label, value }: { label: string; value: string | null }) {
    return (
        <div className="py-3 border-b border-blue-50 last:border-0">
            <dt className="text-xs font-semibold text-slate-500 uppercase tracking-wider">{label}</dt>
            <dd className="text-sm text-slate-900 mt-1">{value ?? '-'}</dd>
        </div>
    );
}

function Section({ icon, tone, title, children }: {
    icon: string; tone: string; title: string; children: React.ReactNode;
}) {
    return (
        <div className="bg-white rounded-2xl shadow-sm border border-blue-100/50 overflow-hidden">
            <div className="p-5 border-b border-blue-50">
                <h2 className="flex items-center gap-2 font-semibold text-slate-900">
                    <i className={`fas ${icon} ${tone}`} aria-hidden="true" />
                    {title}
                </h2>
            </div>
            {children}
        </div>
    );
}

/**
 * Renter detail.
 *
 * Ported from frontend/pages/tenant-details.php: identity, next of kin,
 * financials, bill history, receipts, complaints, maintenance and documents.
 *
 * Outstanding is computed from unpaid bills on the server, not read from the
 * drifting tenants.balance cache.
 */
export default function RenterShow({ renter, history, flash }: Props) {
    const owes = Number(history.unpaid_total) > 0;

    return (
        <>
            <Head title={renter.name} />

            <AppLayout title="Renter Details">
                {flash?.success && (
                    <div role="status" className="mb-4 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-700 text-sm border border-emerald-100">
                        {flash.success}
                    </div>
                )}

                <Link href="/renters" className="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-blue-600 mb-4">
                    <i className="fas fa-arrow-left" aria-hidden="true" />
                    Back to renters
                </Link>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Identity */}
                    <div className="lg:col-span-1 space-y-6">
                        <div className="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-6 text-center">
                            <div className="w-20 h-20 rounded-full bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 flex items-center justify-center text-2xl font-bold mx-auto overflow-hidden">
                                {renter.profile_picture ? (
                                    <img src={renter.profile_picture} className="w-full h-full object-cover" alt="" />
                                ) : (
                                    initials(renter.name)
                                )}
                            </div>
                            <h1 className="text-xl font-bold text-slate-900 mt-4">{renter.name}</h1>
                            <p className="text-sm text-slate-500 mt-1">{renter.phone ?? '-'}</p>
                            <p className="text-sm text-slate-500 break-all">{renter.email ?? '-'}</p>

                            <span className={`inline-block mt-4 px-3 py-1 rounded-full text-xs font-medium ${
                                renter.status === 'active'
                                    ? 'bg-emerald-100 text-emerald-700'
                                    : renter.status === 'pending_termination'
                                        ? 'bg-amber-100 text-amber-700'
                                        : 'bg-slate-100 text-slate-600'
                            }`}>
                                {renter.status === 'pending_termination' ? 'Pending termination' : renter.status}
                            </span>

                            <dl className="mt-6 text-left space-y-1">
                                <Detail label="Property" value={renter.property_name} />
                                <Detail label="Unit" value={renter.house_unit} />
                                {renter.house_unit && (
                                    <Detail label="" value="" />
                                )}
                            </dl>

                            {renter.house_unit && (
                                <Link href={`/houses`}
                                    className="mt-4 inline-flex items-center gap-1.5 text-xs font-medium text-blue-600 hover:text-blue-700">
                                    <i className="fas fa-door-open" aria-hidden="true" />
                                    View unit
                                </Link>
                            )}
                        </div>

                        <Section icon="fa-id-card" tone="text-blue-600" title="Identification">
                            <dl className="px-5 pb-2">
                                <Detail label="ID Type" value={renter.id_type} />
                                <Detail label="ID Number" value={renter.id_number} />
                                <Detail label="Lease Start" value={renter.lease_start} />
                                <Detail label="Lease End" value={renter.lease_end} />
                            </dl>
                        </Section>

                        <Section icon="fa-users" tone="text-emerald-600" title="Next of Kin">
                            <dl className="px-5 pb-2">
                                <Detail label="Name" value={renter.next_of_kin_name} />
                                <Detail label="Phone" value={renter.next_of_kin_phone} />
                                <Detail label="Email" value={renter.next_of_kin_email} />
                            </dl>
                        </Section>
                    </div>

                    {/* History */}
                    <div className="lg:col-span-2 space-y-6">
                        <div className="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-6">
                            <h2 className="flex items-center gap-2 font-semibold text-slate-900 mb-4">
                                <i className="fas fa-money-bill-wave text-emerald-600" aria-hidden="true" />
                                Financials
                            </h2>
                            <dl className="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                {[
                                    ['Rent', formatMoney(renter.rent), 'text-slate-900'],
                                    ['Deposit', formatMoney(renter.deposit), 'text-slate-900'],
                                    ['Outstanding', formatMoney(history.unpaid_total), owes ? 'text-red-600' : 'text-emerald-600'],
                                    ['Credit', formatMoney(renter.credit), 'text-slate-900'],
                                ].map(([label, value, tone]) => (
                                    <div key={label}>
                                        <dt className="text-xs font-semibold text-slate-500 uppercase tracking-wider">{label}</dt>
                                        <dd className={`text-lg font-bold mt-1 ${tone}`}>{value}</dd>
                                    </div>
                                ))}
                            </dl>
                        </div>

                        <Section icon="fa-receipt" tone="text-blue-600" title="Bill History">
                            {history.bills.length === 0 ? (
                                <p className="px-5 py-10 text-center text-slate-400 text-sm">No bills raised.</p>
                            ) : (
                                <div className="overflow-x-auto">
                                    <table className="w-full">
                                        <thead>
                                            <tr className="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50 bg-blue-50/50">
                                                <th className="px-5 py-3">Month</th>
                                                <th className="px-5 py-3">Amount</th>
                                                <th className="px-5 py-3">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-blue-50">
                                            {history.bills.map((bill) => (
                                                <tr key={bill.id} className="hover:bg-blue-50/30 transition-colors">
                                                    <td className="px-5 py-3 text-sm font-medium text-slate-900">{bill.month}</td>
                                                    <td className="px-5 py-3 text-sm text-slate-900">{formatMoney(bill.total)}</td>
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
                        </Section>

                        <Section icon="fa-money-bill-wave" tone="text-emerald-600" title="Payment History">
                            {history.payments.length === 0 ? (
                                <p className="px-5 py-10 text-center text-slate-400 text-sm">No payments recorded.</p>
                            ) : (
                                <ul className="divide-y divide-blue-50">
                                    {history.payments.map((payment) => (
                                        <li key={payment.id} className="px-5 py-4 flex items-center justify-between">
                                            <div>
                                                <p className="text-sm font-medium text-slate-900">{payment.method ?? 'Payment'}</p>
                                                <p className="text-xs text-slate-500">
                                                    {payment.date ?? '-'} · <span className="capitalize">{payment.status}</span>
                                                </p>
                                            </div>
                                            <p className="text-sm font-semibold text-emerald-600">
                                                {formatMoney(payment.amount)}
                                            </p>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </Section>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <Section icon="fa-exclamation-triangle" tone="text-amber-600" title="Complaints">
                                {history.complaints.length === 0 ? (
                                    <p className="px-5 py-8 text-center text-slate-400 text-sm">None raised.</p>
                                ) : (
                                    <ul className="divide-y divide-blue-50">
                                        {history.complaints.map((c) => (
                                            <li key={c.id} className="px-5 py-3 flex items-center justify-between gap-3">
                                                <span className="text-sm text-slate-800 truncate">{c.title}</span>
                                                <span className="text-xs text-slate-500 capitalize shrink-0">{c.status}</span>
                                            </li>
                                        ))}
                                    </ul>
                                )}
                            </Section>

                            <Section icon="fa-tools" tone="text-amber-600" title="Maintenance">
                                {history.maintenance.length === 0 ? (
                                    <p className="px-5 py-8 text-center text-slate-400 text-sm">No requests.</p>
                                ) : (
                                    <ul className="divide-y divide-blue-50">
                                        {history.maintenance.map((m) => (
                                            <li key={m.id} className="px-5 py-3 flex items-center justify-between gap-3">
                                                <span className="text-sm text-slate-800 truncate">{m.title}</span>
                                                <span className="text-xs text-slate-500 capitalize shrink-0">{m.status}</span>
                                            </li>
                                        ))}
                                    </ul>
                                )}
                            </Section>
                        </div>
                    </div>
                </div>
            </AppLayout>
        </>
    );
}