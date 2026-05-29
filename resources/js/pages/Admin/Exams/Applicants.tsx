import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowLeft, CheckCircle, XCircle, Eye, Pencil, Trash2 } from 'lucide-react';
import { router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

interface ExamType {
    id: number;
    name: string;
}

interface Exam {
    id: number;
    name: string;
    language: string;
    exam_type: ExamType;
}

interface Applicant {
    id: number;
    name: string;
    email: string;
    identifier: string;
    phone: string;
    language: string;
}

interface ExamRegistration {
    id: number;
    approved: boolean;
    approved_at: string | null;
    approved_by_user: {
        id: number;
        name: string;
    } | null;
    applicant: Applicant;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedRegistrations {
    data: ExamRegistration[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface ApplicantsProps {
    exam: Exam;
    registrations: PaginatedRegistrations;
}

export default function Applicants({ exam, registrations }: ApplicantsProps) {
    const { errors, flash } = usePage<{
        errors: { approve?: string };
        flash: { success?: string };
    }>().props;

    const handleDelete = (applicantId: number) => {
        if (confirm('Вы уверены, что хотите удалить этого абитуриента?')) {
            router.delete(route('admin.applicants.destroy', applicantId));
        }
    };

    const handleApprove = (registrationId: number) => {
        router.post(route('admin.exam-registrations.approve', registrationId));
    };

    const handleUnapprove = (registrationId: number) => {
        if (confirm('Вы уверены, что хотите отменить одобрение?')) {
            router.post(route('admin.exam-registrations.unapprove', registrationId));
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
            <Head title={`Абитуриенты - ${exam.name}`} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="mb-6">
                        <Link href={route('admin.exams.index')}>
                            <Button variant="ghost" size="sm">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                Назад к экзаменам
                            </Button>
                        </Link>
                    </div>

                    <div className="mb-6">
                        <h2 className="text-3xl font-bold tracking-tight">{exam.name}</h2>
                        <p className="text-muted-foreground mt-2">
                            Записи на экзамен ({getLanguageName(exam.language)})
                        </p>
                    </div>

                    {flash?.success && (
                        <div className="mb-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800">
                            {flash.success}
                        </div>
                    )}

                    {errors?.approve && (
                        <div className="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                            {errors.approve}
                        </div>
                    )}

                    <Card>
                        <CardHeader>
                            <CardTitle>Список абитуриентов</CardTitle>
                            <CardDescription>
                                Всего: {registrations.total} записей
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>ID</TableHead>
                                        <TableHead>Имя</TableHead>
                                        <TableHead>ИИН</TableHead>
                                        <TableHead>Email</TableHead>
                                        <TableHead>Телефон</TableHead>
                                        <TableHead>Язык</TableHead>
                                        <TableHead>Одобрение</TableHead>
                                        <TableHead className="text-right">Действия</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {registrations.data.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={8} className="text-center text-muted-foreground">
                                                Нет зарегистрированных абитуриентов
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        registrations.data.map((registration) => {
                                            const applicant = registration.applicant;

                                            return (
                                                <TableRow key={registration.id}>
                                                    <TableCell className="text-muted-foreground">
                                                        {applicant.id}
                                                    </TableCell>
                                                    <TableCell className="font-medium">
                                                        {applicant.name}
                                                    </TableCell>
                                                    <TableCell className="font-mono">
                                                        {applicant.identifier}
                                                    </TableCell>
                                                    <TableCell>{applicant.email}</TableCell>
                                                    <TableCell>{applicant.phone}</TableCell>
                                                    <TableCell>
                                                        {getLanguageName(applicant.language)}
                                                    </TableCell>
                                                    <TableCell>
                                                        {registration.approved ? (
                                                            <div className="flex flex-col gap-1">
                                                                <span className="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800">
                                                                    Одобрен
                                                                </span>
                                                                {registration.approved_by_user && (
                                                                    <span className="text-xs text-muted-foreground">
                                                                        {registration.approved_by_user.name}
                                                                    </span>
                                                                )}
                                                            </div>
                                                        ) : (
                                                            <span className="rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-800">
                                                                Не одобрен
                                                            </span>
                                                        )}
                                                    </TableCell>
                                                    <TableCell className="text-right">
                                                        <div className="flex justify-end gap-2">
                                                            {!registration.approved ? (
                                                                <Button
                                                                    variant="ghost"
                                                                    size="sm"
                                                                    onClick={() => handleApprove(registration.id)}
                                                                    title="Одобрить"
                                                                >
                                                                    <CheckCircle className="h-4 w-4 text-green-600" />
                                                                </Button>
                                                            ) : (
                                                                <Button
                                                                    variant="ghost"
                                                                    size="sm"
                                                                    onClick={() => handleUnapprove(registration.id)}
                                                                    title="Отменить одобрение"
                                                                >
                                                                    <XCircle className="h-4 w-4 text-orange-600" />
                                                                </Button>
                                                            )}
                                                            <Link href={route('admin.applicants.show', applicant.id)}>
                                                                <Button variant="ghost" size="sm">
                                                                    <Eye className="h-4 w-4" />
                                                                </Button>
                                                            </Link>
                                                            <Link href={route('admin.applicants.edit', applicant.id)}>
                                                                <Button variant="ghost" size="sm">
                                                                    <Pencil className="h-4 w-4" />
                                                                </Button>
                                                            </Link>
                                                            <Button
                                                                variant="ghost"
                                                                size="sm"
                                                                onClick={() => handleDelete(applicant.id)}
                                                            >
                                                                <Trash2 className="h-4 w-4 text-red-600" />
                                                            </Button>
                                                        </div>
                                                    </TableCell>
                                                </TableRow>
                                            );
                                        })
                                    )}
                                </TableBody>
                            </Table>

                            {registrations.last_page > 1 && (
                                <div className="mt-4 flex items-center justify-center gap-2">
                                    {registrations.links.map((link, index) => (
                                        <Button
                                            key={index}
                                            variant={link.active ? 'default' : 'outline'}
                                            size="sm"
                                            disabled={!link.url}
                                            onClick={() => link.url && router.visit(link.url)}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    ))}
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
