/**
 * Bill/payment status presentation.
 *
 * The status vocabulary comes from the server (BillSnapshot), so this file
 * only maps it to a colour and a label. It never derives status itself.
 */

/** 'pending' | 'partial' | 'paid' | 'overdue' */
export type BillStatus = string;

interface Tone {
    label: string;
    className: string;
}

/** Class strings match the legacy pills (bg-*-100 text-*-700). */
const TONES: Record<string, Tone> = {
    paid: { label: 'paid', className: 'bg-emerald-100 text-emerald-700' },
    partial: { label: 'partial', className: 'bg-amber-100 text-amber-700' },
    pending: { label: 'pending', className: 'bg-slate-100 text-slate-600' },
    overdue: { label: 'overdue', className: 'bg-red-100 text-red-700' },
    completed: { label: 'completed', className: 'bg-emerald-100 text-emerald-700' },
    confirmed: { label: 'confirmed', className: 'bg-emerald-100 text-emerald-700' },
    failed: { label: 'failed', className: 'bg-red-100 text-red-700' },
};

const FALLBACK: Tone = { label: 'unknown', className: 'bg-slate-100 text-slate-600' };

export function statusTone(status: string): Tone {
    return TONES[status.toLowerCase()] ?? { ...FALLBACK, label: status };
}

/** True when a payment still has unallocated money (i.e. renter credit). */
export function hasCredit(payment: { allocated: string; amount: string }): boolean {
    return Number(payment.amount) > Number(payment.allocated);
}