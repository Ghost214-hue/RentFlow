import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/layouts/AppLayout';
import type { AuthUser, House, Paginated, PropertyOption } from '@/types';
import HouseModal from './HouseModal';
import HousesFilters from './HousesFilters';
import HousesTable from './HousesTable';

interface Props {
    houses: Paginated<House>;
    properties: PropertyOption[];
    filters: {
        property_id: number | null;
        search: string | null;
    };
    flash?: { success?: string | null; error?: string | null };
}

/**
 * Houses & Units.
 *
 * Ported from frontend/pages/houses.php: same columns, Tailwind classes and
 * wording ("Houses & Units", "Manage individual units", "Add Unit"). The
 * inline fetch()/JSON row-building JS is replaced by typed Inertia props, and
 * money is formatted from a string, never computed.
 */
export default function HousesIndex({
    houses,
    properties,
    filters,
    flash,
}: Props) {
    const user = (usePage().props.auth as { user: AuthUser | null }).user;
    const canManage = user?.role === 'owner';

    const [showModal, setShowModal] = useState(false);
    const [editing, setEditing] = useState<House | null>(null);

    function remove(house: House) {
        if (
            !window.confirm(
                `Delete unit ${house.unit}? This cannot be undone.`,
            )
        ) {
            return;
        }

        router.delete(`/houses/${house.id}`, { preserveScroll: true });
    }

    return (
        <>
            <Head title="Houses & Units" />

        <AppLayout
            title="Houses & Units"
            actions={
                <>
                    <div>
                        <h1 className="text-2xl font-bold text-slate-900">
                            Houses &amp; Units
                        </h1>
                        <p className="text-slate-500 mt-1">
                            Manage individual units
                        </p>
                    </div>

                    {canManage && (
                        <button
                            type="button"
                            onClick={() => {
                                setEditing(null);
                                setShowModal(true);
                            }}
                            className="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all inline-flex items-center gap-2"
                        >
                            <i className="fas fa-plus" aria-hidden="true" />
                            Add Unit
                        </button>
                    )}
                </>
            }
        >
            {flash?.success && (
                <div
                    role="status"
                    className="mb-4 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-700 text-sm border border-emerald-100"
                >
                    {flash.success}
                </div>
            )}

            <HousesFilters properties={properties} filters={filters} />

            <HousesTable
                houses={houses}
                canManage={canManage}
                onEdit={(house) => {
                    setEditing(house);
                    setShowModal(true);
                }}
                onDelete={remove}
            />

            {showModal && (
                <HouseModal
                    editing={editing}
                    properties={properties}
                    onClose={() => {
                        setShowModal(false);
                        setEditing(null);
                    }}
                />
            )}
        </AppLayout>
        </>
    );
}