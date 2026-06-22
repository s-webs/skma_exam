import { describe, expect, it } from 'vitest';
import { getLocalizedName } from '@/lib/localized-name';

describe('getLocalizedName', () => {
    const names = {
        name_ru: 'Русский',
        name_kk: 'Қазақша',
        name_en: 'English',
    };

    it('returns russian by default', () => {
        expect(getLocalizedName(names, 'ru')).toBe('Русский');
    });

    it('maps kz to kazakh name', () => {
        expect(getLocalizedName(names, 'kz')).toBe('Қазақша');
    });

    it('falls back to russian when translation missing', () => {
        expect(getLocalizedName({ name_ru: 'Русский' }, 'en')).toBe('Русский');
    });
});
