import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/layouts/AppLayout';
import { formatMoney } from '@/lib/money';
import { hasCredit, statusTone } from '@/lib/status';
import type { AuthUser, Paginated, Payment, RenterOption } from '@/types';
import RecordPaymentModal from './RecordPaymentModal';

interface Props {
    payments: Paginated<Payment>;
    renters: RenterOption[];
    filters: { renter_id: number | null; status: string | null };
    flash?: { success?: string | null; error?: string | null };
}

/**
 * Payments.
 *
 * Ported from frontend/pages/payments.php. The "allocated" column shows how
 * much of each payment actually reached a bill; anything above it is renter
 * credit. Both numbers are computed on the server.
 */
export default function PaymentsIndex({ payments, renters, filters, flash }: Props) {
    const user = (usePage().props.auth as { user: AuthUser | null }).user;
    const isRenter = user?.role === 'tenant';

    const [showModal, setShowModal] = useState(false);

    function applyFilter(renterId: string, status: string) {
        router.get(
            '/payments',
            { renter_id: renterId || undefined, status: status || undefined },
            { preserveState: true },
        );
    }

    function confirmPayment(payment: Payment) {
        router.post(`/payments/${payment.id}/confirm`, { preserveScroll: true });
    }

    return (
        <>
            <Head title="Payments" />

            <AppLayout
                title="Payments"
                actions={
                    <>
                        <div>
                            <h1 className="text-2xl font-bold text-slate-900">
                                {isRenter ? 'My Payments' : 'Payments'}
                            </h1>
                            <p className="text-slate-500 mt-1">
                                {isRenter ? 'Your payment history' : 'Record and review payments'}
                            </p>
                        </div>

                        <button
                            type="button"
                            onClick={() => setShowModal(true)}
                            className="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all inline-flex items-center gap-2"
                        >
                            <i className="fas fa-plus" aria-hidden="true" />
                            Record Payment
                        </button>
                    </>
                }
            >
                {flash?.success && (
                    <div role="status" className="mb-4 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-700 text-sm border border-emerald-100">
                        {flash.success}
                    </div>
                )}

                {flash?.error && (
                    <div role="alert" className="mb-4 px-4 py-3 rounded-xl bg-red-50 text-red-700 text-sm border border-red-100">
                        {flash.error}
                    </div>
                )}

                {!isRenter && (
                    <div className="mb-4 flex flex-col sm:flex-row gap-3">
                        <select
                            value={filters.renter_id ?? ''}
                            onChange={(e) => applyFilter(e.target.value, filters.status ?? '')}
                            aria-label="Filter by renter"
                            className="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm"
                        >
                            <option value="">All renters</option>
                            {renters.map((r) => (
                                <option key={r.id} value={r.id}>
                                    {r.name}
                                </option>
                            ))}
                        </select>

                        <select
                            value={filters.status ?? ''}
                            onChange={(e) => applyFilter(filters.renter_id === null ? '' : String(filters.renter_id), e.target.value)}
                            aria-label="Filter by status"
                            className="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm"
                        >
                            <option value="">All statuses</option>
                            <option value="completed">Completed</option>
                            <option value="pending">Pending</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                )}

                <div className="bg-white rounded-2xl shadow-sm border border-blue-100/50 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead>
                                <tr className="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50 bg-blue-50/50">
                                    <th className="px-6 py-4">Receipt</th>
                                    <th className="px-6 py-4">Renter</th>
                                    <th className="px-6 py-4">Amount</th>
                                    <th className="px-6 py-4">Allocated</th>
                                    <th className="px-6 py-4">Method</th>
                                    <th className="px-6 py-4">Date</th>
                                    <th className="px-6 py-4">Status</th>
                                    {isRenter && <th className="px-6 py-4">Confirmed</th>}
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-blue-50">
                                {payments.data.length === 0 ? (
                                    <tr>
                                        <td colSpan={isRenter ? 8 : 7} className="px-6 py-12 text-center text-slate-400">
                                            No payments recorded.
                                        </td>
                                    </tr>
                                ) : (
                                    payments.data.map((payment) => {
                                        const tone = statusTone(payment.status);
                                        const credit = hasCredit(payment);

                                        return (
                                            <tr key={payment.id} className="hover:bg-blue-50/30 transition-colors">
                                                <td className="px-6 py-4 text-sm font-medium text-blue-600">
                                                    {payment.receipt ?? 'N/A'}
                                                </td>
                                                <td className="px-6 py-4 text-sm text-slate-700">
                                                    {payment.tenant_name ?? 'N/A'}
                                                </td>
                                                <td className="px-6 py-4 text-sm font-medium text-slate-900">
                                                    {formatMoney(payment.amount)}
                                                </td>
                                                <td className="px-6 py-4 text-sm text-slate-600">
                                                    {formatMoney(payment.allocated)}
                                                    {credit && (
                                                        <span className="ml-2 text-xs text-amber-600">
                                                            credit
                                                        </span>
                                                    )}
                                                </td>
                                                <td className="px-6 py-4 text-sm text-slate-600">
                                                    {payment.method ?? '-'}
                                                </td>
                                                <td className="px-6 py-4 text-sm text-slate-500">
                                                    {payment.date ?? '-'}
                                                </td>
                                                <td className="px-6 py-4">
                                                    <span className={`px-2 py-1 rounded-full text-xs font-medium ${tone.className}`}>
                                                        {tone.label}
                                                    </span>
                                                </td>

                                                {isRenter && (
                                                    <td className="px-6 py-4">
                                                        {payment.tenant_confirmed ? (
                                                            <span className="text-sm text-emerald-600">
                                                                <i className="fas fa-check-circle mr-1" aria-hidden="true" />
                                                                Confirmed
                                                            </span>
                                                        ) : (
                                                            <button
                                                                type="button"
                                                                onClick={() => confirmPayment(payment)}
                                                                className="px-3 py-1.5 text-xs font-medium bg-amber-100 text-amber-700 rounded-full hover:bg-amber-200 transition-all"
                                                            >
                                                                Confirm
                                                            </button>
                                                        )}
                                                    </td>
                                                )}
                                            </tr>
                                        );
                                    })
                                )}
                            </tbody>
                        </table>
                    </div>

                    {payments.meta.last_page > 1 && (
                        <nav className="p-4 flex items-center justify-between border-t border-blue-50" aria-label="Pagination">
                            <p className="text-sm text-slate-500">
                                Page {payments.meta.current_page} of {payments.meta.last_page} ({payments.meta.total} payments)
                            </p>
                            <div className="flex gap-2">
                                {payments.links.prev && (
                                    <Link href={payments.links.prev} preserveScroll className="px-3 py-1.5 text-sm rounded-lg border border-slate-200 hover:bg-slate-50">Previous</Link>
                                )}
                                {payments.links.next && (
                                    <Link href={payments.links.next} preserveScroll className="px-3 py-1.5 text-sm rounded-lg border border-slate-200 hover:bg-slate-50">Next</Link>
                                )}
                            </div>
                        </nav>
                    )}
                </div>

                {showModal && (
                    <RecordPaymentModal
                        renters={isRenter ? [] : renters}
                        onClose={() => setShowModal(false)}
                    />
                )}
            </AppLayout>
        </>
    );
}