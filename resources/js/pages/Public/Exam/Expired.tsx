import { Head } from '@inertiajs/react';
import { TimerOff } from 'lucide-react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

interface ExpiredProps {
    exam: { name: string };
    attempt: { token: string };
}

export default function Expired({ exam }: ExpiredProps) {
    return (
        <>
            <Head title="Время истекло" />

            <div className="min-h-dvh bg-gradient-to-br from-indigo-50 via-white to-blue-50 px-4 py-8 pb-safe">
                <div className="mx-auto max-w-lg">
                    <Card className="border-0 shadow-lg">
                        <CardHeader className="text-center">
                            <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-amber-100">
                                <TimerOff className="h-10 w-10 text-amber-600" />
                            </div>
                            <CardTitle className="text-xl">Время экзамена истекло</CardTitle>
                            <CardDescription>{exam.name}</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <p className="text-center text-sm text-muted-foreground">
                                Отведённое время на прохождение закончилось. Если вы не завершили экзамен, обратитесь к
                                администратору.
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}
