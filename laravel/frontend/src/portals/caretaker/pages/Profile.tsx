/** Caretaker Portal - Profile Page
 * 
 * Displays caretaker profile and assigned properties overview.
 */
import { useAuth } from '@/shared/auth/context';
import { useState } from 'react';
import AppLayout from '@/layouts/AppLayout';

export default function CaretakerProfile() {
    const { user, profile } = useAuth();
    const [editing, setEditing] = useState(false);

    if (!user) {
        return <p>Please sign in to view your profile.</p>;
    }

    return (
        <AppLayout title="My Profile" subtitle="RentFlow">
            <div className="max-w-md mx-auto">
                <div className="mb-4">
                    <h2 className="text-xl font-bold mb-2">My Profile</h2>
                    <p className="text-sm text-gray-500">
                        <strong>Role:</strong> {profile?.charAt(0).toUpperCase() + profile?.slice(1) ?? 'Caretaker'}
                    </p>
                </div>

                <form className="space-y-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input
                            className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tracking"
                            type="text"
                            value={user.name || ''}
                            onChange={(e) => {/* would save */}}
                            disabled={editing}
                        />
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input
                            className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tracking"
                            type="email"
                            value={user.email || ''}
                            onChange={(e) => {/* would save */}}
                            disabled={editing}
                        />
                    </div>

                    {editing ? (
                        <div className="mt-4 flex justify-end space-x-2">
                            <button
                                onClick={() => setEditing(false)}
                                className="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300"
                            >
                                Cancel
                            </button>
                        </div>
                    ) : (
                        <div className="mt-4 flex justify-end">
                            <button
                                onClick={() => setEditing(true)}
                                className="px-4 py-2 bg-primary text-white rounded hover:bg-primary/90"
                            >
                                Edit
                            </button>
                        </div>
                    )}
                </form>
            </div>
        </AppLayout>
    );
}