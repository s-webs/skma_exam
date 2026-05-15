import { useTranslation } from 'react-i18next';
import { router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import Flag from 'react-flagkit';

const languages = [
    { code: 'ru', label: 'RU', country: 'RU' },
    { code: 'kk', label: 'KZ', country: 'KZ' },
    { code: 'en', label: 'EN', country: 'GB' },
];

export default function LanguageSwitcher() {
    const { i18n } = useTranslation();

    const changeLanguage = (lng: string) => {
        i18n.changeLanguage(lng);
        localStorage.setItem('language', lng);

        // Сохраняем язык в сессии на сервере
        router.post(route('locale.set'), { locale: lng }, {
            preserveScroll: true,
            preserveState: true,
        });
    };

    return (
        <div className="flex gap-2">
            {languages.map((lang) => (
                <Button
                    key={lang.code}
                    variant={i18n.language === lang.code ? 'default' : 'outline'}
                    size="sm"
                    onClick={() => changeLanguage(lang.code)}
                    className="min-w-[70px]"
                >
                    <Flag country={lang.country} className="mr-1.5" style={{ width: '20px', height: '15px' }} />
                    {lang.label}
                </Button>
            ))}
        </div>
    );
}
