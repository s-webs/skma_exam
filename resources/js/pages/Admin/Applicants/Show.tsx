import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, FileText, Calendar, Mail, Phone, MapPin, GraduationCap, Languages, Trash2 } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

interface ExamAttempt {
    id: number;
    token: string;
    started_at: string | null;
    completed_at: string | null;
    date: string;
    exam: {
        id: number;
        name: string;
    };
    result: {
        id: number;
        score: number;
        passed: boolean;
    } | null;
}

interface Applicant {
    id: number;
    name: string;
    email: string;
    identifier: string;
    address: string;
    phone: string;
    graduate_organization: string;
    graduate_year: string;
    speciality: string;
    language: string;
    verified: boolean;
    telegram_token: string | null;
    document_front: string | null;
    document_back: string | null;
    diplom: string | null;
    certificate: string | null;
    photo: string | null;
    created_at: string;
    exam_attempts: ExamAttempt[];
}

interface ShowProps {
    applicant: Applicant;
}

export default function Show({ applicant }: ShowProps) {
    const handleDelete = () => {
        if (confirm('Вы уверены, что хотите удалить этого абитуриента?')) {
            router.delete(route('admin.applicants.destroy', applicant.id), {
                onSuccess: () => router.visit(route('admin.applicants.index')),
            });
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

    const getStatusBadge = (attempt: ExamAttempt) => {
        if (attempt.result) {
            return attempt.result.passed ? (
                <span className="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800">
                    Сдан ({attempt.result.score}%)
                </span>
            ) : (
                <span className="rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-800">
                    Не сдан ({attempt.result.score}%)
                </span>
            );
        } else if (attempt.completed_at) {
            return (
                <span className="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-800">
                    Завершен
                </span>
            );
        } else if (attempt.started_at) {
            return (
                <span className="rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-800">
                    В процессе
                </span>
            );
        } else {
            return (
                <span className="rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-800">
                    Не начат
                </span>
            );
        }
    };

    return (
        <AppLayout>
            <Head title={`Абитуриент: ${applicant.name}`} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="mb-6 flex items-center justify-between">
                        <Link href={route('admin.applicants.index')}>
                            <Button variant="ghost" size="sm">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                Назад к списку
                            </Button>
                        </Link>
                        <div className="flex gap-2">
                            <Link href={route('admin.applicants.edit', applicant.id)}>
                                <Button variant="outline">Редактировать</Button>
                            </Link>
                            <Button variant="destructive" onClick={handleDelete}>
                                <Trash2 className="mr-2 h-4 w-4" />
                                Удалить
                            </Button>
                        </div>
                    </div>

                    <div className="grid gap-6 md:grid-cols-3">
                        <div className="md:col-span-2 space-y-6">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Личная информация</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="grid gap-4 md:grid-cols-2">
                                        <div>
                                            <p className="text-sm text-muted-foreground">ФИО</p>
                                            <p className="font-medium">{applicant.name}</p>
                                        </div>
                                        <div>
                                            <p className="text-sm text-muted-foreground">ИИН</p>
                                            <p className="font-mono font-medium">{applicant.identifier}</p>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <Mail className="h-4 w-4 text-muted-foreground" />
                                            <div>
                                                <p className="text-sm text-muted-foreground">Email</p>
                                                <p className="font-medium">{applicant.email}</p>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <Phone className="h-4 w-4 text-muted-foreground" />
                                            <div>
                                                <p className="text-sm text-muted-foreground">Телефон</p>
                                                <p className="font-medium">{applicant.phone}</p>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-2 md:col-span-2">
                                            <MapPin className="h-4 w-4 text-muted-foreground" />
                                            <div>
                                                <p className="text-sm text-muted-foreground">Адрес</p>
                                                <p className="font-medium">{applicant.address}</p>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <Languages className="h-4 w-4 text-muted-foreground" />
                                            <div>
                                                <p className="text-sm text-muted-foreground">Язык экзамена</p>
                                                <p className="font-medium">{getLanguageName(applicant.language)}</p>
                                            </div>
                                        </div>
                                        <div>
                                            <p className="text-sm text-muted-foreground">Статус верификации</p>
                                            {applicant.verified ? (
                                                <span className="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                                    Верифицирован
                                                </span>
                                            ) : (
                                                <span className="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
                                                    Не верифицирован
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                    {applicant.telegram_token && (
                                        <div className="rounded-lg border bg-muted/50 p-3">
                                            <p className="text-sm text-muted-foreground">Telegram Token</p>
                                            <p className="mt-1 font-mono text-sm">{applicant.telegram_token}</p>
                                        </div>
                                    )}
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle>Образование</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="grid gap-4 md:grid-cols-2">
                                        <div className="flex items-center gap-2">
                                            <GraduationCap className="h-4 w-4 text-muted-foreground" />
                                            <div>
                                                <p className="text-sm text-muted-foreground">Учебное заведение</p>
                                                <p className="font-medium">{applicant.graduate_organization}</p>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <Calendar className="h-4 w-4 text-muted-foreground" />
                                            <div>
                                                <p className="text-sm text-muted-foreground">Год окончания</p>
                                                <p className="font-medium">{applicant.graduate_year}</p>
                                            </div>
                                        </div>
                                        <div className="md:col-span-2">
                                            <p className="text-sm text-muted-foreground">Специальность</p>
                                            <p className="font-medium">{applicant.speciality}</p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle>История экзаменов</CardTitle>
                                    <CardDescription>
                                        Всего попыток: {applicant.exam_attempts.length}
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    {applicant.exam_attempts.length === 0 ? (
                                        <p className="text-center text-muted-foreground py-8">
                                            Нет попыток экзаменов
                                        </p>
                                    ) : (
                                        <Table>
                                            <TableHeader>
                                                <TableRow>
                                                    <TableHead>Экзамен</TableHead>
                                                    <TableHead>Дата</TableHead>
                                                    <TableHead>Статус</TableHead>
                                                    <TableHead>Токен</TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                {applicant.exam_attempts.map((attempt) => (
                                                    <TableRow key={attempt.id}>
                                                        <TableCell className="font-medium">
                                                            {attempt.exam.name}
                                                        </TableCell>
                                                        <TableCell>
                                                            {new Date(attempt.date).toLocaleDateString('ru-RU')}
                                                        </TableCell>
                                                        <TableCell>{getStatusBadge(attempt)}</TableCell>
                                                        <TableCell className="font-mono text-sm">
                                                            {attempt.token}
                                                        </TableCell>
                                                    </TableRow>
                                                ))}
                                            </TableBody>
                                        </Table>
                                    )}
                                </CardContent>
                            </Card>
                        </div>

                        <div className="space-y-6">
                            {applicant.photo && (
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Фото</CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <img
                                            src={`/storage/${applicant.photo}`}
                                            alt={applicant.name}
                                            className="w-full rounded-lg border"
                                        />
                                    </CardContent>
                                </Card>
                            )}

                            <Card>
                                <CardHeader>
                                    <CardTitle>Документы</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    {applicant.document_front && (
                                        <div>
                                            <p className="mb-2 text-sm font-medium">Документ (лицевая)</p>
                                            <img
                                                src={`/storage/${applicant.document_front}`}
                                                alt="Document front"
                                                className="w-full rounded border"
                                            />
                                        </div>
                                    )}
                                    {applicant.document_back && (
                                        <div>
                                            <p className="mb-2 text-sm font-medium">Документ (обратная)</p>
                                            <img
                                                src={`/storage/${applicant.document_back}`}
                                                alt="Document back"
                                                className="w-full rounded border"
                                            />
                                        </div>
                                    )}
                                    {applicant.diplom && (
                                        <div>
                                            <p className="mb-2 text-sm font-medium">Диплом</p>
                                            <img
                                                src={`/storage/${applicant.diplom}`}
                                                alt="Diplom"
                                                className="w-full rounded border"
                                            />
                                        </div>
                                    )}
                                    {applicant.certificate && (
                                        <div>
                                            <p className="mb-2 text-sm font-medium">Сертификат</p>
                                            <img
                                                src={`/storage/${applicant.certificate}`}
                                                alt="Certificate"
                                                className="w-full rounded border"
                                            />
                                        </div>
                                    )}
                                    {!applicant.document_front &&
                                        !applicant.document_back &&
                                        !applicant.diplom &&
                                        !applicant.certificate && (
                                            <p className="text-center text-muted-foreground py-4">
                                                Документы не загружены
                                            </p>
                                        )}
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
