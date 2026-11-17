/** Tenant Portal - Profile Page
 * 
 * Displays tenant profile with editable contact details.
 */
import { useAuth } from '@/shared/auth/context';
import { useState } from 'react';
import AppLayout from '@/layouts/AppLayout';

export default function TenantProfile() {
    const { user, profile } = useAuth();
    const [editing, setEditing] = useState(false);
    const [name, setName] = useState<string>(user?.name || '');
    const [email, setEmail] = useState<string>(user?.email || '');
    const [phone, setPhone] = useState<string>(user?.phone || '');

    if (!user) {
        return <p>Please sign in to view your profile.</p>;
    }

    return (
        <AppLayout title="My Profile" subtitle="RentFlow">
            <div className="max-w-md mx-auto">
                <div className="mb-4">
                    <h2 className="text-xl font-bold mb-2">My Profile</h2>
                    <p className="text-sm text-gray-500">
                        <strong>Role:</strong> {profile?.charAt(0).toUpperCase() + profile?.slice(1) ?? 'Tenant'}
                    </p>
                </div>

                <form className="space-y-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Name
                        </label>
                        <input
                            className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tracking"
                            type="text"
                            value={name}
                            onChange={(e) => setName(e.target.value)}
                            disabled={editing}
                        />
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Email
                        </label>
                        <input
                            className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tracking"
                            type="email"
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                            disabled={editing}
                        />
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Phone
                        </label>
                        <input
                            className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tracking"
                            type="tel"
                            value={phone}
                            onChange={(e) => setPhone(e.target.value)}
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
                            <button
                                onClick={() => {
                                    // Would save profile changes via API
                                    setEditing(false);
                                }
                                className="px-4 py-2 bg-primary text-white rounded hover:bg-primary/90"
                            >
                                Save changes
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