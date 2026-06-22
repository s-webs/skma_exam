import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { LocalizedNameFields as LocalizedNameValues } from '@/lib/localized-name';
import { useTranslation } from 'react-i18next';

interface LocalizedNameFieldsProps {
    values: LocalizedNameValues;
    onChange: (field: keyof LocalizedNameValues, value: string) => void;
    errors?: Partial<Record<keyof LocalizedNameValues, string>>;
    idPrefix?: string;
    autoFocus?: boolean;
}

export function LocalizedNameFields({
    values,
    onChange,
    errors = {},
    idPrefix = 'name',
    autoFocus = false,
}: LocalizedNameFieldsProps) {
    const { t } = useTranslation();

    return (
        <fieldset className="space-y-4 rounded-lg border p-4">
            <legend className="px-1 text-sm font-medium">{t('localizedName.sectionTitle')}</legend>
            <div className="space-y-2">
                <Label htmlFor={`${idPrefix}-ru`}>{t('localizedName.nameRu')}</Label>
                <Input
                    id={`${idPrefix}-ru`}
                    type="text"
                    value={values.name_ru}
                    onChange={(e) => onChange('name_ru', e.target.value)}
                    required
                    autoFocus={autoFocus}
                    placeholder={t('localizedName.nameRuPlaceholder')}
                />
                {errors.name_ru && <p className="text-sm text-red-600">{errors.name_ru}</p>}
            </div>

            <div className="space-y-2">
                <Label htmlFor={`${idPrefix}-kk`}>{t('localizedName.nameKk')}</Label>
                <Input
                    id={`${idPrefix}-kk`}
                    type="text"
                    value={values.name_kk ?? ''}
                    onChange={(e) => onChange('name_kk', e.target.value)}
                    placeholder={t('localizedName.nameKkPlaceholder')}
                />
                {errors.name_kk && <p className="text-sm text-red-600">{errors.name_kk}</p>}
            </div>

            <div className="space-y-2">
                <Label htmlFor={`${idPrefix}-en`}>{t('localizedName.nameEn')}</Label>
                <Input
                    id={`${idPrefix}-en`}
                    type="text"
                    value={values.name_en ?? ''}
                    onChange={(e) => onChange('name_en', e.target.value)}
                    placeholder={t('localizedName.nameEnPlaceholder')}
                />
                {errors.name_en && <p className="text-sm text-red-600">{errors.name_en}</p>}
            </div>
        </fieldset>
    );
}
