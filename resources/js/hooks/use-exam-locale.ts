import { useEffect } from 'react';
import i18n from '@/i18n/config';
import { normalizeNameLocale } from '@/lib/localized-name';

export function useExamLocale(locale: string): void {
    useEffect(() => {
        const normalized = normalizeNameLocale(locale);

        if (i18n.language !== normalized) {
            i18n.changeLanguage(normalized);
        }
    }, [locale]);
}
