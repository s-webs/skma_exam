import { Head, Link, router } from '@inertiajs/react';
import { Plus, Pencil, Trash2, ArrowLeft } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';

interface Answer {
    id: number;
    content: string;
    is_correct: boolean;
}

interface Question {
    id: number;
    content: string;
    image_path: string | null;
    is_active: boolean;
    answers_count: number;
    answers: Answer[];
    created_at: string;
}

import { getLocalizedName, LocalizedNameFields } from '@/lib/localized-name';

interface Exam extends LocalizedNameFields {
    id: number;
    language?: string;
}

interface QuestionsIndexProps {
    exam: Exam;
    questions: Question[];
    canManageQuestions?: boolean;
    backUrl?: string;
}

export default function Index({ exam, questions, canManageQuestions = true, backUrl }: QuestionsIndexProps) {
    const { t } = useTranslation();
    const listBackUrl = backUrl ?? route('admin.exams.index');

    const handleDelete = (id: number) => {
        if (confirm(t('questions.deleteConfirm'))) {
            router.delete(route('admin.questions.destroy', id));
        }
    };

    return (
        <AppLayout>
            <Head title={t('questions.titleWithExam', { name: getLocalizedName(exam, exam.language ?? 'ru') })} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="mb-6">
                        <Link href={listBackUrl}>
                            <Button variant="ghost" size="sm">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                {t('questions.backToExams')}
                            </Button>
                        </Link>
                    </div>

                    <div className="mb-6 flex items-center justify-between">
                        <div>
                            <h2 className="text-3xl font-bold tracking-tight">{t('questions.title')}</h2>
                            <p className="text-muted-foreground mt-2">
                                {getLocalizedName(exam, exam.language ?? 'ru')}
                            </p>
                        </div>
                        {canManageQuestions && (
                            <Link href={route('admin.exams.questions.create', exam.id)}>
                                <Button>
                                    <Plus className="mr-2 h-4 w-4" />
                                    {t('questions.addQuestion')}
                                </Button>
                            </Link>
                        )}
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle>{t('questions.allQuestions')}</CardTitle>
                            <CardDescription>
                                {t('questions.total', { count: questions.length })}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead className="w-16">{t('questions.number')}</TableHead>
                                        <TableHead>{t('questions.question')}</TableHead>
                                        <TableHead>{t('questions.answers')}</TableHead>
                                        <TableHead>{t('questions.correctAnswers')}</TableHead>
                                        <TableHead>{t('questions.status')}</TableHead>
                                        {canManageQuestions && (
                                            <TableHead className="text-right">{t('questions.actions')}</TableHead>
                                        )}
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {questions.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={canManageQuestions ? 6 : 5} className="text-center">
                                                {t('questions.noQuestions')}
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        questions.map((question, index) => {
                                            const correctAnswers = question.answers.filter(a => a.is_correct).length;
                                            return (
                                                <TableRow key={question.id}>
                                                    <TableCell className="font-mono text-muted-foreground">
                                                        {index + 1}
                                                    </TableCell>
                                                    <TableCell className="max-w-md">
                                                        <div className="truncate font-medium">
                                                            {question.content}
                                                        </div>
                                                        {question.image_path && (
                                                            <span className="text-xs text-muted-foreground">
                                                                {t('questions.withImage')}
                                                            </span>
                                                        )}
                                                    </TableCell>
                                                    <TableCell>{question.answers_count}</TableCell>
                                                    <TableCell>{correctAnswers}</TableCell>
                                                    <TableCell>
                                                        <Badge
                                                            className={
                                                                question.is_active
                                                                    ? 'bg-green-100 text-green-800'
                                                                    : 'bg-gray-100 text-gray-800'
                                                            }
                                                        >
                                                            {question.is_active ? t('questions.active') : t('questions.inactive')}
                                                        </Badge>
                                                    </TableCell>
                                                    {canManageQuestions && (
                                                        <TableCell className="text-right">
                                                            <div className="flex justify-end gap-2">
                                                                <Link href={route('admin.questions.edit', question.id)}>
                                                                    <Button variant="outline" size="sm">
                                                                        <Pencil className="h-4 w-4" />
                                                                    </Button>
                                                                </Link>
                                                                <Button
                                                                    variant="outline"
                                                                    size="sm"
                                                                    onClick={() => handleDelete(question.id)}
                                                                >
                                                                    <Trash2 className="h-4 w-4 text-red-600" />
                                                                </Button>
                                                            </div>
                                                        </TableCell>
                                                    )}
                                                </TableRow>
                                            );
                                        })
                                    )}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
