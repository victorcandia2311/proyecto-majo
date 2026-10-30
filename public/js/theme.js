(() => {
    const preferenceKey = 'majo.theme';
    const validPreferences = new Set(['light', 'dark', 'auto']);
    const root = document.documentElement;
    const systemPreference = window.matchMedia('(prefers-color-scheme: dark)');
    let preference = 'auto';
    let storageUnavailable = false;

    try {
        const storedPreference = window.localStorage.getItem(preferenceKey);
        if (validPreferences.has(storedPreference)) {
            preference = storedPreference;
        }
    } catch (error) {
        storageUnavailable = true;
        console.warn('No se pudo leer la preferencia de tema guardada.', error);
    }

    const applyTheme = () => {
        const resolvedTheme = preference === 'auto'
            ? (systemPreference.matches ? 'dark' : 'light')
            : preference;

        root.dataset.bsTheme = resolvedTheme;
        root.dataset.themePreference = preference;

        const themeColor = document.querySelector('meta[name="theme-color"]');
        themeColor?.setAttribute('content', resolvedTheme === 'dark' ? '#111827' : '#f3f4f6');

        window.dispatchEvent(new CustomEvent('majo:theme-change', {
            detail: { preference, resolvedTheme },
        }));
    };

    const announceStorageUnavailable = () => {
        const status = document.querySelector('[data-theme-status]');
        if (status) {
            status.textContent = 'No se pudo guardar la preferencia en este navegador.';
        }
    };

    const syncSelector = () => {
        const selector = document.querySelector('[data-theme-selector]');
        if (selector) {
            selector.value = preference;
        }
    };

    const setPreference = (nextPreference) => {
        if (!validPreferences.has(nextPreference)) {
            return;
        }

        preference = nextPreference;
        applyTheme();

        const status = document.querySelector('[data-theme-status]');
        if (status) {
            status.textContent = '';
        }

        try {
            window.localStorage.setItem(preferenceKey, preference);
        } catch (error) {
            storageUnavailable = true;
            console.warn('No se pudo guardar la preferencia de tema.', error);
            announceStorageUnavailable();
        }
    };

    const initializeSelector = () => {
        const selector = document.querySelector('[data-theme-selector]');
        if (selector) {
            selector.value = preference;
            selector.addEventListener('change', () => setPreference(selector.value));
        }

        if (storageUnavailable) {
            announceStorageUnavailable();
        }
    };

    const handleSystemPreferenceChange = () => {
        if (preference === 'auto') {
            applyTheme();
        }
    };

    if (typeof systemPreference.addEventListener === 'function') {
        systemPreference.addEventListener('change', handleSystemPreferenceChange);
    } else {
        systemPreference.addListener(handleSystemPreferenceChange);
    }

    window.addEventListener('storage', (event) => {
        if (event.key !== preferenceKey) {
            return;
        }

        preference = validPreferences.has(event.newValue) ? event.newValue : 'auto';
        applyTheme();
        syncSelector();
    });

    applyTheme();

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeSelector, { once: true });
    } else {
        initializeSelector();
    }
})();
