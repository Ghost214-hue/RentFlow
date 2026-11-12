import { Head, Link, router, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';
import { formatMoney } from '@/lib/money';
import { statusTone } from '@/lib/status';
import type { Bill, Paginated, PropertyOption } from '@/types';

interface Props {
    bills: Paginated<Bill>;
    months: string[];
    properties: PropertyOption[];
    filters: { month: string; property_id: number | null; status: string | null };
    canGenerate: boolean;
    flash?: { success?: string | null };
}

/**
 * Bills.
 *
 * Ported from frontend/pages/bills.php. Every figure shown here comes from
 * BillSnapshot on the server; the browser formats money strings and never
 * totals them.
 */
export default function BillsIndex({
    bills,
    months,
    properties,
    filters,
    canGenerate,
    flash,
}: Props) {
    const generate = useForm({ month: filters.month, property_id: '' });

    function applyFilter(month: string, propertyId: string, status: string) {
        router.get(
            '/bills',
            {
                month,
                property_id: propertyId || undefined,
                status: status || undefined,
            },
            { preserveState: true },
        );
    }

    return (
        <>
            <Head title="Bills" />

            <AppLayout
                title="Bills"
                actions={
                    <>
                        <div>
                            <h1 className="text-2xl font-bold text-slate-900">Bills</h1>
                            <p className="text-slate-500 mt-1">Monthly invoices</p>
                        </div>

                        {canGenerate && (
                            <form
                                onSubmit={(e) => {
                                    e.preventDefault();
                                    generate.post('/bills/generate', {
                                        preserveScroll: true,
                                    });
                                }}
                            >
                                <button
                                    type="submit"
                                    disabled={generate.processing}
                                    className="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all inline-flex items-center gap-2 disabled:opacity-60"
                                >
                                    <i className="fas fa-file-invoice-dollar" aria-hidden="true" />
                                    {generate.processing ? 'Generating...' : 'Generate Bills'}
                                </button>
                            </form>
                        )}
                    </>
                }
            >
                {flash?.success && (
                    <div role="status" className="mb-4 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-700 text-sm border border-emerald-100">
                        {flash.success}
                    </div>
                )}

                <div className="mb-4 flex flex-col sm:flex-row gap-3">
                    <select
                        value={filters.month}
                        onChange={(e) =>
                            applyFilter(e.target.value, filters.property_id === null ? '' : String(filters.property_id), filters.status ?? '')
                        }
                        aria-label="Filter by month"
                        className="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm"
                    >
                        {months.map((m) => (
                            <option key={m} value={m}>
                                {m}
                            </option>
                        ))}
                    </select>

                    <select
                        value={filters.property_id ?? ''}
                        onChange={(e) => applyFilter(filters.month, e.target.value, filters.status ?? '')}
                        aria-label="Filter by property"
                        className="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm"
                    >
                        <option value="">All properties</option>
                        {properties.map((p) => (
                            <option key={p.id} value={p.id}>
                                {p.name}
                            </option>
                        ))}
                    </select>

                    <select
                        value={filters.status ?? ''}
                        onChange={(e) => applyFilter(filters.month, filters.property_id === null ? '' : String(filters.property_id), e.target.value)}
                        aria-label="Filter by status"
                        className="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm"
                    >
                        <option value="">All statuses</option>
                        <option value="pending">Pending</option>
                        <option value="partial">Partial</option>
                        <option value="paid">Paid</option>
                        <option value="overdue">Overdue</option>
                    </select>
                </div>

                <div className="bg-white rounded-2xl shadow-sm border border-blue-100/50 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead>
                                <tr className="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50 bg-blue-50/50">
                                    <th className="px-6 py-4">Renter</th>
                                    <th className="px-6 py-4">Property</th>
                                    <th className="px-6 py-4">Unit</th>
                                    <th className="px-6 py-4">Amount</th>
                                    <th className="px-6 py-4">Paid</th>
                                    <th className="px-6 py-4">Balance</th>
                                    <th className="px-6 py-4">Due</th>
                                    <th className="px-6 py-4">Status</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-blue-50">
                                {bills.data.length === 0 ? (
                                    <tr>
                                        <td colSpan={8} className="px-6 py-12 text-center text-slate-400">
                                            No bills for {filters.month}.
                                        </td>
                                    </tr>
                                ) : (
                                    bills.data.map((bill) => {
                                        const tone = statusTone(bill.status);

                                        return (
                                            <tr key={bill.id} className="hover:bg-blue-50/30 transition-colors">
                                                <td className="px-6 py-4 text-sm text-slate-700 font-medium">
                                                    {bill.tenant_name ?? '-'}
                                                </td>
                                                <td className="px-6 py-4 text-sm text-slate-600">
                                                    {bill.property_name ?? 'N/A'}
                                                </td>
                                                <td className="px-6 py-4 text-sm text-slate-600">
                                                    {bill.house_unit ?? '-'}
                                                </td>
                                                <td className="px-6 py-4 text-sm font-medium text-slate-900">
                                                    {formatMoney(bill.amount)}
                                                </td>
                                                <td className="px-6 py-4 text-sm text-emerald-600">
                                                    {formatMoney(bill.paid)}
                                                </td>
                                                <td className="px-6 py-4 text-sm font-medium text-red-600">
                                                    {formatMoney(bill.balance)}
                                                </td>
                                                <td className="px-6 py-4 text-sm text-slate-500">
                                                    {bill.due_date ?? '-'}
                                                </td>
                                                <td className="px-6 py-4">
                                                    <span className={`px-2 py-1 rounded-full text-xs font-medium ${tone.className}`}>
                                                        {tone.label}
                                                    </span>
                                                </td>
                                            </tr>
                                        );
                                    })
                                )}
                            </tbody>
                        </table>
                    </div>

                    {bills.meta.last_page > 1 && (
                        <nav className="p-4 flex items-center justify-between border-t border-blue-50" aria-label="Pagination">
                            <p className="text-sm text-slate-500">
                                Page {bills.meta.current_page} of {bills.meta.last_page} ({bills.meta.total} bills)
                            </p>
                            <div className="flex gap-2">
                                {bills.links.prev && (
                                    <Link href={bills.links.prev} preserveScroll className="px-3 py-1.5 text-sm rounded-lg border border-slate-200 hover:bg-slate-50">Previous</Link>
                                )}
                                {bills.links.next && (
                                    <Link href={bills.links.next} preserveScroll className="px-3 py-1.5 text-sm rounded-lg border border-slate-200 hover:bg-slate-50">Next</Link>
                                )}
                            </div>
                        </nav>
                    )}
                </div>
            </AppLayout>
        </>
    );
}