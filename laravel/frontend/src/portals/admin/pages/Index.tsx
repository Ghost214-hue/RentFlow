/** Admin Portal - Index Page
 * 
 * Platform overview: owners, properties, units, occupancy, active renters,
 * billed vs collected this month, collection rate, new signups.
 */
import { useAuth } from '@/shared/auth/context';
import { useState, useEffect } from 'react';
import AppLayout from '@/layouts/AppLayout';
import { fetchCSRFToken } from '@/shared/api';

export default function AdminIndex() {
    const { user, profile, logout } = useAuth();
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        fetchCSRFToken();
        if (user?.id) {
            // Fetch platform data from API
            // Would call: api.get(`/admin/dashboard`)
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
        <AppLayout title="Admin Dashboard" subtitle="RentFlow Platform">
            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                {/* Owners */}
                <div className="bg-white rounded-lg shadow p-4">
                    <h3 className="font-medium text-sm text-gray-500 mb-2">Owners</h3>
                    <p className="text-2xl font-bold text-gray-900">0</p>
                    <p className="text-sm text-gray-500">Total owners</p>
                </div>

                {/* Properties */}
                <div className="bg-white rounded-lg shadow p-4">
                    <h3 className="font-medium text-sm text-gray-500 mb-2">Properties</h3>
                    <p className="text-2xl font-bold text-gray-900">0</p>
                    <p className="text-sm text-gray-500">Total properties</p>
                </div>

                {/* Active Renters */}
                <div className="bg-white rounded-lg shadow p-4">
                    <h3 className="font-medium text-sm text-gray-500 mb-2">Active Renters</h3>
                    <p className="text-2xl font-bold text-gray-900">0</p>
                    <p className="text-sm text-gray-500">Currently occupied</p>
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