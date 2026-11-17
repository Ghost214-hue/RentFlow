/** Tenant Portal - Index Page
 * 
 * Balance and bills, payment history, confirm payment,
 * complaints, maintenance, documents, termination request, profile.
 */
import { useAuth } from '@/shared/auth/context';
import { useState, useEffect } from 'react';
import AppLayout from '@/layouts/AppLayout';
import { fetchCSRFToken } from '@/shared/api';

export default function TenantIndex() {
    const { user, profile, logout } = useAuth();
    const [balance, setBalance] = useState<string>('0.00');
    const [bills, setBills] = useState<Array<{month: string; due_date: string; total: string; status: string}>>([]);
    const [payments, setPayments] = useState<Array<{id: number; amount: string; method: string; date: string; status: string}>>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        fetchCSRFToken();
        // Load tenant data from API
        if (user?.id) {
            // Fetch balance and bills
            // ... API calls would go here
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
        <AppLayout title="Tenant Portal" subtitle={`Welcome, ${user.name}`}>
            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                {/* Balance Card */}
                <div className="bg-white rounded-lg shadow p-4">
                    <h3 className="font-medium text-sm text-gray-500 mb-2">Balance</h3>
                    <p className="text-2xl font-bold text-gray-900">{balance}</p>
                </div>

                {/* Recent Bills */}
                <div className="bg-white rounded-lg shadow p-4">
                    <h3 className="font-medium text-sm text-gray-500 mb-2">Recent Bills</h3>
                    {/* Bill list would go here */}
                    {loading ? (
                        <p>Loading...</p>
                    ) : (
                        <p>No bills found</p>
                    )}
                </div>

                {/* Payment History */}
                <div className="bg-white rounded-lg shadow p-4">
                    <h3 className="font-medium text-sm text-gray-500 mb-2">Payment History</h3>
                    {/* Payment list would go here */}
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