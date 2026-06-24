import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowLeft, Calendar, CheckCircle, XCircle, Eye, Trash2, FileX, FileDown, Search } from 'lucide-react';
import { router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import { useMemo } from 'react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useBulkApproval } from '@/hooks/use-bulk-approval';
import { useBulkDateChange } from '@/hooks/use-bulk-date-change';
import { usePermissions } from '@/hooks/use-permissions';
import { useTranslation } from 'react-i18next';

import { RegistrationLinkDialog } from '@/components/registration-link-dialog';

interface ExamType {
    id: number;
    name_ru: string;
    name_kk?: string | null;
    name_en?: string | null;
    slug: string;
    description: string | null;
    exams: Exam[];
}

interface Exam {
    id: number;
    name_ru: string;
    name_kk?: string | null;
    name_en?: string | null;
}

interface ApplicantRow {
    id: number;
    name: string;
    identifier: string;
}

interface ExamResultSummary {
    passed: boolean;
    total_score: number;
    correct_answers: number;
    total_questions: number;
}

interface ExamRegistrationRow {
    attempt_id: number | null;
    registration_id: number;
    date: string | null;
    status: string | null;
    approved: boolean;
    approved_at: string | null;
    approved_by_user: {
        id: number;
        name: string;
    } | null;
    is_repeat_registration: boolean;
    result: ExamResultSummary | null;
    report_url: string | null;
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
    filters: {
        identifier: string;
    };
}

export default function Applicants({ examType, registrations, rows, filters }: ApplicantsProps) {
    const { t } = useTranslation();
    const { errors, flash } = usePage<{
        errors: { approve?: string; date?: string };
        flash: { success?: string; bulk_approve_errors?: string[]; bulk_date_errors?: string[] };
    }>().props;
    const { can } = usePermissions();
    const canApprove = can('exam-registrations.approve');
    const canUnapprove = can('exam-registrations.unapprove');
    const canEditDate = can('exam-registrations.edit-date');
    const canViewRegistration = can('exam-registrations.view');
    const canDeleteAttempt = can('exam-attempts.delete');
    const canDeleteRegistration = can('exam-registrations.delete');
    const showCheckboxColumn = canApprove || canEditDate;
    const {
        selected,
        setSelected,
        pendingIds,
        selectedPendingCount,
        toggle,
        bulkApprove,
        clearSelection,
    } = useBulkApproval(rows);
    const { bulkUpdateDate } = useBulkDateChange(selected, clearSelection);
    const [identifierSearch, setIdentifierSearch] = useState(filters.identifier);

    const handleIdentifierSearch = (event: FormEvent) => {
        event.preventDefault();

        router.get(
            route('admin.exam-types.applicants', examType.id),
            { identifier: identifierSearch },
            { preserveState: true, replace: true },
        );
    };

    const clearIdentifierSearch = () => {
        setIdentifierSearch('');
        router.get(route('admin.exam-types.applicants', examType.id), {}, { preserveState: true, replace: true });
    };

    const checkableIds = useMemo(() => {
        const ids = new Set<number>();

        rows.forEach((row) => {
            if ((!row.approved && canApprove) || canEditDate) {
                ids.add(row.registration_id);
            }
        });

        return [...ids];
    }, [rows, canApprove, canEditDate]);

    const allCheckableSelected =
        checkableIds.length > 0 && checkableIds.every((id) => selected.includes(id));

    const toggleAllCheckable = () => {
        setSelected(allCheckableSelected ? [] : checkableIds);
    };

    const rowIsCheckable = (row: ExamRegistrationRow) =>
        (!row.approved && canApprove) || canEditDate;

    const formatDate = (date: string | null) =>
        date ? new Date(date).toLocaleDateString('ru-RU') : '—';

    const handleDateChange = (registrationId: number, date: string) => {
        router.patch(route('admin.exam-registrations.update-date', registrationId), { date });
    };

    const handleDeleteAttempt = (attemptId: number) => {
        if (confirm(t('applicants.deleteAttempt') + '?')) {
            router.delete(route('admin.exam-attempts.destroy', attemptId));
        }
    };

    const handleDeleteRegistration = (registrationId: number) => {
        if (confirm(t('applicants.deleteRegistrationConfirm'))) {
            router.delete(route('admin.exam-registrations.destroy', registrationId));
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
            <Head title={`Попытки - ${examType.name_ru}`} />

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
                                {examType.name_ru}
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

                    {errors?.date && (
                        <div className="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                            {errors.date}
                        </div>
                    )}

                    {flash?.bulk_date_errors && flash.bulk_date_errors.length > 0 && (
                        <div className="mb-4 rounded-lg border border-orange-200 bg-orange-50 p-3 text-sm text-orange-900">
                            <p className="font-medium">Ошибки при массовом изменении даты:</p>
                            <ul className="mt-2 list-disc space-y-1 pl-5">
                                {flash.bulk_date_errors.map((error) => (
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
                                <div className="flex flex-wrap gap-2">
                                    {canApprove && pendingIds.length > 0 && (
                                        <Button
                                            disabled={selectedPendingCount === 0}
                                            onClick={bulkApprove}
                                        >
                                            <CheckCircle className="mr-2 h-4 w-4" />
                                            Одобрить выбранные ({selectedPendingCount})
                                        </Button>
                                    )}
                                    {canEditDate && checkableIds.length > 0 && (
                                        <Button
                                            variant="outline"
                                            disabled={selected.length === 0}
                                            onClick={bulkUpdateDate}
                                        >
                                            <Calendar className="mr-2 h-4 w-4" />
                                            Изменить дату выбранных ({selected.length})
                                        </Button>
                                    )}
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <form
                                onSubmit={handleIdentifierSearch}
                                className="mb-4 flex flex-wrap items-end gap-2"
                            >
                                <div className="min-w-[220px] flex-1 space-y-1">
                                    <label htmlFor="identifier-search" className="text-sm font-medium">
                                        Поиск по ИИН
                                    </label>
                                    <Input
                                        id="identifier-search"
                                        value={identifierSearch}
                                        onChange={(event) => setIdentifierSearch(event.target.value)}
                                        placeholder="Введите ИИН"
                                        inputMode="numeric"
                                        className="font-mono"
                                    />
                                </div>
                                <Button type="submit" variant="outline">
                                    <Search className="mr-2 h-4 w-4" />
                                    Найти
                                </Button>
                                {filters.identifier && (
                                    <Button type="button" variant="ghost" onClick={clearIdentifierSearch}>
                                        Сбросить
                                    </Button>
                                )}
                            </form>

                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        {showCheckboxColumn && checkableIds.length > 0 && (
                                            <TableHead className="w-10">
                                                <Checkbox
                                                    checked={allCheckableSelected}
                                                    onCheckedChange={toggleAllCheckable}
                                                    aria-label="Выбрать все"
                                                />
                                            </TableHead>
                                        )}
                                        <TableHead>ID</TableHead>
                                        <TableHead>ФИО</TableHead>
                                        <TableHead>ИИН</TableHead>
                                        <TableHead>Экзамен</TableHead>
                                        <TableHead>Дата</TableHead>
                                        <TableHead>Одобрение</TableHead>
                                        <TableHead>Результат</TableHead>
                                        <TableHead className="text-right">Действия</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {rows.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={showCheckboxColumn && checkableIds.length > 0 ? 9 : 8} className="text-center text-muted-foreground">
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
                                                <TableRow
                                                    key={rowKey(row)}
                                                    className={row.result ? 'bg-lime-50 hover:bg-lime-50' : undefined}
                                                >
                                                    {showCheckboxColumn && checkableIds.length > 0 && (
                                                        <TableCell>
                                                            {rowIsCheckable(row) ? (
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
                                                        {canEditDate ? (
                                                            <Input
                                                                type="date"
                                                                className="w-36"
                                                                value={row.date ?? ''}
                                                                onChange={(event) =>
                                                                    handleDateChange(
                                                                        row.registration_id,
                                                                        event.target.value,
                                                                    )
                                                                }
                                                            />
                                                        ) : (
                                                            formatDate(row.date)
                                                        )}
                                                    </TableCell>
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
                                                    <TableCell>
                                                        {row.result ? (
                                                            <div className="flex flex-col gap-2">
                                                                {row.result.passed ? (
                                                                    <span className="w-fit rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800">
                                                                        Сдан ({row.result.total_score}%)
                                                                    </span>
                                                                ) : (
                                                                    <span className="w-fit rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-800">
                                                                        Не сдан ({row.result.total_score}%)
                                                                    </span>
                                                                )}
                                                                {row.report_url && (
                                                                    <a
                                                                        href={row.report_url}
                                                                        target="_blank"
                                                                        rel="noopener noreferrer"
                                                                        className="inline-flex w-fit items-center gap-1 text-xs font-medium text-indigo-700 hover:underline"
                                                                    >
                                                                        <FileDown className="h-3.5 w-3.5" />
                                                                        PDF-ведомость
                                                                    </a>
                                                                )}
                                                            </div>
                                                        ) : (
                                                            '—'
                                                        )}
                                                    </TableCell>
                                                    <TableCell className="text-right">
                                                        <div className="flex justify-end gap-2">
                                                            {canApprove && !row.approved && (
                                                                <Button
                                                                    variant="ghost"
                                                                    size="sm"
                                                                    onClick={() => handleApprove(row.registration_id)}
                                                                    title="Одобрить без просмотра"
                                                                >
                                                                    <CheckCircle className="h-4 w-4 text-green-600" />
                                                                </Button>
                                                            )}
                                                            {canUnapprove && row.approved && (
                                                                <Button
                                                                    variant="ghost"
                                                                    size="sm"
                                                                    onClick={() => handleUnapprove(row.registration_id)}
                                                                    title="Отменить одобрение"
                                                                >
                                                                    <XCircle className="h-4 w-4 text-orange-600" />
                                                                </Button>
                                                            )}
                                                            {canViewRegistration && (
                                                                <Link href={reviewUrl(row.registration_id)}>
                                                                    <Button variant="ghost" size="sm" title="Просмотр">
                                                                        <Eye className="h-4 w-4" />
                                                                    </Button>
                                                                </Link>
                                                            )}
                                                            {canDeleteRegistration && (
                                                                <Button
                                                                    variant="ghost"
                                                                    size="sm"
                                                                    onClick={() => handleDeleteRegistration(row.registration_id)}
                                                                    title={t('applicants.deleteRegistration')}
                                                                >
                                                                    <FileX className="h-4 w-4 text-red-600" />
                                                                </Button>
                                                            )}
                                                            {canDeleteAttempt && row.attempt_id !== null && (
                                                                <Button
                                                                    variant="ghost"
                                                                    size="sm"
                                                                    onClick={() => handleDeleteAttempt(row.attempt_id!)}
                                                                    title={t('applicants.deleteAttempt')}
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
