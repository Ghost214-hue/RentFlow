import { Head, useForm, router } from '@inertiajs/react';
import { type FormEvent } from 'react';
import AppLayout from '@/layouts/AppLayout';

interface Grant {
    id: number;
    owner_id: number;
    admin_id: number;
    scope: string;
    reason: string;
    expires_at: string | null;
    revoked_at: string | null;
    owner?: { id: number; name: string };
    admin?: { id: number; name: string };
}

interface OwnerOption {
    id: number;
    name: string;
}

interface AdminOption {
    id: number;
    name: string;
}

interface Props {
    grants: Grant[];
    owners: OwnerOption[];
    admins: AdminOption[];
    flash?: { success?: string | null; error?: string | null };
}

export default function SupportAccessIndex({ grants, owners, admins, flash }: Props) {
    const { data, setData, post, processing, reset, errors } = useForm({
        owner_id: '',
        admin_id: '',
        scope: 'names',
        reason: '',
        expires_at: '7',
    });

    function submitGrant(e: FormEvent) {
        e.preventDefault();
        post('/admin/support/access', {
            onSuccess: () => reset(),
        });
    }

    function handleRevoke(id: number) {
        if (confirm('Revoke this support access grant immediately?')) {
            router.delete(`/admin/support/access/${id}`);
        }
    }

    return (
        <AppLayout
            title="Support Access Grants"
            subtitle="Grant and audit temporary scoped support access to landlord accounts"
        >
            <Head title="Support Access - Admin" />

            {flash?.success && (
                <div className="mb-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-600 dark:text-emerald-400">
                    {flash.success}
                </div>
            )}
            {flash?.error && (
                <div className="mb-4 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-600 dark:text-red-400">
                    {flash.error}
                </div>
            )}

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                {/* Create Grant Form */}
                <div className="rounded-2xl border border-border bg-surface p-6 shadow-card h-fit">
                    <h3 className="text-base font-bold text-text mb-4 flex items-center gap-2">
                        <i className="fas fa-plus-circle text-primary text-sm" aria-hidden="true" />
                        Authorize Support Grant
                    </h3>

                    <form onSubmit={submitGrant} className="space-y-4 text-sm">
                        <div>
                            <label className="block text-xs font-semibold uppercase text-text-subtle mb-1.5">
                                Select Owner
                            </label>
                            <select
                                required
                                value={data.owner_id}
                                onChange={(e) => setData('owner_id', e.target.value)}
                                className="w-full rounded-xl border border-border bg-surface px-3 py-2 text-text focus:border-primary focus:outline-none"
                            >
                                <option value="">Choose owner…</option>
                                {owners.map((o) => (
                                    <option key={o.id} value={o.id}>
                                        {o.name} (ID #{o.id})
                                    </option>
                                ))}
                            </select>
                            {errors.owner_id && <p className="mt-1 text-xs text-danger">{errors.owner_id}</p>}
                        </div>

                        <div>
                            <label className="block text-xs font-semibold uppercase text-text-subtle mb-1.5">
                                Assigned Administrator
                            </label>
                            <select
                                required
                                value={data.admin_id}
                                onChange={(e) => setData('admin_id', e.target.value)}
                                className="w-full rounded-xl border border-border bg-surface px-3 py-2 text-text focus:border-primary focus:outline-none"
                            >
                                <option value="">Choose admin…</option>
                                {admins.map((a) => (
                                    <option key={a.id} value={a.id}>
                                        {a.name}
                                    </option>
                                ))}
                            </select>
                            {errors.admin_id && <p className="mt-1 text-xs text-danger">{errors.admin_id}</p>}
                        </div>

                        <div>
                            <label className="block text-xs font-semibold uppercase text-text-subtle mb-1.5">
                                Access Scope
                            </label>
                            <select
                                value={data.scope}
                                onChange={(e) => setData('scope', e.target.value)}
                                className="w-full rounded-xl border border-border bg-surface px-3 py-2 text-text focus:border-primary focus:outline-none"
                            >
                                <option value="names">Names (Basic tenant identities)</option>
                                <option value="phones">Phones (Contact details)</option>
                                <option value="id_numbers">ID Numbers (Legal identification)</option>
                                <option value="payment_lines">Payment Lines (Financial ledger)</option>
                            </select>
                        </div>

                        <div>
                            <label className="block text-xs font-semibold uppercase text-text-subtle mb-1.5">
                                Justification / Reason
                            </label>
                            <textarea
                                required
                                rows={2}
                                value={data.reason}
                                onChange={(e) => setData('reason', e.target.value)}
                                placeholder="e.g. Investigating receipt mismatch for tenant support ticket #104"
                                className="w-full rounded-xl border border-border bg-surface px-3 py-2 text-text focus:border-primary focus:outline-none"
                            />
                            {errors.reason && <p className="mt-1 text-xs text-danger">{errors.reason}</p>}
                        </div>

                        <div>
                            <label className="block text-xs font-semibold uppercase text-text-subtle mb-1.5">
                                Duration (Days)
                            </label>
                            <input
                                type="number"
                                min="1"
                                max="30"
                                value={data.expires_at}
                                onChange={(e) => setData('expires_at', e.target.value)}
                                className="w-full rounded-xl border border-border bg-surface px-3 py-2 text-text focus:border-primary focus:outline-none"
                            />
                        </div>

                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full rounded-xl bg-primary py-2.5 font-semibold text-white shadow-sm hover:bg-primary-hover disabled:opacity-60 transition"
                        >
                            {processing ? 'Authorizing…' : 'Grant Scoped Access'}
                        </button>
                    </form>
                </div>

                {/* Grants Table */}
                <div className="lg:col-span-2 rounded-2xl border border-border bg-surface shadow-card overflow-hidden">
                    <div className="p-5 border-b border-border bg-surface-raised/40">
                        <h3 className="text-base font-bold text-text">Active & Past Access Grants ({grants.length})</h3>
                        <p className="text-xs text-text-subtle mt-0.5">Audit log of all authorized support grants</p>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm text-text">
                            <thead className="border-b border-border bg-surface-raised/50 text-xs font-semibold uppercase text-text-subtle">
                                <tr>
                                    <th className="px-5 py-3">Owner</th>
                                    <th className="px-5 py-3">Admin</th>
                                    <th className="px-5 py-3">Scope</th>
                                    <th className="px-5 py-3">Reason</th>
                                    <th className="px-5 py-3">Status</th>
                                    <th className="px-5 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-border">
                                {grants.length === 0 ? (
                                    <tr>
                                        <td colSpan={6} className="px-6 py-12 text-center text-text-subtle">
                                            No active support access grants found.
                                        </td>
                                    </tr>
                                ) : (
                                    grants.map((grant) => {
                                        const isRevoked = !!grant.revoked_at;
                                        const isExpired = grant.expires_at && new Date(grant.expires_at) < new Date();
                                        const isActive = !isRevoked && !isExpired;

                                        return (
                                            <tr key={grant.id} className="hover:bg-page/40 transition-colors">
                                                <td className="px-5 py-3.5 font-medium text-text">
                                                    {grant.owner?.name ?? `Owner #${grant.owner_id}`}
                                                </td>
                                                <td className="px-5 py-3.5 text-text-subtle">
                                                    {grant.admin?.name ?? `Admin #${grant.admin_id}`}
                                                </td>
                                                <td className="px-5 py-3.5">
                                                    <span className="rounded bg-primary/10 text-primary px-2 py-0.5 text-xs font-medium uppercase">
                                                        {grant.scope}
                                                    </span>
                                                </td>
                                                <td className="px-5 py-3.5 text-xs text-text-subtle max-w-[180px] truncate" title={grant.reason}>
                                                    {grant.reason}
                                                </td>
                                                <td className="px-5 py-3.5">
                                                    {isActive ? (
                                                        <span className="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600">
                                                            <span className="h-1.5 w-1.5 rounded-full bg-emerald-500" />
                                                            Active
                                                        </span>
                                                    ) : isRevoked ? (
                                                        <span className="text-xs text-text-subtle">Revoked</span>
                                                    ) : (
                                                        <span className="text-xs text-amber-600">Expired</span>
                                                    )}
                                                </td>
                                                <td className="px-5 py-3.5 text-right">
                                                    {isActive && (
                                                        <button
                                                            type="button"
                                                            onClick={() => handleRevoke(grant.id)}
                                                            className="text-xs font-semibold text-danger hover:underline"
                                                        >
                                                            Revoke
                                                        </button>
                                                    )}
                                                </td>
                                            </tr>
                                        );
                                    })
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
