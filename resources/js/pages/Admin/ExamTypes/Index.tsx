import { Head, Link, router, usePage } from '@inertiajs/react';
import { Plus, Pencil, Trash2, Users, Eye } from 'lucide-react';
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
import { AdminLocalizedName } from '@/components/admin-localized-name';
import { RegistrationLinkDialog } from '@/components/registration-link-dialog';

interface Exam {
    id: number;
    name_ru: string;
    name_kk?: string | null;
    name_en?: string | null;
    language: string;
}

interface ExamType {
    id: number;
    name_ru: string;
    name_kk?: string | null;
    name_en?: string | null;
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

interface SharedAuth {
    auth: {
        isDeveloper: boolean;
        isRegistrator: boolean;
    };
}

export default function Index({ examTypes }: ExamTypesIndexProps) {
    const { t } = useTranslation();
    const { auth } = usePage<SharedAuth>().props;
    const isDeveloper = auth.isDeveloper;
    const isRegistrator = auth.isRegistrator;
    const canManageTypes = !isRegistrator;

    const handleDelete = (id: number, examsCount: number) => {
        const message = examsCount > 0
            ? t('examTypes.deleteConfirmWithExams', { count: examsCount })
            : t('examTypes.deleteConfirm');

        if (confirm(message)) {
            router.delete(route('admin.exam-types.destroy', id));
        }
    };

    return (
        <AppLayout>
            <Head title={t('examTypes.title')} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="mb-6 flex items-center justify-between">
                        <div>
                            <h2 className="text-3xl font-bold tracking-tight">{t('examTypes.title')}</h2>
                            <p className="text-muted-foreground mt-2">
                                {t('examTypes.description')}
                            </p>
                        </div>
                        {isDeveloper && (
                            <Link href={route('admin.exam-types.create')}>
                                <Button>
                                    <Plus className="mr-2 h-4 w-4" />
                                    {t('examTypes.addType')}
                                </Button>
                            </Link>
                        )}
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle>{t('examTypes.allTypes')}</CardTitle>
                            <CardDescription>
                                {t('examTypes.total', { count: examTypes.length })}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>{t('examTypes.name')}</TableHead>
                                        <TableHead>{t('examTypes.descriptionLabel')}</TableHead>
                                        <TableHead>{t('examTypes.examsCount')}</TableHead>
                                        <TableHead>{t('examTypes.status')}</TableHead>
                                        <TableHead>{t('examTypes.registration')}</TableHead>
                                        <TableHead>{t('examTypes.applicants')}</TableHead>
                                        <TableHead className="text-right">{t('examTypes.actions')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {examTypes.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={7} className="text-center">
                                                {t('examTypes.noTypes')}
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        examTypes.map((examType) => (
                                            <TableRow key={examType.id}>
                                                <TableCell className="font-medium">
                                                    <Link
                                                        href={route('admin.exam-types.show', examType.id)}
                                                        className="hover:underline"
                                                    >
                                                        <AdminLocalizedName names={examType} />
                                                    </Link>
                                                </TableCell>
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
                                                        {examType.is_active ? t('examTypes.active') : t('examTypes.inactive')}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    {examType.exams_count > 0 ? (
                                                        <RegistrationLinkDialog examType={examType} />
                                                    ) : (
                                                        <span className="text-sm text-muted-foreground">
                                                            {t('examTypes.noExams')}
                                                        </span>
                                                    )}
                                                </TableCell>
                                                <TableCell>
                                                    {examType.exams_count > 0 ? (
                                                        <Link href={route('admin.exam-types.applicants', examType.id)}>
                                                            <Button variant="outline" size="sm">
                                                                <Users className="mr-2 h-4 w-4" />
                                                                {t('examTypes.list')}
                                                            </Button>
                                                        </Link>
                                                    ) : (
                                                        <span className="text-sm text-muted-foreground">—</span>
                                                    )}
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex justify-end gap-2">
                                                        <Link href={route('admin.exam-types.show', examType.id)}>
                                                            <Button variant="outline" size="sm">
                                                                <Eye className="h-4 w-4" />
                                                            </Button>
                                                        </Link>
                                                        {canManageTypes && (
                                                            <>
                                                                <Link href={route('admin.exam-types.edit', examType.id)}>
                                                                    <Button variant="outline" size="sm">
                                                                        <Pencil className="h-4 w-4" />
                                                                    </Button>
                                                                </Link>
                                                                {isDeveloper && (
                                                                    <Button
                                                                        variant="outline"
                                                                        size="sm"
                                                                        onClick={() => handleDelete(examType.id, examType.exams_count)}
                                                                    >
                                                                        <Trash2 className="h-4 w-4 text-red-600" />
                                                                    </Button>
                                                                )}
                                                            </>
                                                        )}
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
