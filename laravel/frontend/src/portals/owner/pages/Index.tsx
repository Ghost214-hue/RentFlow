/** Owner Portal - Index Page
 * 
 * Dashboard: properties, houses, renters (active and terminated on separate pages),
 * bills, payments and reversals, caretakers and permissions, documents, email logs.
 */
import { useAuth } from '@/shared/auth/context';
import { useState, useEffect } from 'react';
import AppLayout from '@/layouts/AppLayout';
import { fetchCSRFToken } from '@/shared/api';

export default function OwnerIndex() {
    const { user, profile, logout } = useAuth();
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        fetchCSRFToken();
        if (user?.id) {
            // Fetch owner data from API
            // Would call: api.get(`/owner/${user.id}/dashboard`)
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
        <AppLayout title="Owner Dashboard" subtitle={`Welcome, ${user.name}`}>
            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
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

                {/* Unpaid Bills */}
                <div className="bg-white rounded-lg shadow p-4">
                    <h3 className="font-medium text-sm text-gray-500 mb-2">Unpaid Bills</h3>
                    <p className="text-2xl font-bold text-gray-900">0</p>
                    <p className="text-sm text-gray-500">Outstanding amount</p>
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