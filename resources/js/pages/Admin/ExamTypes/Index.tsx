import { Head, Link, router } from '@inertiajs/react';
import { Plus, Pencil, Trash2, Users } from 'lucide-react';
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
import { RegistrationLinkDialog } from '@/components/registration-link-dialog';

interface Exam {
    id: number;
    name: string;
    language: string;
}

interface ExamType {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    is_active: boolean;
    exams_count: number;
    exams: Exam[];
    created_at: string;
}

interface ExamTypesIndexProps {
    examTypes: ExamType[];
}

export default function Index({ examTypes }: ExamTypesIndexProps) {
    const handleDelete = (id: number) => {
        if (confirm('Вы уверены, что хотите удалить этот тип экзамена?')) {
            router.delete(route('admin.exam-types.destroy', id));
        }
    };

    return (
        <AppLayout>
            <Head title="Типы экзаменов" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="mb-6 flex items-center justify-between">
                        <div>
                            <h2 className="text-3xl font-bold tracking-tight">Типы экзаменов</h2>
                            <p className="text-muted-foreground mt-2">
                                Управление типами экзаменов (Магистратура, Ординатура и т.д.)
                            </p>
                        </div>
                        <Link href={route('admin.exam-types.create')}>
                            <Button>
                                <Plus className="mr-2 h-4 w-4" />
                                Добавить тип
                            </Button>
                        </Link>
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle>Все типы экзаменов</CardTitle>
                            <CardDescription>
                                Всего: {examTypes.length} типов
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Название</TableHead>
                                        <TableHead>Описание</TableHead>
                                        <TableHead>Экзаменов</TableHead>
                                        <TableHead>Статус</TableHead>
                                        <TableHead>Регистрация</TableHead>
                                        <TableHead>Абитуриенты</TableHead>
                                        <TableHead className="text-right">Действия</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {examTypes.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={7} className="text-center">
                                                Типы экзаменов не найдены
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        examTypes.map((examType) => (
                                            <TableRow key={examType.id}>
                                                <TableCell className="font-medium">{examType.name}</TableCell>
                                                <TableCell className="max-w-md truncate">
                                                    {examType.description || '—'}
                                                </TableCell>
                                                <TableCell>{examType.exams_count}</TableCell>
                                                <TableCell>
                                                    <Badge
                                                        className={
                                                            examType.is_active
                                                                ? 'bg-green-100 text-green-800'
                                                                : 'bg-gray-100 text-gray-800'
                                                        }
                                                    >
                                                        {examType.is_active ? 'Активен' : 'Неактивен'}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    {examType.exams_count > 0 ? (
                                                        <RegistrationLinkDialog examType={examType} />
                                                    ) : (
                                                        <span className="text-sm text-muted-foreground">
                                                            Нет экзаменов
                                                        </span>
                                                    )}
                                                </TableCell>
                                                <TableCell>
                                                    {examType.exams_count > 0 ? (
                                                        <Link href={route('admin.exam-types.applicants', examType.id)}>
                                                            <Button variant="outline" size="sm">
                                                                <Users className="mr-2 h-4 w-4" />
                                                                Список
                                                            </Button>
                                                        </Link>
                                                    ) : (
                                                        <span className="text-sm text-muted-foreground">—</span>
                                                    )}
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex justify-end gap-2">
                                                        <Link href={route('admin.exam-types.edit', examType.id)}>
                                                            <Button variant="outline" size="sm">
                                                                <Pencil className="h-4 w-4" />
                                                            </Button>
                                                        </Link>
                                                        <Button
                                                            variant="outline"
                                                            size="sm"
                                                            onClick={() => handleDelete(examType.id)}
                                                            disabled={examType.exams_count > 0}
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
