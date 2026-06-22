import { Head, Link, router } from '@inertiajs/react';
import { Plus, Pencil, Trash2, Settings, Users } from 'lucide-react';
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
import { AdminLocalizedName } from '@/components/admin-localized-name';
import { Badge } from '@/components/ui/badge';

interface ExamType {
    id: number;
    name_ru: string;
    name_kk?: string | null;
    name_en?: string | null;
}

interface User {
    id: number;
    name: string;
}

interface Exam {
    id: number;
    name_ru: string;
    name_kk?: string | null;
    name_en?: string | null;
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
    const { t } = useTranslation();

    const handleDelete = (id: number) => {
        if (confirm(t('exams.deleteConfirm'))) {
            router.delete(route('admin.exams.destroy', id));
        }
    };

    const getLanguageName = (lang: string) => {
        const languages: Record<string, string> = {
            kz: t('exams.kazakh'),
            ru: t('exams.russian'),
            en: t('exams.english'),
        };
        return languages[lang] || lang;
    };

    return (
        <AppLayout>
            <Head title={t('exams.title')} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="mb-6 flex items-center justify-between">
                        <div>
                            <h2 className="text-3xl font-bold tracking-tight">{t('exams.title')}</h2>
                            <p className="text-muted-foreground mt-2">
                                {t('exams.description')}
                            </p>
                        </div>
                        <Link href={route('admin.exams.create')}>
                            <Button>
                                <Plus className="mr-2 h-4 w-4" />
                                {t('exams.createExam')}
                            </Button>
                        </Link>
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle>{t('exams.allExams')}</CardTitle>
                            <CardDescription>
                                {t('exams.total', { count: exams.length })}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>{t('exams.name')}</TableHead>
                                        <TableHead>{t('exams.type')}</TableHead>
                                        <TableHead>{t('exams.language')}</TableHead>
                                        <TableHead>{t('exams.time')}</TableHead>
                                        <TableHead>{t('exams.questions')}</TableHead>
                                        <TableHead>{t('exams.passingScore')}</TableHead>
                                        <TableHead>{t('exams.status')}</TableHead>
                                        <TableHead>{t('exams.applicants')}</TableHead>
                                        <TableHead className="text-right">{t('exams.actions')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {exams.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={9} className="text-center">
                                                {t('exams.noExams')}
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        exams.map((exam) => (
                                            <TableRow key={exam.id}>
                                                <TableCell className="font-medium">
                                                    <AdminLocalizedName names={exam} />
                                                </TableCell>
                                                <TableCell>
                                                    <AdminLocalizedName names={exam.exam_type} />
                                                </TableCell>
                                                <TableCell>{getLanguageName(exam.language)}</TableCell>
                                                <TableCell>{exam.duration_minutes} {t('exams.minutes')}</TableCell>
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
                                                        {exam.is_active ? t('exams.active') : t('exams.inactive')}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    <Link href={route('admin.exams.applicants', exam.id)}>
                                                        <Button variant="outline" size="sm">
                                                            <Users className="mr-2 h-4 w-4" />
                                                            {t('exams.list')}
                                                        </Button>
                                                    </Link>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex justify-end gap-2">
                                                        <Link href={route('admin.exams.questions.index', exam.id)}>
                                                            <Button variant="outline" size="sm" title={t('exams.questionsTitle')}>
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
