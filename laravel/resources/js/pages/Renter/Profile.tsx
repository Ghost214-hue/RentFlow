import { Head, useForm } from '@inertiajs/react';
import { useEffect } from 'react';
import AppLayout from '@/layouts/AppLayout';
import { formatMoney } from '@/lib/money';
import type { RenterProfileProps } from '@/types';

const fieldClass = 'w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white';

function Field({ id, label, error, children }: {
    id: string; label: string; error?: string; children: React.ReactNode;
}) {
    return (
        <div>
            <label htmlFor={id} className="block text-sm font-medium text-slate-700 mb-1">{label}</label>
            {children}
            {error && <p className="mt-1 text-sm text-red-600">{error}</p>}
        </div>
    );
}

/**
 * The renter's own profile.
 *
 * Ported from frontend/pages/tenant-profile.php. Only contact details are
 * editable; tenancy (unit, rent, status, lease dates) is displayed read-only
 * because the owner owns it.
 */
export default function RenterProfile({ renter, flash }: RenterProfileProps) {
    const { data, setData, put, processing, errors } = useForm({
        name: renter.name,
        email: renter.email,
        phone: renter.phone ?? '',
        id_number: renter.id_number ?? '',
        id_type: renter.id_type ?? '',
        next_of_kin_name: renter.next_of_kin_name ?? '',
        next_of_kin_phone: renter.next_of_kin_phone ?? '',
        next_of_kin_email: renter.next_of_kin_email ?? '',
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    // Re-sync after a successful save so the form reflects stored values.
    useEffect(() => {
        setData((prev) => ({
            ...prev,
            name: renter.name,
            email: renter.email,
            phone: renter.phone ?? '',
        }));
    }, [renter.name, renter.email, renter.phone, setData]);

    function submit(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();
        put('/renter/profile', { preserveScroll: true });
    }

    return (
        <>
            <Head title="My Profile" />

            <AppLayout
                title="My Profile"
                actions={
                    <div>
                        <h1 className="text-2xl font-bold text-slate-900">My Profile</h1>
                        <p className="text-slate-500 mt-1">Your contact details</p>
                    </div>
                }
            >
                {flash?.success && (
                    <div role="status" className="mb-4 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-700 text-sm border border-emerald-100">
                        {flash.success}
                    </div>
                )}

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div className="lg:col-span-2 space-y-6">
                        <form onSubmit={submit} className="space-y-6" noValidate>
                            <section className="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-6">
                                <h2 className="font-semibold text-slate-900 mb-4">Contact Details</h2>

                                <div className="space-y-4">
                                    <Field id="profileName" label="Full Name" error={errors.name}>
                                        <input id="profileName" value={data.name}
                                            onChange={(e) => setData('name', e.target.value)} className={fieldClass} required />
                                    </Field>

                                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <Field id="profileEmail" label="Email" error={errors.email}>
                                            <input id="profileEmail" type="email" value={data.email}
                                                onChange={(e) => setData('email', e.target.value)} className={fieldClass} required />
                                        </Field>
                                        <Field id="profilePhone" label="Phone" error={errors.phone}>
                                            <input id="profilePhone" value={data.phone}
                                                onChange={(e) => setData('phone', e.target.value)} className={fieldClass} />
                                        </Field>
                                    </div>

                                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <Field id="profileIdType" label="ID Type" error={errors.id_type}>
                                            <input id="profileIdType" value={data.id_type}
                                                onChange={(e) => setData('id_type', e.target.value)} className={fieldClass} />
                                        </Field>
                                        <Field id="profileIdNumber" label="ID Number" error={errors.id_number}>
                                            <input id="profileIdNumber" value={data.id_number}
                                                onChange={(e) => setData('id_number', e.target.value)} className={fieldClass} />
                                        </Field>
                                    </div>
                                </div>
                            </section>

                            <section className="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-6">
                                <h2 className="font-semibold text-slate-900 mb-1">Next of Kin</h2>
                                <p className="text-sm text-slate-500 mb-4">Who we should contact in an emergency.</p>

                                <div className="space-y-4">
                                    <Field id="kinName" label="Name" error={errors.next_of_kin_name}>
                                        <input id="kinName" value={data.next_of_kin_name}
                                            onChange={(e) => setData('next_of_kin_name', e.target.value)} className={fieldClass} />
                                    </Field>

                                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <Field id="kinPhone" label="Phone" error={errors.next_of_kin_phone}>
                                            <input id="kinPhone" value={data.next_of_kin_phone}
                                                onChange={(e) => setData('next_of_kin_phone', e.target.value)} className={fieldClass} />
                                        </Field>
                                        <Field id="kinEmail" label="Email" error={errors.next_of_kin_email}>
                                            <input id="kinEmail" type="email" value={data.next_of_kin_email}
                                                onChange={(e) => setData('next_of_kin_email', e.target.value)} className={fieldClass} />
                                        </Field>
                                    </div>
                                </div>
                            </section>

                            <section className="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-6">
                                <h2 className="font-semibold text-slate-900 mb-1">Change Password</h2>
                                <p className="text-sm text-slate-500 mb-4">Leave blank to keep your current password.</p>

                                <div className="space-y-4">
                                    <Field id="currentPassword" label="Current Password" error={errors.current_password}>
                                        <input id="currentPassword" type="password" value={data.current_password}
                                            onChange={(e) => setData('current_password', e.target.value)} className={fieldClass}
                                            autoComplete="current-password" />
                                    </Field>

                                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <Field id="newPassword" label="New Password" error={errors.password}>
                                            <input id="newPassword" type="password" value={data.password}
                                                onChange={(e) => setData('password', e.target.value)} className={fieldClass}
                                                autoComplete="new-password" />
                                        </Field>
                                        <Field id="confirmPassword" label="Confirm New Password" error={errors.password_confirmation}>
                                            <input id="confirmPassword" type="password" value={data.password_confirmation}
                                                onChange={(e) => setData('password_confirmation', e.target.value)} className={fieldClass}
                                                autoComplete="new-password" />
                                        </Field>
                                    </div>
                                </div>
                            </section>

                            <div className="flex justify-end">
                                <button type="submit" disabled={processing}
                                    className="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all disabled:opacity-60">
                                    {processing ? 'Saving...' : 'Save Changes'}
                                </button>
                            </div>
                        </form>
                    </div>

                    <aside className="space-y-6">
                        <section className="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-6">
                            <h2 className="font-semibold text-slate-900 mb-4">Your Tenancy</h2>

                            <dl className="space-y-3 text-sm">
                                {[
                                    ['Status', renter.status],
                                    ['Property', renter.property_name ?? '-'],
                                    ['Unit', renter.house_unit ?? '-'],
                                    ['Lease start', renter.lease_start ?? '-'],
                                    ['Lease end', renter.lease_end ?? '-'],
                                    ['Deposit', formatMoney(renter.deposit)],
                                    ['Balance', formatMoney(renter.balance)],
                                    ['Credit', formatMoney(renter.credit)],
                                ].map(([label, value]) => (
                                    <div key={label} className="flex items-center justify-between gap-4">
                                        <dt className="text-slate-500">{label}</dt>
                                        <dd className="font-medium text-slate-900 text-right">{value}</dd>
                                    </div>
                                ))}
                            </dl>

                            <p className="mt-4 pt-4 border-t border-blue-50 text-xs text-slate-400">
                                Contact your landlord to change any tenancy detail.
                            </p>
                        </section>
                    </aside>
                </div>
            </AppLayout>
        </>
    );
}