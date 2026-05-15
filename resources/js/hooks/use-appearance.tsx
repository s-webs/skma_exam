import { useEffect, useState } from 'react';

export type Appearance = 'light';

const applyTheme = (appearance: Appearance) => {
    document.documentElement.classList.remove('dark');
};

export function initializeTheme() {
    applyTheme('light');
    localStorage.setItem('appearance', 'light');
}

export function useAppearance() {
    const [appearance, setAppearance] = useState<Appearance>('light');

    const updateAppearance = (mode: Appearance) => {
        setAppearance('light');
        localStorage.setItem('appearance', 'light');
        applyTheme('light');
    };

    useEffect(() => {
        updateAppearance('light');
    }, []);

    return { appearance, updateAppearance };
}
