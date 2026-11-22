import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/layouts/AppLayout';
import { formatMoney } from '@/lib/money';
import type { Expense, Paginated } from '@/types';

interface Props {
    expenses: Paginated<Expense>;
    filters: { category: string | null; from: string | null; to: string | null };
    flash?: { success?: string | null; error?: string | null };
}

const CATEGORIES = [
    { value: 'security', label: 'Security Guard' },
    { value: 'caretaker', label: 'Caretaker Payment' },
    { value: 'maintenance', label: 'Maintenance' },
    { value: 'utilities', label: 'Utilities' },
    { value: 'other', label: 'Other' },
] as const;

export default function ExpensesIndex({ expenses, filters, flash }: Props) {
    const [showModal, setShowModal] = useState(false);
    const [editing, setEditing] = useState<Expense | null>(null);

    function applyFilter(category: string, from: string, to: string) {
        router.get('/expenses', { category: category || undefined, from: from || undefined, to: to || undefined }, { preserveState: true });
    }

    function removeExpense(expense: Expense) {
        if (!window.confirm(`Remove the ${expense.category} expense of ${formatMoney(expense.amount)}?`)) return;
        router.delete(`/expenses/${expense.id}`, { preserveScroll: true });
    }

    function categoryTone(category: string) {
        const t: Record<string, string> = {
            security: 'bg-rose-50 text-rose-700', caretaker: 'bg-amber-50 text-amber-700',
            maintenance: 'bg-blue-50 text-blue-700', utilities: 'bg-violet-50 text-violet-700',
            other: 'bg-slate-50 text-slate-600',
        };
        return t[category] ?? t.other;
    }

    return (
        <>
            <Head title="Expenses" />
            <AppLayout title="Expenses" actions={
                <>
                    <div>
                        <h1 className="text-2xl font-bold text-slate-900">Expenses</h1>
                        <p className="text-slate-500 mt-1">Money going out — security, caretaker, maintenance, utilities</p>
                    </div>
                    <button type="button" onClick={() => { setEditing(null); setShowModal(true); }} className="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all inline-flex items-center gap-2">
                        <i className="fas fa-plus" aria-hidden="true" /> Add Expense
                    </button>
                </>
            }>
                {flash?.success && (
                    <div role="status" className="mb-4 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-700 text-sm border border-emerald-100">{flash.success}</div>
                )}
                <div className="mb-4 flex flex-col sm:flex-row gap-3">
                    <select value={filters.category ?? ''} onChange={(e) => applyFilter(e.target.value, filters.from ?? '', filters.to ?? '')} aria-label="Filter by category" className="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm">
                        <option value="">All categories</option>{CATEGORIES.map((c) => (<option key={c.value} value={c.value}>{c.label}</option>))}
                    </select>
                    <input type="date" value={filters.from ?? ''} onChange={(e) => applyFilter(filters.category ?? '', e.target.value, filters.to ?? '')} aria-label="From date" className="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm" />
                    <input type="date" value={filters.to ?? ''} onChange={(e) => applyFilter(filters.category ?? '', filters.from ?? '', e.target.value)} aria-label="To date" className="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm" />
                </div>
                <div className="bg-white rounded-2xl shadow-sm border border-blue-100/50 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead>
                                <tr className="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50 bg-blue-50/50">
                                    <th className="px-6 py-4">Date</th>
                                    <th className="px-6 py-4">Category</th>
                                    <th className="px-6 py-4">Amount</th>
                                    <th className="px-6 py-4">Paid To</th>
                                    <th className="px-6 py-4">Description</th>
                                    <th className="px-6 py-4">Receipt</th>
                                    <th className="px-6 py-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-blue-50">
                                {expenses.data.length === 0 ? (
                                    <tr><td colSpan={7} className="px-6 py-12 text-center text-slate-400">No expenses recorded yet.</td></tr>
                                ) : (
                                    expenses.data.map((expense) => (
                                        <tr key={expense.id} className="hover:bg-blue-50/30 transition-colors">
                                            <td className="px-6 py-4 text-sm text-slate-700">{expense.date ?? '-'}</td>
                                            <td className="px-6 py-4 text-sm"><span className={`px-2 py-1 rounded-full text-xs font-medium ${categoryTone(expense.category)}`}>{expense.category.charAt(0).toUpperCase() + expense.category.slice(1)}</span></td>
                                            <td className="px-6 py-4 text-sm font-medium text-rose-600">-{formatMoney(expense.amount)}</td>
                                            <td className="px-6 py-4 text-sm text-slate-600">{expense.paid_to ?? '-'}</td>
                                            <td className="px-6 py-4 text-sm text-slate-600">{expense.description ?? '-'}</td>
                                            <td className="px-6 py-4 text-sm text-slate-500">{expense.receipt ?? '-'}</td>
                                            <td className="px-6 py-4"><div className="flex items-center gap-1">
                                                <button type="button" onClick={() => { setEditing(expense); setShowModal(true); }} className="p-1.5 text-slate-400 hover:text-blue-600 transition-colors" aria-label={`Edit ${expense.category} expense`}><i className="fas fa-pen" aria-hidden="true" /></button>
                                                <button type="button" onClick={() => removeExpense(expense)} className="p-1.5 text-slate-400 hover:text-red-600 transition-colors" aria-label={`Remove ${expense.category} expense`}><i className="fas fa-trash" aria-hidden="true" /></button>
                                            </div></td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                    {expenses.meta.last_page > 1 && (
                        <nav className="p-4 flex items-center justify-between border-t border-blue-50" aria-label="Pagination">
                            <p className="text-sm text-slate-500">Page {expenses.meta.current_page} of {expenses.meta.last_page} ({expenses.meta.total} expenses)</p>
                            <div className="flex gap-2">
                                {expenses.links.prev && (
                                    <a href={expenses.links.prev} onClick={(e) => { e.preventDefault(); router.get(expenses.links.prev!, { preserveScroll: true }); }} className="px-3 py-1.5 text-sm rounded-lg border border-slate-200 hover:bg-slate-50">Previous</a>
                                )}
                                {expenses.links.next && (
                                    <a href={expenses.links.next} onClick={(e) => { e.preventDefault(); router.get(expenses.links.next!, { preserveScroll: true }); }} className="px-3 py-1.5 text-sm rounded-lg border border-slate-200 hover:bg-slate-50">Next</a>
                                )}
                            </div>
                        </nav>
                    )}
                </div>
                {showModal && <ExpenseModal expense={editing} onClose={() => { setShowModal(false); setEditing(null); }} />}
            </AppLayout>
        </>
    );
}

function ExpenseModal({ expense, onClose }: { expense: Expense | null; onClose: () => void }) {
    const [isEditing, setIsEditing] = useState(!!expense);
    const [formData, setFormData] = useState({
        amount: '', category: expense?.category ?? 'other', customCategory: expense?.category === 'other' ? expense?.category : '',
        description: expense?.description ?? '', date: expense?.date ?? '',
        paid_to: expense?.paid_to ?? '', receipt: expense?.receipt ?? '',
    });

    function submit(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();
        if (!parseFloat(formData.amount) || parseFloat(formData.amount) <= 0) return;
        const finalCategory = formData.category === 'other' && formData.customCategory
            ? formData.customCategory
            : formData.category;
        const url = isEditing && expense ? `/expenses/${expense.id}` : '/expenses';
        const method = isEditing && expense ? 'put' : 'post';
        router[method](url, { amount: formData.amount, category: finalCategory, description: formData.description, date: formData.date, paid_to: formData.paid_to, receipt: formData.receipt }, { preserveScroll: true, onSuccess: () => onClose(), onError: () => {} });
    }

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" onClick={(e) => { if (e.target === e.currentTarget) onClose(); }} role="dialog" aria-modal="true" aria-labelledby="expense-modal-title">
            <div className="bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto p-6" onClick={(e) => e.stopPropagation()}>
                <div className="flex items-center justify-between mb-4">
                    <h3 id="expense-modal-title" className="text-lg font-bold text-slate-900">{isEditing ? 'Edit Expense' : 'Add Expense'}</h3>
                    <button type="button" onClick={onClose} className="text-slate-400 hover:text-slate-600" aria-label="Close"><i className="fas fa-times text-xl" aria-hidden="true" /></button>
                </div>
                <form onSubmit={submit} className="space-y-4" noValidate>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-slate-700 mb-1">Amount (KES)</label>
                            <input type="text" inputMode="decimal" value={formData.amount} onChange={(e) => setFormData({ ...formData, amount: e.target.value })} className="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white" placeholder="3500.00" required />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-slate-700 mb-1">Category</label>
                            {formData.category === 'other' ? (
                                <>
                                    <input type="text" value={formData.customCategory} onChange={(e) => setFormData({ ...formData, customCategory: e.target.value })} className="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white" placeholder="Specify category (e.g. Gardener, Cleaner)" />
                                    <p className="text-xs text-slate-500 mt-1">Enter a custom category name.</p>
                                </>
                            ) : (
                                <select value={formData.category} onChange={(e) => { setFormData({ ...formData, category: e.target.value, customCategory: e.target.value === 'other' ? '' : formData.customCategory }); }} className="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white">{CATEGORIES.map((c) => (<option key={c.value} value={c.value}>{c.label}</option>))}</select>
                            )}
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-slate-700 mb-1">Date</label>
                            <input type="date" value={formData.date} onChange={(e) => setFormData({ ...formData, date: e.target.value })} className="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white" required />
                        </div>
                        <div className="sm:col-span-2">
                            <label className="block text-sm font-medium text-slate-700 mb-1">Paid To</label>
                            <input type="text" value={formData.paid_to} onChange={(e) => setFormData({ ...formData, paid_to: e.target.value })} className="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white" placeholder="e.g. John the security guard" />
                        </div>
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-slate-700 mb-1">Description (optional)</label>
                        <input type="text" value={formData.description} onChange={(e) => setFormData({ ...formData, description: e.target.value })} className="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white" placeholder="e.g. October gatekeeping wages" />
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-slate-700 mb-1">Receipt / Reference (optional)</label>
                        <input type="text" value={formData.receipt} onChange={(e) => setFormData({ ...formData, receipt: e.target.value })} className="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white" placeholder="e.g. M-Pesa confirmation" />
                    </div>
                    <div className="flex justify-end gap-3 pt-2">
                        <button type="button" onClick={onClose} className="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-medium hover:bg-slate-50">Cancel</button>
                        <button type="submit" className="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all">{isEditing ? 'Save Changes' : 'Record Expense'}</button>
                    </div>
                </form>
            </div>
        </div>
    );
}