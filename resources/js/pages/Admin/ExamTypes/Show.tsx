import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowLeft, BookOpen, Users } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { RegistrationLinkDialog } from '@/components/registration-link-dialog';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

interface Exam {
    id: number;
    name: string;
    language: string;
    is_active: boolean;
    questions_count: number;
}

interface ExamType {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    is_active: boolean;
    exams: Exam[];
}

interface ShowProps {
    examType: ExamType;
}

interface SharedAuth {
    auth: {
        isDeveloper: boolean;
        isRegistrator: boolean;
    };
}

export default function Show({ examType }: ShowProps) {
    const { t } = useTranslation();
    const { auth } = usePage<SharedAuth>().props;
    const canEdit = !auth.isRegistrator;

    return (
        <AppLayout>
            <Head title={examType.name} />

            <div className="py-12">
                <div className="mx-auto max-w-5xl sm:px-6 lg:px-8">
                    <div className="mb-6 flex items-center justify-between">
                        <Link href={route('admin.exam-types.index')}>
                            <Button variant="ghost" size="sm">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                {t('examTypes.backToTypes')}
                            </Button>
                        </Link>
                        <div className="flex gap-2">
                            {examType.exams.length > 0 && (
                                <RegistrationLinkDialog examType={examType} />
                            )}
                            <Link href={route('admin.exam-types.applicants', examType.id)}>
                                <Button variant="outline">
                                    <Users className="mr-2 h-4 w-4" />
                                    {t('examTypes.applicants')}
                                </Button>
                            </Link>
                            {canEdit && (
                                <Link href={route('admin.exam-types.edit', examType.id)}>
                                    <Button>{t('examTypes.editTitle')}</Button>
                                </Link>
                            )}
                        </div>
                    </div>

                    <Card className="mb-6">
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <CardTitle>{examType.name}</CardTitle>
                                <Badge
                                    className={
                                        examType.is_active
                                            ? 'bg-green-100 text-green-800'
                                            : 'bg-gray-100 text-gray-800'
                                    }
                                >
                                    {examType.is_active ? t('examTypes.active') : t('examTypes.inactive')}
                                </Badge>
                            </div>
                            {examType.description && (
                                <CardDescription>{examType.description}</CardDescription>
                            )}
                        </CardHeader>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>{t('examTypes.examsInType')}</CardTitle>
                            <CardDescription>{t('examTypes.examsInTypeDesc')}</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>{t('exams.name')}</TableHead>
                                        <TableHead>{t('exams.language')}</TableHead>
                                        <TableHead>{t('exams.questions')}</TableHead>
                                        <TableHead>{t('exams.status')}</TableHead>
                                        <TableHead className="text-right">{t('examTypes.actions')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {examType.exams.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={5} className="text-center">
                                                {t('examTypes.noExams')}
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        examType.exams.map((exam) => (
                                            <TableRow key={exam.id}>
                                                <TableCell className="font-medium">{exam.name}</TableCell>
                                                <TableCell>{exam.language}</TableCell>
                                                <TableCell>{exam.questions_count}</TableCell>
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
                                                <TableCell className="text-right">
                                                    <Link href={route('admin.exams.questions.index', exam.id)}>
                                                        <Button variant="outline" size="sm">
                                                            <BookOpen className="mr-2 h-4 w-4" />
                                                            {t('examTypes.viewQuestions')}
                                                        </Button>
                                                    </Link>
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
