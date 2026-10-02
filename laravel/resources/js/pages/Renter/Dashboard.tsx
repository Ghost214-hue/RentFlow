import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';
import { formatMoney } from '@/lib/money';
import type { Bill, RenterDashboardProps } from '@/types';

/**
 * The renter's own dashboard.
 *
 * Ported from frontend/pages/tenant-dashboard.php. Outstanding figures come
 * from the server, which computes them from bills and allocations rather than
 * from the cached tenants.balance column.
 */
export default function RenterDashboard(props: RenterDashboardProps) {
    const { renter, summary, recentBills, recentPayments } = props;

    const cards = [
        {
            label: 'Outstanding',
            value: formatMoney(summary.outstanding),
            hint: `${summary.unpaid_bill_count} unpaid bill${summary.unpaid_bill_count === 1 ? '' : 's'}`,
            tone: summary.outstanding === '0.00' ? 'emerald' : 'rose',
        },
        {
            label: 'Credit',
            value: formatMoney(summary.credit),
            hint: 'Overpayment carried forward',
            tone: 'blue',
        },
        {
            label: 'Open Complaints',
            value: String(summary.open_complaints),
            hint: 'Awaiting a response',
            tone: 'amber',
        },
        {
            label: 'Maintenance',
            value: String(summary.open_maintenance),
            hint: 'Open requests',
            tone: 'violet',
        },
    ] as const;

    const toneClass = {
        emerald: 'bg-emerald-50 text-emerald-700',
        rose: 'bg-rose-50 text-rose-700',
        blue: 'bg-blue-50 text-blue-700',
        amber: 'bg-amber-50 text-amber-700',
        violet: 'bg-violet-50 text-violet-700',
    } as const;

    return (
        <>
            <Head title="My Dashboard" />

            <AppLayout
                title="My Dashboard"
                actions={
                    <div>
                        <h1 className="text-2xl font-bold text-slate-900">Welcome, {renter.name}</h1>
                        <p className="text-slate-500 mt-1">
                            {renter.house_unit ? `Unit ${renter.house_unit}` : 'Your tenancy'}
                            {renter.property_name ? ` · ${renter.property_name}` : ''}
                        </p>
                    </div>
                }
            >
                <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
                    {cards.map((card) => (
                        <div key={card.label} className="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                            <div className={`inline-flex px-2.5 py-1 rounded-full text-xs font-medium ${toneClass[card.tone]}`}>
                                {card.label}
                            </div>
                            <p className="mt-3 text-2xl font-bold text-slate-900">{card.value}</p>
                            <p className="mt-1 text-xs text-slate-500">{card.hint}</p>
                        </div>
                    ))}
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <section className="bg-white rounded-2xl shadow-sm border border-blue-100/50 overflow-hidden">
                        <div className="p-5 border-b border-blue-50 flex items-center justify-between">
                            <h2 className="font-semibold text-slate-900">Recent Bills</h2>
                            <Link href="/bills" className="text-sm text-blue-600 hover:text-blue-700">View all</Link>
                        </div>

                        {recentBills.length === 0 ? (
                            <p className="px-5 py-10 text-center text-slate-400 text-sm">No bills yet.</p>
                        ) : (
                            <ul className="divide-y divide-blue-50">
                                {recentBills.map((bill: Bill) => (
                                    <li key={bill.id} className="px-5 py-4 flex items-center justify-between">
                                        <div>
                                            <p className="text-sm font-medium text-slate-900">{bill.month}</p>
                                            <p className="text-xs text-slate-500 capitalize">{bill.status}</p>
                                        </div>
                                        <p className="text-sm font-semibold text-slate-900">{formatMoney(bill.amount)}</p>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </section>

                    <section className="bg-white rounded-2xl shadow-sm border border-blue-100/50 overflow-hidden">
                        <div className="p-5 border-b border-blue-50 flex items-center justify-between">
                            <h2 className="font-semibold text-slate-900">Recent Payments</h2>
                            <Link href="/payments" className="text-sm text-blue-600 hover:text-blue-700">View all</Link>
                        </div>

                        {recentPayments.length === 0 ? (
                            <p className="px-5 py-10 text-center text-slate-400 text-sm">No payments yet.</p>
                        ) : (
                            <ul className="divide-y divide-blue-50">
                                {recentPayments.map((payment) => (
                                    <li key={payment.id} className="px-5 py-4 flex items-center justify-between">
                                        <div>
                                            <p className="text-sm font-medium text-slate-900">{payment.method}</p>
                                            <p className="text-xs text-slate-500">
                                                {payment.paid_at ?? 'No date'} · {payment.status}
                                            </p>
                                        </div>
                                        <p className="text-sm font-semibold text-slate-900">{formatMoney(payment.amount)}</p>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </section>
                </div>

                <div className="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    {[
                        { href: '/complaints', label: 'Raise a Complaint', icon: 'fa-comment-dots' },
                        { href: '/maintenance', label: 'Request Maintenance', icon: 'fa-screwdriver-wrench' },
                        { href: '/renter/profile', label: 'My Profile', icon: 'fa-user' },
                    ].map((link) => (
                        <Link
                            key={link.href}
                            href={link.href}
                            className="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5 hover:shadow-md transition-shadow flex items-center gap-3"
                        >
                            <i className={`fas ${link.icon} text-blue-600`} aria-hidden="true" />
                            <span className="font-medium text-slate-900">{link.label}</span>
                        </Link>
                    ))}
                </div>
            </AppLayout>
        </>
    );
}