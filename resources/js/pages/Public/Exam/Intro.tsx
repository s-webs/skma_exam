import { Head, router } from '@inertiajs/react';
import { FormEvent } from 'react';
import { Clock, FileQuestion, User } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

interface IntroProps {
    attempt: { token: string; status: string };
    exam: {
        name: string;
        description: string | null;
        duration_minutes: number;
        questions_count: number;
    };
    applicant: { name: string };
}

export default function Intro({ attempt, exam, applicant }: IntroProps) {
    const start = (e: FormEvent) => {
        e.preventDefault();
        router.post(route('public.exam.start', attempt.token));
    };

    return (
        <>
            <Head title={`Экзамен — ${exam.name}`} />

            <div className="min-h-dvh bg-gradient-to-br from-indigo-50 via-white to-blue-50 px-4 py-6 pb-safe">
                <div className="mx-auto max-w-lg">
                    <Card className="border-0 shadow-lg">
                        <CardHeader className="text-center pb-2">
                            <CardTitle className="text-xl sm:text-2xl">{exam.name}</CardTitle>
                            <CardDescription className="flex items-center justify-center gap-1">
                                <User className="h-4 w-4" />
                                {applicant.name}
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-6">
                            {exam.description && (
                                <p className="text-sm text-muted-foreground text-center">{exam.description}</p>
                            )}

                            <ul className="space-y-3 rounded-xl bg-indigo-50 p-4 text-sm text-indigo-900">
                                <li className="flex items-center gap-3">
                                    <FileQuestion className="h-5 w-5 shrink-0" />
                                    <span>{exam.questions_count} вопросов</span>
                                </li>
                                <li className="flex items-center gap-3">
                                    <Clock className="h-5 w-5 shrink-0" />
                                    <span>{exam.duration_minutes} минут на прохождение</span>
                                </li>
                            </ul>

                            <div className="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                                После нажатия «Начать экзамен» запустится таймер. Убедитесь, что у вас стабильное
                                интернет-соединение и достаточно времени.
                            </div>

                            <form onSubmit={start}>
                                <Button type="submit" className="h-12 w-full text-base" size="lg">
                                    Начать экзамен
                                </Button>
                            </form>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}
