import { Head } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { CheckCircle2, XCircle, Clock, FileDown } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useExamLocale } from '@/hooks/use-exam-locale';

interface CompleteProps {
    locale: string;
    reportUrl: string;
    resultsDeliveryMethod: 'telegram' | 'email';
    exam: { name: string };
    result: {
        passed: boolean;
        total_score: number;
        correct_answers: number;
        total_questions: number;
        passing_score: number;
        time_spent_seconds: number;
    };
}

export default function Complete({ locale, reportUrl, resultsDeliveryMethod, exam, result }: CompleteProps) {
    const { t } = useTranslation();
    useExamLocale(locale);

    const formatTime = (seconds: number): string => {
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        return `${t('publicExam.complete.timeMinutes', { count: m })} ${t('publicExam.complete.timeSeconds', { count: s })}`;
    };

    return (
        <>
            <Head title={t('publicExam.complete.pageTitle', { name: exam.name })} />

            <div className="min-h-dvh bg-gradient-to-br from-indigo-50 via-white to-blue-50 px-4 py-8 pb-safe">
                <div className="mx-auto max-w-lg">
                    <Card className="border-0 shadow-lg">
                        <CardHeader className="text-center">
                            <div
                                className={`mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full ${
                                    result.passed ? 'bg-green-100' : 'bg-red-100'
                                }`}
                            >
                                {result.passed ? (
                                    <CheckCircle2 className="h-10 w-10 text-green-600" />
                                ) : (
                                    <XCircle className="h-10 w-10 text-red-600" />
                                )}
                            </div>
                            <CardTitle className="text-xl">
                                {result.passed ? t('publicExam.complete.passed') : t('publicExam.complete.failed')}
                            </CardTitle>
                            <CardDescription>{exam.name}</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-2 gap-3 text-center">
                                <div className="rounded-xl bg-gray-50 p-4">
                                    <p className="text-2xl font-bold text-indigo-600">{result.total_score}%</p>
                                    <p className="text-xs text-gray-600">{t('publicExam.complete.result')}</p>
                                </div>
                                <div className="rounded-xl bg-gray-50 p-4">
                                    <p className="text-2xl font-bold text-gray-900">
                                        {result.correct_answers}/{result.total_questions}
                                    </p>
                                    <p className="text-xs text-gray-600">{t('publicExam.complete.correctAnswers')}</p>
                                </div>
                            </div>

                            <div className="flex items-center justify-center gap-2 text-sm text-gray-600">
                                <Clock className="h-4 w-4" />
                                {formatTime(result.time_spent_seconds)}
                            </div>

                            <p className="text-center text-sm text-muted-foreground">
                                {t('publicExam.complete.passingRequirement', { score: result.passing_score })}
                            </p>

                            <p className="text-center text-sm text-muted-foreground">
                                {resultsDeliveryMethod === 'telegram'
                                    ? t('publicExam.complete.reportTelegram')
                                    : t('publicExam.complete.reportEmail')}
                            </p>

                            <Button asChild className="h-12 w-full" size="lg">
                                <a href={reportUrl} target="_blank" rel="noopener noreferrer">
                                    <FileDown className="mr-2 h-4 w-4" />
                                    {t('publicExam.complete.downloadPdf')}
                                </a>
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}
