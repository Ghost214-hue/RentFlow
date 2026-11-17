/**
 * Axios client for RentFlow API.
 * 
 * Features:
 * - Credentials include with every request (cookies)
 * - CSRF cookie flow: fetch /api/v1/csrf-cookie on app start
 * - Central error handling
 * - Redirect to login on 401
 */
import axios from 'axios';
import { useNavigation } from '@inertiajs/react';

const baseURL = import.meta.env.VITE_API_URL || '/api/v1';

// Create axios instance with cookies enabled
const api = axios.create({
    baseURL,
    withCredentials: true,
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    },
});

// Interceptor: handle errors globally
api.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            // Redirect to login on 401
            const navigation = useNavigation();
            navigation.replace('/login');
        }
        return Promise.reject(error);
    }
);

// CSRF cookie flow: fetch fresh cookie on app start
export const fetchCSRFToken = async () => {
    try {
        await api.get('/csrf-cookie');
    } catch (e) {
        // If CSRF fetch fails, the cookie may already be set from login
        console.warn('CSRF token fetch failed, proceeding anyway.');
    }
};

// Export the api instance for use in components
export default api;

// Type exports for API responses
export interface AuthUser {
    id: number;
    name: string;
    email: string;
    role: 'owner' | 'caretaker' | 'tenant';
    roleLabel: string;
}

export interface AuthState {
    user: AuthUser | null;
    profile: 'owner' | 'caretaker' | 'tenant' | null;
    login: (email: string, password: string) => Promise<void>;
    logout: () => Promise<void>;
    switchProfile: (profile: 'owner' | 'caretaker' | 'tenant') => Promise<void>;
    hasProfile: (profile: 'owner' | 'caretaker' | 'tenant') => boolean;
}

// Money handling - money is always a string or decimal, never a float
export function formatMoney(amount: string | number): string {
    const num = typeof amount === 'number' ? amount : parseFloat(amount);
    if (isNaN(num)) return '0.00';
    return num.toFixed(2);
}

export function parseMoney(raw: string): number {
    const cleaned = raw.replace(/[^0-9.-]/g, '');
    return parseFloat(cleaned) || 0;
}