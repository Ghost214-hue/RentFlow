import { useEffect, useState } from 'react';

export type ThemeMode = 'light' | 'dark' | 'system';
export type ResolvedTheme = 'light' | 'dark';

function getStoredTheme(): ThemeMode {
    if (typeof window === 'undefined') {
        return 'light';
    }

    let stored: string | null;
    try {
        stored = localStorage.getItem('rentflow-theme');
    } catch {
        return 'light';
    }

    if (stored === 'light' || stored === 'dark' || stored === 'system') {
        return stored;
    }

    return 'light';
}

export function useTheme() {
    const [theme, setThemeState] = useState<ThemeMode>(getStoredTheme);

    const systemPrefersDark = typeof window !== 'undefined' && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const resolvedTheme: ResolvedTheme = theme === 'system' ? (systemPrefersDark ? 'dark' : 'light') : theme;

    useEffect(() => {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        const applyTheme = () => {
            const resolved = theme === 'system' ? (mediaQuery.matches ? 'dark' : 'light') : theme;
            document.documentElement.classList.toggle('dark', resolved === 'dark');
            document.documentElement.style.colorScheme = resolved;
        };

        applyTheme();
        mediaQuery.addEventListener('change', applyTheme);

        return () => mediaQuery.removeEventListener('change', applyTheme);
    }, [theme]);

    const setTheme = (nextTheme: ThemeMode) => {
        setThemeState(nextTheme);
        if (typeof window !== 'undefined') {
            localStorage.setItem('rentflow-theme', nextTheme);
        }
    };

    return { theme, resolvedTheme, setTheme };
}
