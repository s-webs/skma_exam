import { Head, Link } from '@inertiajs/react';
import { CheckCircle2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

interface Applicant {
    id: number;
    name: string;
    email: string;
}

interface SuccessProps {
    applicant: Applicant;
}

export default function Success({ applicant }: SuccessProps) {
    return (
        <>
            <Head title="Регистрация завершена" />

            <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-12 px-4 sm:px-6 lg:px-8">
                <div className="mx-auto max-w-2xl">
                    <Card>
                        <CardHeader className="text-center">
                            <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                                <CheckCircle2 className="h-10 w-10 text-green-600" />
                            </div>
                            <CardTitle className="text-2xl">Регистрация успешно завершена!</CardTitle>
                            <CardDescription>
                                Спасибо за регистрацию, {applicant.name}
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-6">
                            <div className="rounded-lg border bg-blue-50 p-4">
                                <h3 className="font-semibold text-blue-900 mb-2">Что дальше?</h3>
                                <ul className="space-y-2 text-sm text-blue-800">
                                    <li>• Ваша заявка отправлена на проверку</li>
                                    <li>• Регистратор проверит ваши данные и документы</li>
                                    <li>• После одобрения вы получите ссылку на экзамен в Telegram</li>
                                    <li>• Время на прохождение определяется настройками экзамена в админке</li>
                                </ul>
                            </div>

                            <div className="rounded-lg border border-yellow-200 bg-yellow-50 p-4">
                                <p className="text-sm text-yellow-800">
                                    <strong>Обратите внимание:</strong> Проверка заявки может занять некоторое время.
                                    Следите за сообщениями от Telegram-бота.
                                </p>
                            </div>

                            <div className="text-center pt-4">
                                <Link href="/">
                                    <Button variant="outline">
                                        Вернуться на главную
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
