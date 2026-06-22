import { Head, Link } from '@inertiajs/react';
import { CheckCircle2 } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import LanguageSwitcher from '@/components/language-switcher';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

interface Applicant {
    id: number;
    name: string;
    email: string;
}

interface SuccessProps {
    applicant: Applicant;
    deliveryMethod?: 'telegram' | 'email';
}

export default function Success({ applicant, deliveryMethod = 'telegram' }: SuccessProps) {
    const { t } = useTranslation();
    const usesEmail = deliveryMethod === 'email';

    return (
        <>
            <Head title={t('publicRegistration.success.pageTitle')} />

            <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-12 px-4 sm:px-6 lg:px-8">
                <div className="mx-auto mb-6 flex max-w-2xl justify-end">
                    <LanguageSwitcher />
                </div>
                <div className="mx-auto max-w-2xl">
                    <Card>
                        <CardHeader className="text-center">
                            <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                                <CheckCircle2 className="h-10 w-10 text-green-600" />
                            </div>
                            <CardTitle className="text-2xl">{t('publicRegistration.success.title')}</CardTitle>
                            <CardDescription>
                                {t('publicRegistration.success.thanks', { name: applicant.name })}
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-6">
                            <div className="rounded-lg border bg-blue-50 p-4">
                                <h3 className="font-semibold text-blue-900 mb-2">{t('publicRegistration.success.whatNext')}</h3>
                                <ul className="space-y-2 text-sm text-blue-800">
                                    <li>• {t('publicRegistration.success.step1')}</li>
                                    <li>• {t('publicRegistration.success.step2')}</li>
                                    <li>
                                        • {t('publicRegistration.success.step3', {
                                            channel: usesEmail
                                                ? t('publicRegistration.success.viaEmail')
                                                : t('publicRegistration.success.viaTelegram'),
                                        })}
                                    </li>
                                    <li>• {t('publicRegistration.success.step4')}</li>
                                </ul>
                            </div>

                            <div className="rounded-lg border border-yellow-200 bg-yellow-50 p-4">
                                <p className="text-sm text-yellow-800">
                                    {t('publicRegistration.success.notice')}
                                    {usesEmail
                                        ? t('publicRegistration.success.watchEmail', { email: applicant.email })
                                        : t('publicRegistration.success.watchTelegram')}
                                </p>
                            </div>

                            <div className="text-center pt-4">
                                <Link href="/">
                                    <Button variant="outline">
                                        {t('publicRegistration.success.backHome')}
                                    </Button>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}
