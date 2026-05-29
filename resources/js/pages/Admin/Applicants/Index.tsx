import { Head, Link, router } from '@inertiajs/react';
import { Plus, Eye, Pencil, Trash2 } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

interface Applicant {
    id: number;
    name: string;
    email: string;
    identifier: string;
    phone: string;
    language: string;
    verified: boolean;
    exam_attempts_count: number;
    created_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedApplicants {
    data: Applicant[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface IndexProps {
    applicants: PaginatedApplicants;
}

export default function Index({ applicants }: IndexProps) {
    const { t } = useTranslation();

    const handleDelete = (id: number) => {
        if (confirm(t('applicants.deleteConfirm'))) {
            router.delete(route('admin.applicants.destroy', id));
        }
    };

    const getLanguageName = (lang: string) => {
        const languages: Record<string, string> = {
            kz: t('applicants.kazakh'),
            ru: t('applicants.russian'),
            en: t('applicants.english'),
        };
        return languages[lang] || lang;
    };

    return (
        <AppLayout>
            <Head title={t('applicants.title')} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="mb-6 flex items-center justify-between">
                        <div>
                            <h2 className="text-2xl font-bold">{t('applicants.title')}</h2>
                            <p className="text-muted-foreground">
                                {t('applicants.total', { count: applicants.total })}
                            </p>
                        </div>
                        <Link href={route('admin.applicants.create')}>
                            <Button>
                                <Plus className="mr-2 h-4 w-4" />
                                {t('applicants.addApplicant')}
                            </Button>
                        </Link>
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle>{t('applicants.listTitle')}</CardTitle>
                            <CardDescription>
                                {t('applicants.listDescription')}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>{t('applicants.iin')}</TableHead>
                                        <TableHead>{t('applicants.name')}</TableHead>
                                        <TableHead>{t('applicants.email')}</TableHead>
                                        <TableHead>{t('applicants.phone')}</TableHead>
                                        <TableHead>{t('applicants.language')}</TableHead>
                                        <TableHead>{t('applicants.verification')}</TableHead>
                                        <TableHead>{t('applicants.attempts')}</TableHead>
                                        <TableHead className="text-right">{t('applicants.actions')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {applicants.data.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={8} className="text-center text-muted-foreground">
                                                {t('applicants.noApplicants')}
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        applicants.data.map((applicant) => (
                                            <TableRow key={applicant.id}>
                                                <TableCell className="font-mono">
                                                    {applicant.identifier}
                                                </TableCell>
                                                <TableCell className="font-medium">
                                                    {applicant.name}
                                                </TableCell>
                                                <TableCell>{applicant.email}</TableCell>
                                                <TableCell>{applicant.phone}</TableCell>
                                                <TableCell>
                                                    {getLanguageName(applicant.language)}
                                                </TableCell>
                                                <TableCell>
                                                    {applicant.verified ? (
                                                        <span className="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800">
                                                            {t('applicants.verified')}
                                                        </span>
                                                    ) : (
                                                        <span className="rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-800">
                                                            {t('applicants.notVerified')}
                                                        </span>
                                                    )}
                                                </TableCell>
                                                <TableCell>
                                                    <span className="rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-800">
                                                        {applicant.exam_attempts_count}
                                                    </span>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex justify-end gap-2">
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
                                        ))
                                    )}
                                </TableBody>
                            </Table>

                            {applicants.last_page > 1 && (
                                <div className="mt-4 flex items-center justify-center gap-2">
                                    {applicants.links.map((link, index) => (
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
