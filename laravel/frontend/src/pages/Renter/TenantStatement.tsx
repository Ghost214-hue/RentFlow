import { formatMoney } from '@/lib/money';

export interface StatementMonth {
    bill_id: number;
    month: string;
    due_date: string | null;
    rent: string;
    water: string;
    electricity: string;
    other: string;
    charges: string;
    paid: string;
    unpaid: string;
    brought_forward: string;
    balance: string;
    received_in_month: string;
    status: string;
}

export interface StatementPayment {
    id: number;
    date: string | null;
    receipt: string | null;
    method: string | null;
    type: string | null;
    status: string;
    amount: string;
    applied_to: Array<{ month: string; amount: string }>;
}

export interface Statement {
    summary: { billed: string; paid: string; outstanding: string; overdue: string; received: string; credit: string };
    months: StatementMonth[];
    payments: StatementPayment[];
}

const TONE: Record<string, string> = {
    paid: 'bg-emerald-100 text-emerald-700',
    partial: 'bg-amber-100 text-amber-700',
    pending: 'bg-slate-100 text-slate-600',
    overdue: 'bg-red-100 text-red-700',
};

const isPositive = (v: string) => Number(v) > 0; // comparison only, never arithmetic

function Card({ label, value, tone }: { label: string; value: string; tone: string }) {
    return (
        <div className="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-4">
            <p className="text-xs font-semibold text-slate-500 uppercase tracking-wider">{label}</p>
            <p className={`text-lg font-bold mt-1 ${tone}`}>{formatMoney(value)}</p>
        </div>
    );
}

/** Tenant revenue portfolio: bills, payments and outstanding balance, per month. All figures are server-computed. */
export default function TenantStatementView({ statement }: { statement: Statement }) {
    const { summary, months, payments } = statement;

    return (
        <div className="space-y-6">
            <div className="grid grid-cols-2 md:grid-cols-5 gap-3">
                <Card label="Total billed" value={summary.billed} tone="text-slate-900" />
                <Card label="Total paid" value={summary.paid} tone="text-emerald-600" />
                <Card label="Outstanding" value={summary.outstanding} tone={isPositive(summary.outstanding) ? 'text-red-600' : 'text-slate-900'} />
                <Card label="Overdue" value={summary.overdue} tone={isPositive(summary.overdue) ? 'text-red-600' : 'text-slate-900'} />
                <Card label="Credit" value={summary.credit} tone="text-blue-600" />
            </div>

            <div className="bg-white rounded-2xl shadow-sm border border-blue-100/50 overflow-hidden">
                <div className="p-5 border-b border-blue-50">
                    <h2 className="font-semibold text-slate-900">Monthly statement</h2>
                    <p className="text-xs text-slate-500 mt-1">Balance = arrears brought forward + that month&apos;s unpaid charges.</p>
                </div>
                {months.length === 0 ? (
                    <p className="px-5 py-10 text-center text-slate-400 text-sm">No bills raised.</p>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50 bg-blue-50/50">
                                    {['Month', 'Rent', 'Utilities', 'Billed', 'Paid', 'Unpaid', 'B/F', 'Balance', 'Received', 'Status'].map((h) => (
                                        <th key={h} className="px-4 py-3 whitespace-nowrap">{h}</th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-blue-50">
                                {months.map((m) => (
                                    <tr key={m.bill_id} className="hover:bg-blue-50/30">
                                        <td className="px-4 py-3 font-medium text-slate-900 whitespace-nowrap">{m.month}</td>
                                        <td className="px-4 py-3">{formatMoney(m.rent)}</td>
                                        <td className="px-4 py-3">
                                            {formatMoney(m.water)}
                                            {isPositive(m.electricity) && <span className="block text-xs text-slate-400">+ elec {formatMoney(m.electricity)}</span>}
                                        </td>
                                        <td className="px-4 py-3">{formatMoney(m.charges)}</td>
                                        <td className="px-4 py-3 text-emerald-600">{formatMoney(m.paid)}</td>
                                        <td className={`px-4 py-3 font-medium ${isPositive(m.unpaid) ? 'text-red-600' : 'text-slate-500'}`}>{formatMoney(m.unpaid)}</td>
                                        <td className="px-4 py-3 text-slate-500">{formatMoney(m.brought_forward)}</td>
                                        <td className={`px-4 py-3 font-semibold ${isPositive(m.balance) ? 'text-red-600' : 'text-slate-900'}`}>{formatMoney(m.balance)}</td>
                                        <td className="px-4 py-3 text-slate-500">{formatMoney(m.received_in_month)}</td>
                                        <td className="px-4 py-3">
                                            <span className={`px-2 py-1 rounded-full text-xs font-medium ${TONE[m.status] ?? TONE.pending}`}>{m.status}</span>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>

            <div className="bg-white rounded-2xl shadow-sm border border-blue-100/50 overflow-hidden">
                <div className="p-5 border-b border-blue-50">
                    <h2 className="font-semibold text-slate-900">Payments received</h2>
                </div>
                {payments.length === 0 ? (
                    <p className="px-5 py-10 text-center text-slate-400 text-sm">No payments recorded.</p>
                ) : (
                    <ul className="divide-y divide-blue-50">
                        {payments.map((p) => (
                            <li key={p.id} className="px-5 py-4 flex items-start justify-between gap-4">
                                <div>
                                    <p className="text-sm font-medium text-slate-900">
                                        {p.method ?? 'Payment'} {p.receipt && <span className="text-slate-400 font-normal">· {p.receipt}</span>}
                                    </p>
                                    <p className="text-xs text-slate-500">{p.date ?? '-'} · <span className="capitalize">{p.status}</span></p>
                                    {p.applied_to.length > 0 && (
                                        <p className="text-xs text-slate-500 mt-1">
                                            Applied to: {p.applied_to.map((a) => `${a.month} (${formatMoney(a.amount)})`).join(', ')}
                                        </p>
                                    )}
                                </div>
                                <p className="text-sm font-semibold text-emerald-600 whitespace-nowrap">{formatMoney(p.amount)}</p>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </div>
    );
}
