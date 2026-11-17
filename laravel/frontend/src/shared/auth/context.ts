/**
 * Authentication context for React portals.
 * 
 * Manages:
 * - Login / logout / profile switcher
 * - Session expiry handling
 * - Route guards driven by the profile and permissions from the API
 */
import { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import api, { formatMoney, parseMoney, AuthUser, AuthState } from './api';

// Check if user already has a session (server-rendered from Inertia)
const storedUser = typeof window !== 'undefined' ? 
    JSON.parse(localStorage.getItem('rentflow:user') || 'null') : null;

const AuthContext = createContext<AuthState | undefined>(undefined);

export const useAuth = (): AuthState => {
    const context = useContext(AuthContext);
    if (!context) {
        throw new Error('useAuth must be used within an AuthProvider');
    }
    return context;
};

export const AuthProvider = ({ children }: { children: ReactNode }) => {
    const [user, setUser] = useState<AuthUser | null>(storedUser);
    const [profile, setProfile] = useState<'owner' | 'caretaker' | 'tenant' | null>(null);

    // Load user profile on mount
    useEffect(() => {
        if (user?.id && !profile) {
            // Fetch profile from API
            api.get(`/profiles/${user.id}`)
                .then(res => {
                    setProfile(res.data.profile ?? null);
                })
                .catch(() => {
                    // If profile fetch fails, clear and let login handle it
                    setUser(null);
                    setProfile(null);
                });
        }
    }, [user, profile]);

    // Login
    const login = async (email: string, password: string) => {
        try {
            const res = await api.post('/login', { email, password });
            const data = res.data;
            
            setUser(data.user);
            setProfile(data.profile);
            
            // Store user in localStorage for persistence across refreshes
            if (typeof window !== 'undefined') {
                localStorage.setItem('rentflow:user', JSON.stringify(data.user));
            }
        } catch (e) {
            console.error('Login failed', e);
            throw e;
        }
    };

    // Logout
    const logout = async () => {
        try {
            await api.post('/logout');
        } catch (e) {
            console.error('Logout failed', e);
        } finally {
            setUser(null);
            setProfile(null);
            
            if (typeof window !== 'undefined') {
                localStorage.removeItem('rentflow:user');
                // Remove auth-related cookies
                document.cookie = 'rf_session=; Max-Age=0; path=/';
                document.cookie = 'rf_admin_session=; Max-Age=0; path=/';
            }
        }
    };

    // Switch profile (for users with multiple profiles)
    const switchProfile = async (newProfile: 'owner' | 'caretaker' | 'tenant') => {
        try {
            const res = await api.post(`/profiles/select`, { profile: newProfile });
            setProfile(res.data.profile);
        } catch (e) {
            console.error('Profile switch failed', e);
            throw e;
        }
    };

    // Check if user has a specific profile
    const hasProfile = (profile: 'owner' | 'caretaker' | 'tenant'): boolean => {
        return profile === (profile || null);
    };

    return (
        <AuthContext.Provider value={{
            user,
            profile,
            login,
            logout,
            switchProfile,
            hasProfile,
        }}>
            {children}
        </AuthContext.Provider>
    );
};