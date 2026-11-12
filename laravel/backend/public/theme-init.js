(function () {
    const storageKey = 'rentflow-theme';
    const root = document.documentElement;

    function getStoredTheme() {
        try {
            const stored = window.localStorage.getItem(storageKey);
            return stored === 'light' || stored === 'dark' || stored === 'system' ? stored : 'light';
        } catch (error) {
            return 'light';
        }
    }

    function getSystemTheme() {
        return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    const theme = getStoredTheme();
    const resolved = theme === 'system' ? getSystemTheme() : theme;

    root.classList.toggle('dark', resolved === 'dark');
    root.style.colorScheme = resolved;

    if (theme === 'system') {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        const updateTheme = (event) => {
            root.classList.toggle('dark', event.matches);
            root.style.colorScheme = event.matches ? 'dark' : 'light';
        };

        if (typeof mediaQuery.addEventListener === 'function') {
            mediaQuery.addEventListener('change', updateTheme);
        } else if (typeof mediaQuery.addListener === 'function') {
            mediaQuery.addListener(updateTheme);
        }
    }
})();
