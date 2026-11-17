/**
 * Profile switcher component.
 * 
 * Shows the current profile and allows switching between profiles
 * when a login holds multiple profiles (owner, caretaker, renter).
 */
import { useAuth } from '@/shared/auth/context';
import { useState } from 'react';

export const ProfileSwitcher = () => {
    const { user, profile, switchProfile, hasProfile } = useAuth();
    const [isDropdownOpen, setIsDropdownOpen] = useState(false);

    if (!user) {
        return null;
    }

    return (
        <div className="flex items-center gap-2">
            <span className="text-sm font-medium text-gray-300">
                {user.name}
            </span>
            
            <button
                onClick={() => setIsDropdownOpen(!isDropdownOpen)}
                className="flex items-center gap-1 px-2 py-1 rounded hover:bg-gray-700 cursor-pointer text-sm"
                aria-haspopup="menu"
                aria-label="Profile menu"
            >
                {profile?.charAt(0).toUpperCase() + profile?.slice(1) ?? 'No Profile'}
                <i className="fas fa-caret-down text-xs opacity-60" />
            </button>

            {isDropdownOpen && (
                <div className="absolute right-0 mt-2 w-48 bg-white rounded border border-gray-300 shadow-md py-2 min-w">
                    {user.profiles?.length > 0 ? (
                        user.profiles?.map((p: any) => (
                            <button
                                key={p.type}
                                onClick={() => switchProfile(p.type)}
                                className="w-full text-left px-3 py-1.5 text-sm rounded hover:bg-gray-50 cursor-pointer"
                                aria-label={`Switch to ${p.type} profile`}
                            >
                                {p.type.charAt(0).toUpperCase() + p.type.slice(1)}
                                {p.owner_id && <span className="text-xs text-gray-500 ml-2">(owner: {p.owner_id})</span>}
                            </button>
                        ))
                    ) : (
                        <span className="px-3 py-1.5 text-sm text-gray-500">
                            Single {profile} profile
                        </span>
                    )}
                </div>
            )}
            
            <button
                onClick={logout}
                className="ml-2 text-sm text-red-600 hover:text-red-800 cursor-pointer"
                aria-label="Sign out"
            >
                Sign out
            </button>
        </div>
    );
};