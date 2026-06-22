import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowLeft, CheckCircle, XCircle, Eye, Trash2 } from 'lucide-react';
import { router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useBulkApproval } from '@/hooks/use-bulk-approval';
import { useTranslation } from 'react-i18next';

import { RegistrationLinkDialog } from '@/components/registration-link-dialog';

interface ExamType {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    exams: Exam[];
}

interface Exam {
    id: number;
    name: string;
}

interface ApplicantRow {
    id: number;
    name: string;
    identifier: string;
}

interface ExamRegistrationRow {
    attempt_id: number | null;
    registration_id: number;
    status: string | null;
    approved: boolean;
    approved_at: string | null;
    approved_by_user: {
        id: number;
        name: string;
    } | null;
    is_repeat_registration: boolean;
    applicant: ApplicantRow | null;
    exam: Exam | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedRegistrations {
    data: unknown[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface ApplicantsProps {
    examType: ExamType;
    registrations: PaginatedRegistrations;
    rows: ExamRegistrationRow[];
}

interface SharedAuth {
    auth: {
        isRegistrator: boolean;
    };
}

export default function Applicants({ examType, registrations, rows }: ApplicantsProps) {
    const { t } = useTranslation();
    const { errors, flash, auth } = usePage<{
        errors: { approve?: string };
        flash: { success?: string; bulk_approve_errors?: string[] };
    } & SharedAuth>().props;
    const isRegistrator = auth.isRegistrator;
    const {
        selected,
        pendingIds,
        allPendingSelected,
        selectedPendingCount,
        toggle,
        toggleAllPending,
        bulkApprove,
    } = useBulkApproval(rows);

    const handleDeleteAttempt = (attemptId: number) => {
        if (confirm('Вы уверены, что хотите удалить эту попытку?')) {
            router.delete(route('admin.exam-attempts.destroy', attemptId));
        }
    };

    const handleApprove = (registrationId: number) => {
        router.post(route('admin.exam-registrations.approve', registrationId));
    };

    const reviewUrl = (registrationId: number) =>
        `${route('admin.exam-registrations.review', registrationId)}?source=exam-type`;

    const handleUnapprove = (registrationId: number) => {
        if (confirm('Вы уверены, что хотите отменить одобрение?')) {
            router.post(route('admin.exam-registrations.unapprove', registrationId));
        }
    };

    const rowKey = (row: ExamRegistrationRow) =>
        row.attempt_id ? `attempt-${row.attempt_id}` : `reg-${row.registration_id}`;

    return (
        <AppLayout>
            <Head title={`Попытки - ${examType.name}`} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="mb-6 flex flex-wrap items-center justify-between gap-4">
                        <Link href={route('admin.exam-types.index')}>
                            <Button variant="ghost" size="sm">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                Назад к типам экзаменов
                            </Button>
                        </Link>
                        <div className="flex flex-wrap gap-2">
                            <Link href={route('admin.exam-types.show', examType.id)}>
                                <Button variant="outline" size="sm">
                                    Тип экзамена
                                </Button>
                            </Link>
                            {examType.exams.length > 0 && (
                                <RegistrationLinkDialog examType={examType} />
                            )}
                        </div>
                    </div>

                    <div className="mb-6">
                        <h2 className="text-3xl font-bold tracking-tight">
                            <Link
                                href={route('admin.exam-types.show', examType.id)}
                                className="hover:underline"
                            >
                                {examType.name}
                            </Link>
                        </h2>
                        <p className="text-muted-foreground mt-2">Попытки и записи на экзамены этого типа</p>
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

                    {flash?.bulk_approve_errors && flash.bulk_approve_errors.length > 0 && (
                        <div className="mb-4 rounded-lg border border-orange-200 bg-orange-50 p-3 text-sm text-orange-900">
                            <p className="font-medium">Ошибки при массовом одобрении:</p>
                            <ul className="mt-2 list-disc space-y-1 pl-5">
                                {flash.bulk_approve_errors.map((error) => (
                                    <li key={error}>{error}</li>
                                ))}
                            </ul>
                        </div>
                    )}

                    <Card>
                        <CardHeader>
                            <div className="flex flex-wrap items-center justify-between gap-4">
                                <div>
                                    <CardTitle>Попытки / записи на экзамен</CardTitle>
                                    <CardDescription>Всего: {registrations.total} записей</CardDescription>
                                </div>
                                {pendingIds.length > 0 && (
                                    <Button
                                        disabled={selectedPendingCount === 0}
                                        onClick={bulkApprove}
                                    >
                                        <CheckCircle className="mr-2 h-4 w-4" />
                                        Одобрить выбранные ({selectedPendingCount})
                                    </Button>
                                )}
                            </div>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        {pendingIds.length > 0 && (
                                            <TableHead className="w-10">
                                                <Checkbox
                                                    checked={allPendingSelected}
                                                    onCheckedChange={toggleAllPending}
                                                    aria-label="Выбрать все неодобренные"
                                                />
                                            </TableHead>
                                        )}
                                        <TableHead>ID</TableHead>
                                        <TableHead>ФИО</TableHead>
                                        <TableHead>ИИН</TableHead>
                                        <TableHead>Экзамен</TableHead>
                                        <TableHead>Одобрение</TableHead>
                                        <TableHead className="text-right">Действия</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {rows.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={pendingIds.length > 0 ? 7 : 6} className="text-center text-muted-foreground">
                                                Нет зарегистрированных записей
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        rows.map((row) => {
                                            const applicant = row.applicant;

                                            if (!applicant) {
                                                return null;
                                            }

                                            return (
                                                <TableRow key={rowKey(row)}>
                                                    {pendingIds.length > 0 && (
                                                        <TableCell>
                                                            {!row.approved ? (
                                                                <Checkbox
                                                                    checked={selected.includes(row.registration_id)}
                                                                    onCheckedChange={() => toggle(row.registration_id)}
                                                                    aria-label={`Выбрать ${applicant.name}`}
                                                                />
                                                            ) : null}
                                                        </TableCell>
                                                    )}
                                                    <TableCell className="font-mono text-muted-foreground">
                                                        {row.attempt_id ?? '—'}
                                                    </TableCell>
                                                    <TableCell className="font-medium">
                                                        <div className="flex flex-col gap-1">
                                                            <span>{applicant.name}</span>
                                                            {row.is_repeat_registration && (
                                                                <span className="w-fit rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">
                                                                    {t('applicants.repeatAttempt')}
                                                                </span>
                                                            )}
                                                        </div>
                                                    </TableCell>
                                                    <TableCell className="font-mono">{applicant.identifier}</TableCell>
                                                    <TableCell>{row.exam?.name ?? '—'}</TableCell>
                                                    <TableCell>
                                                        {row.approved ? (
                                                            <div className="flex flex-col gap-1">
                                                                <span className="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800">
                                                                    Одобрен
                                                                </span>
                                                                {row.approved_by_user && (
                                                                    <span className="text-xs text-muted-foreground">
                                                                        {row.approved_by_user.name}
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
                                                            {!isRegistrator && !row.approved && (
                                                                <Button
                                                                    variant="ghost"
                                                                    size="sm"
                                                                    onClick={() => handleApprove(row.registration_id)}
                                                                    title="Одобрить без просмотра"
                                                                >
                                                                    <CheckCircle className="h-4 w-4 text-green-600" />
                                                                </Button>
                                                            )}
                                                            {isRegistrator && !row.approved ? (
                                                                <Link href={reviewUrl(row.registration_id)}>
                                                                    <Button variant="outline" size="sm">
                                                                        Проверить
                                                                    </Button>
                                                                </Link>
                                                            ) : (
                                                                <Link href={reviewUrl(row.registration_id)}>
                                                                    <Button variant="ghost" size="sm" title="Просмотр">
                                                                        <Eye className="h-4 w-4" />
                                                                    </Button>
                                                                </Link>
                                                            )}
                                                            {row.approved && !isRegistrator && (
                                                                <Button
                                                                    variant="ghost"
                                                                    size="sm"
                                                                    onClick={() => handleUnapprove(row.registration_id)}
                                                                    title="Отменить одобрение"
                                                                >
                                                                    <XCircle className="h-4 w-4 text-orange-600" />
                                                                </Button>
                                                            )}
                                                            {row.attempt_id !== null && !isRegistrator && (
                                                                <Button
                                                                    variant="ghost"
                                                                    size="sm"
                                                                    onClick={() => handleDeleteAttempt(row.attempt_id!)}
                                                                    title="Удалить попытку"
                                                                >
                                                                    <Trash2 className="h-4 w-4 text-red-600" />
                                                                </Button>
                                                            )}
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
