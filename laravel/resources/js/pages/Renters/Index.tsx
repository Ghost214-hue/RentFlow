import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/layouts/AppLayout';
import type { AuthUser, Paginated, PropertyOption, Renter, VacantHouse } from '@/types';
import RenterWizardModal from './RenterWizardModal';
import TerminateModal from './TerminateModal';
import RentersTable from './RentersTable';

interface Props {
    renters: Paginated<Renter>;
    properties: PropertyOption[];
    vacantHouses: VacantHouse[];
    filters: {
        search: string | null;
        status: string | null;
        property_id: number | null;
    };
    flash?: { success?: string | null; error?: string | null };
}

/**
 * Renters.
 *
 * Ported from frontend/pages/tenants.php. Wording, classes and icon set are
 * preserved; the inline fetch()/JSON row building is replaced by typed props.
 */
export default function RentersIndex({
    renters,
    properties,
    vacantHouses,
    filters,
    flash,
}: Props) {
    const user = (usePage().props.auth as { user: AuthUser | null }).user;
    const canManage = user?.role === 'owner';

    const [showModal, setShowModal] = useState(false);
    const [editing, setEditing] = useState<Renter | null>(null);

    // Termination replaces deletion, so this drives a modal rather than a
    // confirm-and-DELETE.
    const [terminating, setTerminating] = useState<Renter | null>(null);
    const [terminateMode, setTerminateMode] = useState<'terminate' | 'approve'>('terminate');

    function applyFilter(propertyId: string | undefined, status: string | undefined, search: string | undefined) {
        router.get('/renters', { property_id: propertyId, status, search }, { preserveState: true });
    }

    /*
     * A tenancy is ENDED, never deleted. There is no remove action: deleting the
     * row would orphan the bills, payments and the termination audit trail.
     * Terminating frees the unit and scrubs personal data while keeping the
     * financial history.
     */
    function openTerminate(renter: Renter, mode: 'terminate' | 'approve') {
        setTerminating(renter);
        setTerminateMode(mode);
    }

    return (
        <>
            <Head title="Renters" />

            <AppLayout
                title="Renters"
                actions={
                    <>
                        <div>
                            <h1 className="text-2xl font-bold text-slate-900">Renters</h1>
                            <p className="text-slate-500 mt-1">Manage your tenants</p>
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
                                Add Renter
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

                <form method="get" className="mb-4 flex flex-col sm:flex-row gap-3"
                    onSubmit={(e) => {
                        e.preventDefault();
                        const search = new FormData(e.currentTarget).get('search');
                        applyFilter(
                            filters.property_id === null ? undefined : String(filters.property_id),
                            filters.status ?? undefined,
                            typeof search === 'string' && search !== '' ? search : undefined,
                        );
                    }}>
                    <select name="property_id" value={filters.property_id ?? ''} aria-label="Filter by property"
                        onChange={(e) => applyFilter(e.target.value || undefined, filters.status ?? undefined, filters.search ?? undefined)}
                        className="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm">
                        <option value="">All properties</option>
                        {properties.map((p) => <option key={p.id} value={p.id}>{p.name}</option>)}
                    </select>

                    <select name="status" value={filters.status ?? ''} aria-label="Filter by status"
                        onChange={(e) => applyFilter(filters.property_id === null ? undefined : String(filters.property_id), e.target.value || undefined, filters.search ?? undefined)}
                        className="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm">
                        <option value="">All statuses</option>
                        <option value="active">Active</option>
                        <option value="terminated">Terminated</option>
                    </select>

                    <input type="search" name="search" defaultValue={filters.search ?? ''} placeholder="Search renters..."
                        aria-label="Search renters"
                        className="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm flex-1 max-w-xs" />
                </form>

                <RentersTable
                    renters={renters}
                    canManage={canManage}
                    onEdit={(r) => { setEditing(r); setShowModal(true); }}
                    onTerminate={openTerminate}
                />

                {terminating && (
                    <TerminateModal
                        renter={terminating}
                        mode={terminateMode}
                        onClose={() => { setTerminating(null); setTerminateMode('terminate'); }}
                    />
                )}

                {showModal && (
                    <RenterWizardModal
                        editing={editing}
                        properties={properties}
                        vacantHouses={vacantHouses}
                        onClose={() => { setShowModal(false); setEditing(null); }}
                    />
                )}
            </AppLayout>
        </>
    );
}
