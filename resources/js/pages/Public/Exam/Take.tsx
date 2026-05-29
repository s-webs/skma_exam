import { Head, router } from '@inertiajs/react';
import { useCallback, useEffect, useRef, useState } from 'react';
import { ChevronLeft, ChevronRight, Loader2 } from 'lucide-react';
import { ExamTimer } from '@/components/exam/ExamTimer';
import { ExamQuestion, QuestionView } from '@/components/exam/QuestionView';
import { Button } from '@/components/ui/button';
import { examJson } from '@/lib/exam-api';

interface TakeProps {
    attempt: {
        token: string;
        expires_at: string;
        started_at: string;
    };
    exam: { name: string; duration_minutes: number };
    questions: ExamQuestion[];
    savedAnswers: Record<string, number>;
}

export default function Take({ attempt, exam, questions, savedAnswers }: TakeProps) {
    const initialAnswers: Record<number, number> = {};
    for (const [qId, aId] of Object.entries(savedAnswers)) {
        initialAnswers[Number(qId)] = aId;
    }

    const [currentIndex, setCurrentIndex] = useState(0);
    const [answers, setAnswers] = useState<Record<number, number>>(initialAnswers);
    const [saving, setSaving] = useState(false);
    const [finishing, setFinishing] = useState(false);
    const finishCalledRef = useRef(false);

    const currentQuestion = questions[currentIndex];
    const total = questions.length;
    const answeredCount = Object.keys(answers).length;
    const progress = total > 0 ? (answeredCount / total) * 100 : 0;

    const handleFinish = useCallback(async () => {
        if (finishCalledRef.current) {
            return;
        }
        finishCalledRef.current = true;
        setFinishing(true);

        const result = await examJson<{ redirect: string }>(
            route('public.exam.finish', attempt.token),
            { method: 'POST', body: '{}' },
        );

        if (result.ok && result.data.redirect) {
            router.visit(result.data.redirect);
            return;
        }

        finishCalledRef.current = false;
        setFinishing(false);
        alert(result.ok ? 'Ошибка завершения' : result.message);
    }, [attempt.token]);

    useEffect(() => {
        const warn = (e: BeforeUnloadEvent) => {
            e.preventDefault();
            e.returnValue = '';
        };
        window.addEventListener('beforeunload', warn);
        return () => window.removeEventListener('beforeunload', warn);
    }, []);

    const saveAnswer = async (questionId: number, answerId: number) => {
        setAnswers((prev) => ({ ...prev, [questionId]: answerId }));
        setSaving(true);
        await examJson(route('public.exam.answers', attempt.token), {
            method: 'POST',
            body: JSON.stringify({ question_id: questionId, answer_id: answerId }),
        });
        setSaving(false);
    };

    const goNext = () => {
        if (currentIndex < total - 1) {
            setCurrentIndex((i) => i + 1);
        }
    };

    const goPrev = () => {
        if (currentIndex > 0) {
            setCurrentIndex((i) => i - 1);
        }
    };

    const isLast = currentIndex === total - 1;
    const selectedId = currentQuestion ? (answers[currentQuestion.id] ?? null) : null;

    return (
        <>
            <Head title={`Экзамен — ${exam.name}`} />

            <div className="flex min-h-dvh flex-col bg-gradient-to-br from-indigo-50 via-white to-blue-50">
                <header className="sticky top-0 z-10 border-b border-indigo-100 bg-indigo-50/95 px-4 py-3 backdrop-blur">
                    <div className="mx-auto flex max-w-lg items-center justify-between gap-2">
                        <h1 className="truncate text-sm font-semibold text-gray-900">{exam.name}</h1>
                        <ExamTimer expiresAt={attempt.expires_at} onExpire={handleFinish} />
                    </div>
                    <div className="mx-auto mt-2 max-w-lg">
                        <div className="flex justify-between text-xs text-gray-600">
                            <span>
                                {currentIndex + 1} / {total}
                            </span>
                            <span>Отвечено: {answeredCount}</span>
                        </div>
                        <div className="mt-1 h-1.5 overflow-hidden rounded-full bg-white">
                            <div
                                className="h-full bg-indigo-600 transition-all duration-300"
                                style={{ width: `${progress}%` }}
                            />
                        </div>
                    </div>
                </header>

                <main className="flex-1 overflow-y-auto px-4 py-4 pb-28">
                    <div className="mx-auto max-w-lg">
                        {currentQuestion && (
                            <QuestionView
                                question={currentQuestion}
                                selectedAnswerId={selectedId}
                                onSelectAnswer={(answerId) => saveAnswer(currentQuestion.id, answerId)}
                            />
                        )}
                        {saving && (
                            <p className="mt-2 flex items-center gap-1 text-xs text-gray-500">
                                <Loader2 className="h-3 w-3 animate-spin" />
                                Сохранение…
                            </p>
                        )}
                    </div>
                </main>

                <footer className="fixed inset-x-0 bottom-0 z-10 border-t border-gray-200 bg-white/95 px-4 py-3 pb-safe backdrop-blur">
                    <div className="mx-auto flex max-w-lg gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            className="h-12 flex-1"
                            onClick={goPrev}
                            disabled={currentIndex === 0 || finishing}
                        >
                            <ChevronLeft className="mr-1 h-4 w-4" />
                            Назад
                        </Button>

                        {isLast ? (
                            <Button
                                type="button"
                                className="h-12 flex-[2]"
                                onClick={handleFinish}
                                disabled={finishing || answeredCount < total}
                            >
                                {finishing ? (
                                    <Loader2 className="h-4 w-4 animate-spin" />
                                ) : (
                                    'Завершить'
                                )}
                            </Button>
                        ) : (
                            <Button
                                type="button"
                                className="h-12 flex-[2]"
                                onClick={goNext}
                                disabled={!selectedId || finishing}
                            >
                                Далее
                                <ChevronRight className="ml-1 h-4 w-4" />
                            </Button>
                        )}
                    </div>
                </footer>
            </div>
        </>
    );
}
