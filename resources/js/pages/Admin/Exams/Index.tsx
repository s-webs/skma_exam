import { Head, Link, router } from '@inertiajs/react';
import { Plus, Pencil, Trash2, Settings, Users } from 'lucide-react';
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

interface ExamType {
    id: number;
    name: string;
}

interface User {
    id: number;
    name: string;
}

interface Exam {
    id: number;
    name: string;
    exam_type: ExamType;
    language: string;
    duration_minutes: number;
    questions_count: number;
    passing_score: number;
    max_attempts: number | null;
    is_active: boolean;
    questions_count_total: number;
    created_by: User;
    created_at: string;
}

interface ExamsIndexProps {
    exams: Exam[];
}

export default function Index({ exams }: ExamsIndexProps) {
    const handleDelete = (id: number) => {
        if (confirm('Вы уверены, что хотите удалить этот экзамен?')) {
            router.delete(route('admin.exams.destroy', id));
        }
    };

    const getLanguageName = (lang: string) => {
        const languages: Record<string, string> = {
            kz: 'Казахский',
            ru: 'Русский',
            en: 'Английский',
        };
        return languages[lang] || lang;
    };

    return (
        <AppLayout>
            <Head title="Экзамены" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="mb-6 flex items-center justify-between">
                        <div>
                            <h2 className="text-3xl font-bold tracking-tight">Экзамены</h2>
                            <p className="text-muted-foreground mt-2">
                                Управление экзаменами и их настройками
                            </p>
                        </div>
                        <Link href={route('admin.exams.create')}>
                            <Button>
                                <Plus className="mr-2 h-4 w-4" />
                                Создать экзамен
                            </Button>
                        </Link>
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle>Все экзамены</CardTitle>
                            <CardDescription>
                                Всего: {exams.length} экзаменов
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Название</TableHead>
                                        <TableHead>Тип</TableHead>
                                        <TableHead>Язык</TableHead>
                                        <TableHead>Время</TableHead>
                                        <TableHead>Вопросов</TableHead>
                                        <TableHead>Проходной балл</TableHead>
                                        <TableHead>Статус</TableHead>
                                        <TableHead>Абитуриенты</TableHead>
                                        <TableHead className="text-right">Действия</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {exams.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={9} className="text-center">
                                                Экзамены не найдены
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        exams.map((exam) => (
                                            <TableRow key={exam.id}>
                                                <TableCell className="font-medium">{exam.name}</TableCell>
                                                <TableCell>{exam.exam_type.name}</TableCell>
                                                <TableCell>{getLanguageName(exam.language)}</TableCell>
                                                <TableCell>{exam.duration_minutes} мин</TableCell>
                                                <TableCell>
                                                    {exam.questions_count_total} / {exam.questions_count}
                                                </TableCell>
                                                <TableCell>{exam.passing_score}</TableCell>
                                                <TableCell>
                                                    <Badge
                                                        className={
                                                            exam.is_active
                                                                ? 'bg-green-100 text-green-800'
                                                                : 'bg-gray-100 text-gray-800'
                                                        }
                                                    >
                                                        {exam.is_active ? 'Активен' : 'Неактивен'}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    <Link href={route('admin.exams.applicants', exam.id)}>
                                                        <Button variant="outline" size="sm">
                                                            <Users className="mr-2 h-4 w-4" />
                                                            Список
                                                        </Button>
                                                    </Link>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex justify-end gap-2">
                                                        <Link href={route('admin.exams.questions.index', exam.id)}>
                                                            <Button variant="outline" size="sm" title="Вопросы">
                                                                <Settings className="h-4 w-4" />
                                                            </Button>
                                                        </Link>
                                                        <Link href={route('admin.exams.edit', exam.id)}>
                                                            <Button variant="outline" size="sm">
                                                                <Pencil className="h-4 w-4" />
                                                            </Button>
                                                        </Link>
                                                        <Button
                                                            variant="outline"
                                                            size="sm"
                                                            onClick={() => handleDelete(exam.id)}
                                                        >
                                                            <Trash2 className="h-4 w-4 text-red-600" />
                                                        </Button>
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))
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
