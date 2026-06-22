import { Head, Link, router, usePage } from '@inertiajs/react';
import {
    ArrowLeft,
    Calendar,
    CheckCircle,
    GraduationCap,
    Languages,
    Mail,
    MapPin,
    Phone,
    XCircle,
} from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { getLocalizedName, LocalizedNameFields } from '@/lib/localized-name';

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
    document_front: string | null;
    document_back: string | null;
    diplom: string | null;
    certificate: string | null;
    photo: string | null;
    created_at: string;
}

interface ReviewProps {
    registration: {
        id: number;
        approved: boolean;
        approved_at: string | null;
        approved_by_user: {
            id: number;
            name: string;
        } | null;
    };
    applicant: Applicant;
    exam: LocalizedNameFields & {
        id: number;
        language: string;
    };
    examType: LocalizedNameFields & {
        id: number;
    };
    canApprove: boolean;
    canUnapprove: boolean;
    backUrl: string;
}

export default function Review({
    registration,
    applicant,
    exam,
    examType,
    canApprove,
    canUnapprove,
    backUrl,
}: ReviewProps) {
    const { errors, flash } = usePage<{
        errors: { approve?: string };
        flash: { success?: string };
    }>().props;

    const getLanguageName = (lang: string) => {
        const languages: Record<string, string> = {
            kz: 'Казахский',
            ru: 'Русский',
            en: 'Английский',
        };
        return languages[lang] || lang;
    };

    const handleApprove = () => {
        if (confirm('Одобрить запись после проверки профиля и документов?')) {
            router.post(route('admin.exam-registrations.approve', registration.id));
        }
    };

    const handleUnapprove = () => {
        if (confirm('Вы уверены, что хотите отменить одобрение?')) {
            router.post(route('admin.exam-registrations.unapprove', registration.id));
        }
    };

    return (
        <AppLayout>
            <Head title={`Проверка: ${applicant.name}`} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="mb-6 flex flex-wrap items-center justify-between gap-4">
                        <Link href={backUrl}>
                            <Button variant="ghost" size="sm">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                Назад к списку
                            </Button>
                        </Link>

                        <div className="flex gap-2">
                            {canApprove && (
                                <Button onClick={handleApprove}>
                                    <CheckCircle className="mr-2 h-4 w-4" />
                                    Одобрить запись
                                </Button>
                            )}
                            {canUnapprove && (
                                <Button variant="outline" onClick={handleUnapprove}>
                                    <XCircle className="mr-2 h-4 w-4" />
                                    Отменить одобрение
                                </Button>
                            )}
                        </div>
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

                    <div className="mb-6">
                        <h2 className="text-3xl font-bold tracking-tight">{applicant.name}</h2>
                        <p className="text-muted-foreground mt-2">
                            {getLocalizedName(examType, 'ru')} — {getLocalizedName(exam, exam.language)}
                        </p>
                    </div>

                    <Card className="mb-6">
                        <CardHeader>
                            <CardTitle>Статус записи</CardTitle>
                        </CardHeader>
                        <CardContent>
                            {registration.approved ? (
                                <div className="flex flex-col gap-1">
                                    <span className="inline-flex w-fit rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                        Одобрен
                                    </span>
                                    {registration.approved_by_user && (
                                        <span className="text-sm text-muted-foreground">
                                            {registration.approved_by_user.name}
                                            {registration.approved_at &&
                                                ` · ${new Date(registration.approved_at).toLocaleString('ru-RU')}`}
                                        </span>
                                    )}
                                </div>
                            ) : (
                                <span className="inline-flex w-fit rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
                                    Ожидает проверки и одобрения
                                </span>
                            )}
                        </CardContent>
                    </Card>

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
                                            <p className="text-sm text-muted-foreground">Верификация</p>
                                            {applicant.verified ? (
                                                <span className="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                                    Подтверждён
                                                </span>
                                            ) : (
                                                <span className="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
                                                    Не подтверждён
                                                </span>
                                            )}
                                        </div>
                                    </div>
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
                                    <CardDescription>Проверьте перед одобрением</CardDescription>
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
                                            <p className="py-4 text-center text-muted-foreground">
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
