import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/layouts/AppLayout';

interface OwnerItem {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    status: 'active' | 'suspended' | string;
    created_at: string;
}

interface Props {
    owners: OwnerItem[];
    flash?: { success?: string | null; warning?: string | null; error?: string | null };
}

export default function OwnersIndex({ owners, flash }: Props) {
    const [statusFilter, setStatusFilter] = useState('');
    const [search, setSearch] = useState('');

    const filtered = owners.filter((o) => {
        const matchesStatus = statusFilter === '' || o.status === statusFilter;
        const matchesSearch =
            search === '' ||
            o.name.toLowerCase().includes(search.toLowerCase()) ||
            o.email.toLowerCase().includes(search.toLowerCase());
        return matchesStatus && matchesSearch;
    });

    function handleSuspend(id: number, name: string) {
        if (confirm(`Are you sure you want to suspend owner ${name}? Their access will be revoked immediately.`)) {
            router.post(`/admin/owners/${id}/suspend`);
        }
    }

    function handleActivate(id: number, name: string) {
        if (confirm(`Activate owner ${name}?`)) {
            router.post(`/admin/owners/${id}/activate`);
        }
    }

    function handleReset(id: number, name: string) {
        if (confirm(`Send password reset link to ${name}?`)) {
            router.post(`/admin/owners/${id}/reset`);
        }
    }

    return (
        <AppLayout
            title="Owner Management"
            subtitle="Administer registered landlords, access status, and portfolio accounts"
            actions={
                <div className="flex flex-wrap items-center gap-3">
                    <input
                        type="search"
                        placeholder="Search owners by name or email…"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="rounded-xl border border-border bg-surface px-4 py-2 text-sm text-text placeholder-text-subtle focus:border-primary focus:outline-none"
                    />
                    <select
                        value={statusFilter}
                        onChange={(e) => setStatusFilter(e.target.value)}
                        className="rounded-xl border border-border bg-surface px-4 py-2 text-sm text-text focus:border-primary focus:outline-none"
                    >
                        <option value="">All Statuses ({owners.length})</option>
                        <option value="active">Active Only</option>
                        <option value="suspended">Suspended Only</option>
                    </select>
                </div>
            }
        >
            <Head title="Owner Management - Admin" />

            {flash?.success && (
                <div className="mb-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-600 dark:text-emerald-400">
                    {flash.success}
                </div>
            )}
            {flash?.warning && (
                <div className="mb-4 rounded-xl border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-600 dark:text-amber-400">
                    {flash.warning}
                </div>
            )}

            <div className="rounded-2xl border border-border bg-surface shadow-card overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="w-full text-left text-sm text-text">
                        <thead className="border-b border-border bg-surface-raised/50 text-xs font-semibold uppercase text-text-subtle">
                            <tr>
                                <th className="px-6 py-4">Landlord</th>
                                <th className="px-6 py-4">Contact</th>
                                <th className="px-6 py-4">Status</th>
                                <th className="px-6 py-4">Registered</th>
                                <th className="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-border">
                            {filtered.length === 0 ? (
                                <tr>
                                    <td colSpan={5} className="px-6 py-12 text-center text-text-subtle">
                                        No owners found matching the criteria.
                                    </td>
                                </tr>
                            ) : (
                                filtered.map((owner) => (
                                    <tr key={owner.id} className="hover:bg-page/40 transition-colors">
                                        <td className="px-6 py-4">
                                            <div className="font-semibold text-text">{owner.name}</div>
                                            <div className="text-xs text-text-subtle">ID #{owner.id}</div>
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="text-text">{owner.email}</div>
                                            <div className="text-xs text-text-subtle">{owner.phone || 'No phone'}</div>
                                        </td>
                                        <td className="px-6 py-4">
                                            <span
                                                className={`inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium ${
                                                    owner.status === 'active'
                                                        ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20'
                                                        : 'bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20'
                                                }`}
                                            >
                                                <span
                                                    className={`h-1.5 w-1.5 rounded-full ${
                                                        owner.status === 'active' ? 'bg-emerald-500' : 'bg-red-500'
                                                    }`}
                                                />
                                                {owner.status.toUpperCase()}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 text-xs text-text-subtle">
                                            {new Date(owner.created_at).toLocaleDateString()}
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            <div className="flex items-center justify-end gap-1.5">
                                                <Link
                                                    href={`/admin/owners/${owner.id}`}
                                                    className="rounded-lg p-1.5 text-text-subtle hover:text-primary hover:bg-page transition-colors"
                                                    title="View details"
                                                >
                                                    <i className="fas fa-eye text-sm" aria-hidden="true" />
                                                </Link>

                                                {owner.status === 'active' ? (
                                                    <button
                                                        type="button"
                                                        onClick={() => handleSuspend(owner.id, owner.name)}
                                                        className="rounded-lg p-1.5 text-text-subtle hover:text-amber-600 hover:bg-page transition-colors"
                                                        title="Suspend account"
                                                    >
                                                        <i className="fas fa-pause text-sm" aria-hidden="true" />
                                                    </button>
                                                ) : (
                                                    <button
                                                        type="button"
                                                        onClick={() => handleActivate(owner.id, owner.name)}
                                                        className="rounded-lg p-1.5 text-text-subtle hover:text-emerald-600 hover:bg-page transition-colors"
                                                        title="Activate account"
                                                    >
                                                        <i className="fas fa-play text-sm" aria-hidden="true" />
                                                    </button>
                                                )}

                                                <button
                                                    type="button"
                                                    onClick={() => handleReset(owner.id, owner.name)}
                                                    className="rounded-lg p-1.5 text-text-subtle hover:text-blue-600 hover:bg-page transition-colors"
                                                    title="Send password reset"
                                                >
                                                    <i className="fas fa-key text-sm" aria-hidden="true" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </AppLayout>
    );
}
