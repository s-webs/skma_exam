import { Head, useForm, Link } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import { ArrowLeft, Plus, Trash2 } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';

interface Exam {
    id: number;
    name: string;
}

interface Answer {
    content: string;
    is_correct: boolean;
}

interface CreateProps {
    exam: Exam;
}

export default function Create({ exam }: CreateProps) {
    const [answers, setAnswers] = useState<Answer[]>([
        { content: '', is_correct: false },
        { content: '', is_correct: false },
    ]);

    const { data, setData, post, processing, errors } = useForm({
        content: '',
        image: null as File | null,
        explanation: '',
        answers: answers,
    });

    const addAnswer = () => {
        if (answers.length < 6) {
            const newAnswers = [...answers, { content: '', is_correct: false }];
            setAnswers(newAnswers);
            setData('answers', newAnswers);
        }
    };

    const removeAnswer = (index: number) => {
        if (answers.length > 2) {
            const newAnswers = answers.filter((_, i) => i !== index);
            setAnswers(newAnswers);
            setData('answers', newAnswers);
        }
    };

    const updateAnswer = (index: number, field: 'content' | 'is_correct', value: string | boolean) => {
        const newAnswers = [...answers];
        newAnswers[index] = { ...newAnswers[index], [field]: value };
        setAnswers(newAnswers);
        setData('answers', newAnswers);
    };

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post(route('admin.exams.questions.store', exam.id));
    };

    return (
        <AppLayout>
            <Head title="Создать вопрос" />

            <div className="py-12">
                <div className="mx-auto max-w-3xl sm:px-6 lg:px-8">
                    <div className="mb-6">
                        <Link href={route('admin.exams.questions.index', exam.id)}>
                            <Button variant="ghost" size="sm">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                Назад к вопросам
                            </Button>
                        </Link>
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle>Создать новый вопрос</CardTitle>
                            <CardDescription>
                                Добавьте вопрос для экзамена: {exam.name}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={submit} className="space-y-6">
                                <div className="space-y-2">
                                    <Label htmlFor="content">Текст вопроса</Label>
                                    <Textarea
                                        id="content"
                                        value={data.content}
                                        onChange={(e) => setData('content', e.target.value)}
                                        required
                                        rows={4}
                                        placeholder="Введите текст вопроса"
                                    />
                                    {errors.content && (
                                        <p className="text-sm text-red-600">{errors.content}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="image">Изображение (опционально)</Label>
                                    <Input
                                        id="image"
                                        type="file"
                                        accept="image/*"
                                        onChange={(e) => setData('image', e.target.files?.[0] || null)}
                                    />
                                    {errors.image && (
                                        <p className="text-sm text-red-600">{errors.image}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="explanation">Объяснение (опционально)</Label>
                                    <Textarea
                                        id="explanation"
                                        value={data.explanation}
                                        onChange={(e) => setData('explanation', e.target.value)}
                                        rows={3}
                                        placeholder="Объяснение правильного ответа"
                                    />
                                    {errors.explanation && (
                                        <p className="text-sm text-red-600">{errors.explanation}</p>
                                    )}
                                </div>

                                <div className="space-y-4">
                                    <div className="flex items-center justify-between">
                                        <Label>Варианты ответов</Label>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            onClick={addAnswer}
                                            disabled={answers.length >= 6}
                                        >
                                            <Plus className="mr-2 h-4 w-4" />
                                            Добавить ответ
                                        </Button>
                                    </div>

                                    {answers.map((answer, index) => (
                                        <div key={index} className="flex gap-2 items-start">
                                            <div className="flex-1 space-y-2">
                                                <Input
                                                    value={answer.content}
                                                    onChange={(e) => updateAnswer(index, 'content', e.target.value)}
                                                    placeholder={`Вариант ответа ${index + 1}`}
                                                    required
                                                />
                                            </div>
                                            <div className="flex items-center space-x-2 pt-2">
                                                <Checkbox
                                                    id={`correct-${index}`}
                                                    checked={answer.is_correct}
                                                    onCheckedChange={(checked) =>
                                                        updateAnswer(index, 'is_correct', checked as boolean)
                                                    }
                                                />
                                                <Label
                                                    htmlFor={`correct-${index}`}
                                                    className="cursor-pointer text-sm"
                                                >
                                                    Правильный
                                                </Label>
                                            </div>
                                            {answers.length > 2 && (
                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() => removeAnswer(index)}
                                                    className="mt-0"
                                                >
                                                    <Trash2 className="h-4 w-4 text-red-600" />
                                                </Button>
                                            )}
                                        </div>
                                    ))}
                                    {errors.answers && (
                                        <p className="text-sm text-red-600">{errors.answers}</p>
                                    )}
                                </div>

                                <div className="flex justify-end gap-4">
                                    <Link href={route('admin.exams.questions.index', exam.id)}>
                                        <Button type="button" variant="outline">
                                            Отмена
                                        </Button>
                                    </Link>
                                    <Button type="submit" disabled={processing}>
                                        {processing ? 'Создание...' : 'Создать вопрос'}
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
