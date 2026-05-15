import { Head, useForm, Link } from '@inertiajs/react';
import { FormEvent, useState, useEffect } from 'react';
import { ArrowLeft, Plus, Trash2, Image as ImageIcon } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { toast } from 'sonner';

interface Answer {
    id?: number;
    content: string;
    image_path?: string | null;
    image?: File | null;
    is_correct: boolean;
}

interface Question {
    id: number;
    exam_id: number;
    content: string;
    image_path: string | null;
    explanation: string | null;
    is_active: boolean;
    answers: Answer[];
    exam: {
        id: number;
        name: string;
    };
}

interface EditProps {
    question: Question;
}

export default function Edit({ question }: EditProps) {
    const [answers, setAnswers] = useState<Answer[]>(question.answers);

    const { data, setData, post, processing, errors } = useForm({
        content: question.content,
        image: null as File | null,
        explanation: question.explanation || '',
        is_active: question.is_active,
        answers: answers,
        _method: 'PUT',
    });

    useEffect(() => {
        setData('answers', answers);
    }, [answers]);

    useEffect(() => {
        if (Object.keys(errors).length > 0) {
            Object.values(errors).forEach((error) => {
                toast.error(error, {
                    style: {
                        background: '#fef2f2',
                        border: '2px solid #fca5a5',
                        color: '#7f1d1d',
                    },
                });
            });
        }
    }, [errors]);

    const addAnswer = () => {
        if (answers.length < 6) {
            setAnswers([...answers, { content: '', is_correct: false }]);
        }
    };

    const removeAnswer = (index: number) => {
        if (answers.length > 2) {
            setAnswers(answers.filter((_, i) => i !== index));
        }
    };

    const updateAnswer = (index: number, field: 'content' | 'is_correct' | 'image', value: string | boolean | File | null) => {
        const newAnswers = [...answers];
        newAnswers[index] = { ...newAnswers[index], [field]: value };
        setAnswers(newAnswers);
    };

    const submit = (e: FormEvent) => {
        e.preventDefault();

        const formData = new FormData();
        formData.append('content', data.content);
        formData.append('explanation', data.explanation);
        formData.append('is_active', data.is_active ? '1' : '0');
        formData.append('_method', 'PUT');

        if (data.image) {
            formData.append('image', data.image);
        }

        answers.forEach((answer, index) => {
            formData.append(`answers[${index}][content]`, answer.content);
            formData.append(`answers[${index}][is_correct]`, answer.is_correct ? '1' : '0');
            if (answer.id) {
                formData.append(`answers[${index}][id]`, answer.id.toString());
            }
            if (answer.image) {
                formData.append(`answers[${index}][image]`, answer.image);
            }
        });

        post(route('admin.questions.update', question.id), {
            data: formData,
            forceFormData: true,
        });
    };

    return (
        <AppLayout>
            <Head title="Редактировать вопрос" />

            <div className="py-12">
                <div className="mx-auto max-w-3xl sm:px-6 lg:px-8">
                    <div className="mb-6">
                        <Link href={route('admin.exams.questions.index', question.exam_id)}>
                            <Button variant="ghost" size="sm">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                Назад к вопросам
                            </Button>
                        </Link>
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle>Редактировать вопрос</CardTitle>
                            <CardDescription>
                                Экзамен: {question.exam.name}
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
                                        rows={4}
                                        placeholder="Введите текст вопроса или загрузите изображение"
                                    />
                                    {errors.content && (
                                        <p className="text-sm text-red-600">{errors.content}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="image">Изображение вопроса (необязательно)</Label>
                                    {question.image_path && (
                                        <div className="mb-2">
                                            <img
                                                src={`/storage/questions/${question.image_path}`}
                                                alt="Question"
                                                className="max-w-xs rounded border"
                                            />
                                        </div>
                                    )}
                                    <div className="flex items-center gap-2">
                                        <input
                                            id="image"
                                            type="file"
                                            accept="image/*"
                                            onChange={(e) => setData('image', e.target.files?.[0] || null)}
                                            className="hidden"
                                        />
                                        <label
                                            htmlFor="image"
                                            className="cursor-pointer inline-flex items-center gap-2 rounded-md border border-input bg-background px-4 py-2 text-sm font-medium hover:bg-accent hover:text-accent-foreground"
                                        >
                                            <ImageIcon className="h-4 w-4" />
                                            {data.image ? data.image.name : 'Выбрать изображение'}
                                        </label>
                                        {data.image && (
                                            <span className="text-sm text-muted-foreground">
                                                Файл выбран
                                            </span>
                                        )}
                                    </div>
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
                                    />
                                    {errors.explanation && (
                                        <p className="text-sm text-red-600">{errors.explanation}</p>
                                    )}
                                </div>

                                <div className="flex items-center space-x-2">
                                    <Checkbox
                                        id="is_active"
                                        checked={data.is_active}
                                        onCheckedChange={(checked) => setData('is_active', checked as boolean)}
                                    />
                                    <Label htmlFor="is_active" className="cursor-pointer">
                                        Активен
                                    </Label>
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
                                        <div key={index} className="space-y-2 rounded-lg border p-4">
                                            {(answer.image_path || answer.image) && (
                                                <div className="mb-2">
                                                    <img
                                                        src={answer.image ? URL.createObjectURL(answer.image) : `/storage/answers/${answer.image_path}`}
                                                        alt={`Answer ${index + 1}`}
                                                        className="max-w-xs rounded border"
                                                    />
                                                </div>
                                            )}
                                            <div className="flex gap-2 items-center">
                                                <input
                                                    type="file"
                                                    accept="image/*"
                                                    onChange={(e) => updateAnswer(index, 'image', e.target.files?.[0] || null)}
                                                    className="hidden"
                                                    id={`answer-image-${index}`}
                                                />
                                                <label
                                                    htmlFor={`answer-image-${index}`}
                                                    className="cursor-pointer rounded-md border border-input bg-background p-2 hover:bg-accent hover:text-accent-foreground"
                                                    title="Добавить изображение"
                                                >
                                                    <ImageIcon className="h-5 w-5" />
                                                </label>
                                                <Input
                                                    value={answer.content}
                                                    onChange={(e) => updateAnswer(index, 'content', e.target.value)}
                                                    placeholder={`Вариант ответа ${index + 1} (текст или изображение)`}
                                                    className="flex-1"
                                                />
                                                <div className="flex items-center space-x-2">
                                                    <Checkbox
                                                        id={`correct-${index}`}
                                                        checked={answer.is_correct}
                                                        onCheckedChange={(checked) =>
                                                            updateAnswer(index, 'is_correct', checked as boolean)
                                                        }
                                                    />
                                                    <Label
                                                        htmlFor={`correct-${index}`}
                                                        className="cursor-pointer text-sm whitespace-nowrap"
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
                                                    >
                                                        <Trash2 className="h-4 w-4 text-red-600" />
                                                    </Button>
                                                )}
                                            </div>
                                        </div>
                                    ))}
                                    {errors.answers && (
                                        <p className="text-sm text-red-600">{errors.answers}</p>
                                    )}
                                </div>

                                <div className="flex justify-end gap-4">
                                    <Link href={route('admin.exams.questions.index', question.exam_id)}>
                                        <Button type="button" variant="outline">
                                            Отмена
                                        </Button>
                                    </Link>
                                    <Button type="submit" disabled={processing}>
                                        {processing ? 'Сохранение...' : 'Сохранить изменения'}
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
