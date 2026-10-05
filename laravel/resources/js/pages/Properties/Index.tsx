import { Head, Link, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/layouts/AppLayout';
import { formatMoney, normaliseMoneyInput } from '@/lib/money';
import type { PropertiesIndexProps, Property } from '@/types';

const METHODS = [
    { value: 'mobile_money', label: 'Mobile Money' },
    { value: 'paybill', label: 'Paybill' },
    { value: 'bank', label: 'Bank' },
    { value: 'till', label: 'Till' },
] as const;

/**
 * Form shape. Declared explicitly so `reset()` type-checks: Inertia infers the
 * keys from the object passed to useForm, and a spread of EMPTY loses that.
 */
interface PropertyForm {
    name: string;
    address: string;
    type: string;
    rent: string;
    payment_method_type: string;
    paybill_number: string;
    paybill_account: string;
    till_number: string;
    bank_name: string;
    bank_account: string;
    bank_branch: string;
    mobile_money_number: string;
    caretaker_id: string;
}

const EMPTY: PropertyForm = {
    name: '',
    address: '',
    type: 'Apartment Block',
    rent: '',
    payment_method_type: 'mobile_money',
    paybill_number: '',
    paybill_account: '',
    till_number: '',
    bank_name: '',
    bank_account: '',
    bank_branch: '',
    mobile_money_number: '',
    caretaker_id: '',
};

function toForm(p: Property): PropertyForm {
    return {
        ...EMPTY,
        name: p.name,
        address: p.address,
        type: p.type ?? '',
        rent: p.rent,
        payment_method_type: p.payment_method_type ?? 'mobile_money',
        paybill_number: p.paybill_number ?? '',
        paybill_account: p.paybill_account ?? '',
        till_number: p.till_number ?? '',
        bank_name: p.bank_name ?? '',
        bank_account: p.bank_account ?? '',
        bank_branch: p.bank_branch ?? '',
        mobile_money_number: p.mobile_money_number ?? '',
        caretaker_id: p.caretaker_id === null ? '' : String(p.caretaker_id),
    };
}

const field = 'w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white';

/**
 * Properties.
 *
 * Ported from frontend/pages/properties.php. Unit counts come from the server,
 * which recomputes them from the house rows; the browser never counts anything.
 */
export default function PropertiesIndex({
    properties,
    caretakers,
    canManage,
    filters,
    flash,
}: PropertiesIndexProps) {
    const [editing, setEditing] = useState<Property | null>(null);
    const [open, setOpen] = useState(false);

    const { data, setData, post, put, processing, errors, clearErrors } =
        useForm<PropertyForm>(EMPTY);
    const { data: search, setData: setSearch } = useForm({ search: filters.search ?? '' });

    /*
     * Inertia 3's reset() takes field NAMES, not values, so the whole form is
     * replaced with setData() and stale validation errors are cleared by hand.
     */
    function fill(values: PropertyForm) {
        clearErrors();
        setData(values);
    }

    function openNew() {
        fill(EMPTY);
        setEditing(null);
        setOpen(true);
    }

    function openEdit(property: Property) {
        fill(toForm(property));
        setEditing(property);
        setOpen(true);
    }

    function close() {
        setOpen(false);
        setEditing(null);
        fill(EMPTY);
    }

    function submit(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();
        const options = { preserveScroll: true, onSuccess: () => close() };
        if (editing) {
            put(`/properties/${editing.id}`, options);
            return;
        }
        post('/properties', options);
    }

    function remove(property: Property) {
        if (!window.confirm(`Remove ${property.name}?`)) return;
        router.delete(`/properties/${property.id}`, { preserveScroll: true });
    }

    function applySearch(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();
        router.get(
            '/properties',
            { search: search.search || undefined },
            { preserveState: true, preserveScroll: true },
        );
    }

    return (
        <>
            <Head title="Properties" />

            <AppLayout
                title="Properties"
                actions={
                    <>
                        <div>
                            <h1 className="text-2xl font-bold text-slate-900">Properties</h1>
                            <p className="text-slate-500 mt-1">Your buildings and units</p>
                        </div>

                        {canManage && (
                            <button
                                type="button"
                                onClick={openNew}
                                className="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all inline-flex items-center gap-2"
                            >
                                <i className="fas fa-plus" aria-hidden="true" />
                                Add Property
                            </button>
                        )}
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

                <form onSubmit={applySearch} className="mb-4 max-w-sm">
                    <label htmlFor="propertySearch" className="sr-only">Search properties</label>
                    <div className="flex gap-2">
                        <input
                            id="propertySearch"
                            value={search.search}
                            onChange={(e) => setSearch('search', e.target.value)}
                            placeholder="Search by name..."
                            className={field}
                        />
                        <button type="submit" className="px-4 py-2.5 rounded-xl bg-blue-600 text-white font-medium">
                            <i className="fas fa-magnifying-glass" aria-hidden="true" />
                            <span className="sr-only">Search</span>
                        </button>
                    </div>
                </form>

                <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    {properties.data.length === 0 ? (
                        <p className="col-span-full py-12 text-center text-slate-400">
                            No properties found.
                        </p>
                    ) : (
                        properties.data.map((property) => {
                            const vacant = property.units - property.occupied;

                            return (
                                <article
                                    key={property.id}
                                    className="bg-white rounded-2xl shadow-sm border border-blue-100/50 overflow-hidden flex flex-col"
                                >
                                    <div className="p-5 flex-1">
                                        <div className="flex items-start justify-between gap-3">
                                            <div>
                                                <h2 className="font-semibold text-slate-900">{property.name}</h2>
                                                <p className="text-xs text-slate-500 mt-0.5">{property.type ?? 'Property'}</p>
                                            </div>
                                            <i className="fas fa-building text-blue-500 text-xl" aria-hidden="true" />
                                        </div>

                                        <p className="text-sm text-slate-600 mt-3 flex items-start gap-2">
                                            <i className="fas fa-location-dot text-slate-400 mt-0.5" aria-hidden="true" />
                                            {property.address}
                                        </p>

                                        <dl className="grid grid-cols-3 gap-2 mt-4 text-center">
                                            {[
                                                ['Units', property.units],
                                                ['Occupied', property.occupied],
                                                ['Vacant', vacant],
                                            ].map(([label, value]) => (
                                                <div key={String(label)} className="rounded-xl bg-blue-50/60 py-2">
                                                    <dt className="text-[11px] uppercase tracking-wide text-slate-500">{label}</dt>
                                                    <dd className="text-lg font-bold text-slate-900">{value}</dd>
                                                </div>
                                            ))}
                                        </dl>

                                        <p className="text-sm text-slate-600 mt-4">
                                            <span className="text-slate-500">Rent: </span>
                                            <span className="font-semibold">{formatMoney(property.rent)}</span>
                                        </p>

                                        {property.caretaker_name && (
                                            <p className="text-xs text-slate-500 mt-1">
                                                <i className="fas fa-user-shield mr-1" aria-hidden="true" />
                                                {property.caretaker_name}
                                            </p>
                                        )}
                                    </div>

                                    <div className="px-5 py-3 border-t border-blue-50 bg-blue-50/30 flex items-center justify-between">
                                        <Link
                                            href={`/houses?property_id=${property.id}`}
                                            className="text-xs font-medium text-blue-600 hover:text-blue-700"
                                        >
                                            View units
                                        </Link>

                                        {canManage && (
                                            <div className="flex items-center gap-1">
                                                <button type="button" onClick={() => openEdit(property)}
                                                    className="p-1.5 text-slate-400 hover:text-blue-600 transition-colors"
                                                    aria-label={`Edit ${property.name}`}>
                                                    <i className="fas fa-pen" aria-hidden="true" />
                                                </button>
                                                <button type="button" onClick={() => remove(property)}
                                                    className="p-1.5 text-slate-400 hover:text-red-600 transition-colors"
                                                    aria-label={`Remove ${property.name}`}>
                                                    <i className="fas fa-trash" aria-hidden="true" />
                                                </button>
                                            </div>
                                        )}
                                    </div>
                                </article>
                            );
                        })
                    )}
                </div>

                {properties.meta.last_page > 1 && (
                    <nav className="mt-6 flex items-center justify-between" aria-label="Pagination">
                        <p className="text-sm text-slate-500">
                            Page {properties.meta.current_page} of {properties.meta.last_page} ({properties.meta.total})
                        </p>
                        <div className="flex gap-2">
                            {properties.links.prev && (
                                <Link href={properties.links.prev} preserveScroll className="px-3 py-1.5 text-sm rounded-lg border border-slate-200 hover:bg-slate-50">Previous</Link>
                            )}
                            {properties.links.next && (
                                <Link href={properties.links.next} preserveScroll className="px-3 py-1.5 text-sm rounded-lg border border-slate-200 hover:bg-slate-50">Next</Link>
                            )}
                        </div>
                    </nav>
                )}

                {open && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
                        onClick={(e) => { if (e.target === e.currentTarget) close(); }}
                        role="dialog" aria-modal="true" aria-labelledby="property-modal-title">
                        <div className="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6"
                            onClick={(e) => e.stopPropagation()}>
                            <div className="flex items-center justify-between mb-4">
                                <h3 id="property-modal-title" className="text-lg font-bold text-slate-900">
                                    {editing ? `Edit ${editing.name}` : 'Add Property'}
                                </h3>
                                <button type="button" onClick={close} className="text-slate-400 hover:text-slate-600" aria-label="Close">
                                    <i className="fas fa-times text-xl" aria-hidden="true" />
                                </button>
                            </div>

                            <form onSubmit={submit} className="space-y-4" noValidate>
                                <div>
                                    <label htmlFor="pName" className="block text-sm font-medium text-slate-700 mb-1">Name</label>
                                    <input id="pName" value={data.name} onChange={(e) => setData('name', e.target.value)} className={field} required />
                                    {errors.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
                                </div>

                                <div>
                                    <label htmlFor="pAddress" className="block text-sm font-medium text-slate-700 mb-1">Address</label>
                                    <input id="pAddress" value={data.address} onChange={(e) => setData('address', e.target.value)} className={field} required />
                                    {errors.address && <p className="mt-1 text-sm text-red-600">{errors.address}</p>}
                                </div>

                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label htmlFor="pType" className="block text-sm font-medium text-slate-700 mb-1">Type</label>
                                        <input id="pType" value={data.type} onChange={(e) => setData('type', e.target.value)} className={field} />
                                    </div>
                                    <div>
                                        <label htmlFor="pRent" className="block text-sm font-medium text-slate-700 mb-1">Default Rent</label>
                                        <input id="pRent" inputMode="decimal" value={data.rent}
                                            onChange={(e) => setData('rent', normaliseMoneyInput(e.target.value))}
                                            className={field} placeholder="0.00" />
                                        {errors.rent && <p className="mt-1 text-sm text-red-600">{errors.rent}</p>}
                                    </div>
                                </div>

                                <div>
                                    <label htmlFor="pMethod" className="block text-sm font-medium text-slate-700 mb-1">Payment Method</label>
                                    <select id="pMethod" value={data.payment_method_type}
                                        onChange={(e) => setData('payment_method_type', e.target.value)} className={field}>
                                        {METHODS.map((m) => (
                                            <option key={m.value} value={m.value}>{m.label}</option>
                                        ))}
                                    </select>
                                </div>

                                {data.payment_method_type === 'mobile_money' && (
                                    <div>
                                        <label htmlFor="pMpesa" className="block text-sm font-medium text-slate-700 mb-1">Mobile Money Number</label>
                                        <input id="pMpesa" value={data.mobile_money_number}
                                            onChange={(e) => setData('mobile_money_number', e.target.value)} className={field} />
                                    </div>
                                )}

                                {data.payment_method_type === 'paybill' && (
                                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label htmlFor="pPaybill" className="block text-sm font-medium text-slate-700 mb-1">Paybill Number</label>
                                            <input id="pPaybill" value={data.paybill_number}
                                                onChange={(e) => setData('paybill_number', e.target.value)} className={field} />
                                        </div>
                                        <div>
                                            <label htmlFor="pPaybillAcc" className="block text-sm font-medium text-slate-700 mb-1">Paybill Account</label>
                                            <input id="pPaybillAcc" value={data.paybill_account}
                                                onChange={(e) => setData('paybill_account', e.target.value)} className={field} />
                                        </div>
                                    </div>
                                )}

                                {data.payment_method_type === 'till' && (
                                    <div>
                                        <label htmlFor="pTill" className="block text-sm font-medium text-slate-700 mb-1">Till Number</label>
                                        <input id="pTill" value={data.till_number}
                                            onChange={(e) => setData('till_number', e.target.value)} className={field} />
                                    </div>
                                )}

                                {data.payment_method_type === 'bank' && (
                                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label htmlFor="pBankName" className="block text-sm font-medium text-slate-700 mb-1">Bank</label>
                                            <input id="pBankName" value={data.bank_name}
                                                onChange={(e) => setData('bank_name', e.target.value)} className={field} />
                                        </div>
                                        <div>
                                            <label htmlFor="pBankAcc" className="block text-sm font-medium text-slate-700 mb-1">Account</label>
                                            <input id="pBankAcc" value={data.bank_account}
                                                onChange={(e) => setData('bank_account', e.target.value)} className={field} />
                                        </div>
                                        <div>
                                            <label htmlFor="pBankBranch" className="block text-sm font-medium text-slate-700 mb-1">Branch</label>
                                            <input id="pBankBranch" value={data.bank_branch}
                                                onChange={(e) => setData('bank_branch', e.target.value)} className={field} />
                                        </div>
                                    </div>
                                )}

                                <div>
                                    <label htmlFor="pCaretaker" className="block text-sm font-medium text-slate-700 mb-1">Caretaker</label>
                                    <select id="pCaretaker" value={data.caretaker_id}
                                        onChange={(e) => setData('caretaker_id', e.target.value)} className={field}>
                                        <option value="">Unassigned</option>
                                        {caretakers.map((c) => (
                                            <option key={c.id} value={c.id}>{c.name}</option>
                                        ))}
                                    </select>
                                    {errors.caretaker_id && <p className="mt-1 text-sm text-red-600">{errors.caretaker_id}</p>}
                                </div>

                                <div className="flex justify-end gap-3 pt-2">
                                    <button type="button" onClick={close}
                                        className="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-medium hover:bg-slate-50">
                                        Cancel
                                    </button>
                                    <button type="submit" disabled={processing}
                                        className="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all disabled:opacity-60">
                                        {processing ? 'Saving...' : editing ? 'Save Changes' : 'Add Property'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                )}
            </AppLayout>
        </>
    );
}