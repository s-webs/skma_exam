import { LocalizedNameFields } from '@/lib/localized-name';

interface AdminLocalizedNameProps {
    names: LocalizedNameFields;
}

export function AdminLocalizedName({ names }: AdminLocalizedNameProps) {
    const alternates = [names.name_kk, names.name_en].filter(Boolean);

    return (
        <div>
            <div className="font-medium">{names.name_ru}</div>
            {alternates.length > 0 && (
                <div className="text-sm text-muted-foreground">{alternates.join(' · ')}</div>
            )}
        </div>
    );
}
