/** Caretaker Portal - Index Page
 * 
 * Dashboard: assigned properties, houses and renters, record payments,
 * payments, complaints and maintenance.
 */
import { useAuth } from '@/shared/auth/context';
import { useState, useEffect } from 'react';
import AppLayout from '@/layouts/AppLayout';
import { fetchCSRFToken } from '@/shared/api';

export default function CaretakerIndex() {
    const { user, profile, logout } = useAuth();
    const [properties, setProperties] = useState<Array<{id: number; name: string; units: number; occupied: number; vacant: number}>>([]);
    const [renters, setRenters] = useState<Array<{id: number; name: string; property_name: string; house_unit: string; status: string}>>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        fetchCSRFToken();
        if (user?.id) {
            // Fetch caretaker data from API
            // Would call: api.get(`/caretaker/${user.id}/dashboard`)
        }
        setLoading(false);
    }, [user?.id]);

    if (!user) {
        return (
            <div className="min-h-screen flex items-center justify-center">
                <p>Please sign in to view your portal.</p>
            </div>
        );
    }

    return (
        <AppLayout title="Caretaker Dashboard" subtitle={`Welcome, ${user.name}`}>
            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                {/* Assigned Properties */}
                <div className="bg-white rounded-lg shadow p-4">
                    <h3 className="font-medium text-sm text-gray-500 mb-2">Assigned Properties</h3>
                    <p className="text-2xl font-bold text-gray-900">{properties.length}</p>
                    <p className="text-sm text-gray-500">Properties under management</p>
                </div>

                {/* Active Renters */}
                <div className="bg-white rounded-lg shadow p-4">
                    <h3 className="font-medium text-sm text-gray-500 mb-2">Active Renters</h3>
                    <p className="text-2xl font-bold text-gray-900">{renters.length}</p>
                    <p className="text-sm text-gray-500">Currently occupied units</p>
                </div>

                {/* Open Complaints */}
                <div className="bg-white rounded-lg shadow p-4">
                    <h3 className="font-medium text-sm text-gray-500 mb-2">Open Complaints</h3>
                    {/* Complaint count would go here */}
                    <p>0</p>
                </div>
            </div>

            {/* Action Section */}
            <div className="mt-6 pt-6 border-t border-gray-200">
                <button
                    onClick={logout}
                    className="w-full bg-danger text-white py-2 rounded hover:bg-danger/90"
                >
                    Sign Out
                </button>
            </div>
        </AppLayout>
    );
}