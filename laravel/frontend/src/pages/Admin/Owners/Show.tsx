import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';

interface PropertyItem {
    id: number;
    name: string;
    address: string;
    houses?: Array<{ id: number; unit: string; status: string }>;
}

interface OwnerDetail {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    status: string;
    created_at: string;
    properties?: PropertyItem[];
}

interface Props {
    owner: OwnerDetail;
    flash?: { success?: string | null; warning?: string | null };
}

export default function OwnerShow({ owner, flash }: Props) {
    function handleSuspend() {
        if (confirm(`Suspend ${owner.name}?`)) {
            router.post(`/admin/owners/${owner.id}/suspend`);
        }
    }

    function handleActivate() {
        if (confirm(`Activate ${owner.name}?`)) {
            router.post(`/admin/owners/${owner.id}/activate`);
        }
    }

    function handleReset() {
        if (confirm(`Send password reset email to ${owner.email}?`)) {
            router.post(`/admin/owners/${owner.id}/reset`);
        }
    }

    const properties = owner.properties || [];

    return (
        <AppLayout
            title={`Landlord: ${owner.name}`}
            subtitle="Detailed account status, properties, and direct controls"
            actions={
                <div className="flex items-center gap-3">
                    <Link
                        href="/admin/owners"
                        className="rounded-xl border border-border bg-surface px-4 py-2 text-sm text-text hover:bg-surface-raised transition-colors"
                    >
                        ← Back to Owners
                    </Link>
                    {owner.status === 'active' ? (
                        <button
                            type="button"
                            onClick={handleSuspend}
                            className="rounded-xl bg-amber-600/10 border border-amber-600/30 px-4 py-2 text-sm font-semibold text-amber-600 hover:bg-amber-600/20 transition-colors"
                        >
                            Suspend Access
                        </button>
                    ) : (
                        <button
                            type="button"
                            onClick={handleActivate}
                            className="rounded-xl bg-emerald-600/10 border border-emerald-600/30 px-4 py-2 text-sm font-semibold text-emerald-600 hover:bg-emerald-600/20 transition-colors"
                        >
                            Activate Account
                        </button>
                    )}
                    <button
                        type="button"
                        onClick={handleReset}
                        className="rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-hover transition-colors"
                    >
                        Send Reset Link
                    </button>
                </div>
            }
        >
            <Head title={`Owner: ${owner.name} - Admin`} />

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

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                {/* Account Details */}
                <div className="rounded-2xl border border-border bg-surface p-6 shadow-card">
                    <h3 className="text-base font-bold text-text mb-4">Account Profile</h3>
                    <dl className="space-y-3 text-sm">
                        <div>
                            <dt className="text-xs uppercase text-text-subtle font-semibold">Full Name</dt>
                            <dd className="text-text font-medium">{owner.name}</dd>
                        </div>
                        <div>
                            <dt className="text-xs uppercase text-text-subtle font-semibold">Email Address</dt>
                            <dd className="text-text font-medium">{owner.email}</dd>
                        </div>
                        <div>
                            <dt className="text-xs uppercase text-text-subtle font-semibold">Phone</dt>
                            <dd className="text-text font-medium">{owner.phone || 'Not recorded'}</dd>
                        </div>
                        <div>
                            <dt className="text-xs uppercase text-text-subtle font-semibold">Account Status</dt>
                            <dd className="mt-1">
                                <span
                                    className={`inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium ${
                                        owner.status === 'active'
                                            ? 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20'
                                            : 'bg-red-500/10 text-red-600 border border-red-500/20'
                                    }`}
                                >
                                    {owner.status.toUpperCase()}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt className="text-xs uppercase text-text-subtle font-semibold">Member Since</dt>
                            <dd className="text-text font-medium">{new Date(owner.created_at).toLocaleDateString()}</dd>
                        </div>
                    </dl>
                </div>

                {/* Portfolio Summary */}
                <div className="lg:col-span-2 rounded-2xl border border-border bg-surface p-6 shadow-card">
                    <div className="flex items-center justify-between mb-4">
                        <h3 className="text-base font-bold text-text">Associated Properties ({properties.length})</h3>
                        <span className="text-xs text-text-subtle">Real-estate registered to this owner</span>
                    </div>

                    {properties.length === 0 ? (
                        <div className="py-12 text-center text-sm text-text-subtle border border-dashed border-border rounded-xl">
                            No properties added yet by this owner.
                        </div>
                    ) : (
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {properties.map((prop) => (
                                <div key={prop.id} className="rounded-xl border border-border bg-page/50 p-4">
                                    <div className="flex items-center justify-between">
                                        <div className="font-semibold text-text">{prop.name}</div>
                                        <span className="text-xs rounded bg-primary/10 text-primary px-2 py-0.5 font-medium">
                                            {prop.houses?.length ?? 0} units
                                        </span>
                                    </div>
                                    <div className="text-xs text-text-subtle mt-1">{prop.address}</div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
