export interface LocalizedNameFields {
    name_ru: string;
    name_kk?: string | null;
    name_en?: string | null;
}

export function normalizeNameLocale(locale: string): string {
    return locale === 'kz' ? 'kk' : locale;
}

export function getLocalizedName(
    names: LocalizedNameFields,
    locale: string,
): string {
    const normalized = normalizeNameLocale(locale);

    if (normalized === 'kk' && names.name_kk) {
        return names.name_kk;
    }

    if (normalized === 'en' && names.name_en) {
        return names.name_en;
    }

    return names.name_ru;
}
