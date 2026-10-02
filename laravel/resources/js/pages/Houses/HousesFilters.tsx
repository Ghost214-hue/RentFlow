import { router } from '@inertiajs/react';
import type { PropertyOption } from '@/types';

interface FiltersProps {
    properties: PropertyOption[];
    filters: {
        property_id: number | null;
        search: string | null;
    };
}

/**
 * Property + search toolbar for the houses list.
 * Filtering happens server-side, so the result set stays owner-scoped.
 */
export default function HousesFilters({
    properties,
    filters,
}: FiltersProps) {
    function apply(propertyId: string | undefined, search: string | undefined) {
        router.get(
            '/houses',
            { property_id: propertyId, search },
            { preserveState: true },
        );
    }

    return (
        <form
            method="get"
            onSubmit={(e) => {
                e.preventDefault();
                const data = new FormData(e.currentTarget);
                const search = data.get('search');

                apply(
                    filters.property_id === null
                        ? undefined
                        : String(filters.property_id),
                    typeof search === 'string' && search !== ''
                        ? search
                        : undefined,
                );
            }}
            className="mb-4 flex flex-col sm:flex-row gap-3"
        >
            <select
                name="property_id"
                value={filters.property_id ?? ''}
                onChange={(e) => {
                    const value = e.target.value;
                    apply(
                        value === '' ? undefined : value,
                        filters.search ?? undefined,
                    );
                }}
                className="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm"
                aria-label="Filter by property"
            >
                <option value="">All properties</option>
                {properties.map((p) => (
                    <option key={p.id} value={p.id}>
                        {p.name}
                    </option>
                ))}
            </select>

            <input
                type="search"
                name="search"
                defaultValue={filters.search ?? ''}
                placeholder="Search unit..."
                aria-label="Search units"
                className="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm flex-1 max-w-xs"
            />
        </form>
    );
}