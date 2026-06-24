import { Head } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { TimerOff } from 'lucide-react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useExamLocale } from '@/hooks/use-exam-locale';

interface ExpiredProps {
    locale: string;
    exam: { name: string };
    attempt: { token: string };
}

export default function Expired({ locale, exam }: ExpiredProps) {
    const { t } = useTranslation();
    useExamLocale(locale);

    return (
        <>
            <Head title={t('publicExam.expired.pageTitle')} />

            <div className="min-h-dvh bg-gradient-to-br from-indigo-50 via-white to-blue-50 px-4 py-8 pb-safe">
                <div className="mx-auto max-w-lg">
                    <Card className="border-0 shadow-lg">
                        <CardHeader className="text-center">
                            <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-amber-100">
                                <TimerOff className="h-10 w-10 text-amber-600" />
                            </div>
                            <CardTitle className="text-xl">{t('publicExam.expired.title')}</CardTitle>
                            <CardDescription>{exam.name}</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <p className="text-center text-sm text-muted-foreground">
                                {t('publicExam.expired.description')}
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}
