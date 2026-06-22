import { Head, Link, router } from '@inertiajs/react';
import { Plus, Trash2 } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

interface Permission {
    id: number;
    name: string;
    guard_name: string;
    roles: Array<{ id: number; name: string }>;
}

interface IndexProps {
    permissions: Permission[];
}

export default function Index({ permissions }: IndexProps) {
    const { t } = useTranslation();

    const handleDelete = (id: number) => {
        if (confirm(t('permissions.deleteConfirm'))) {
            router.delete(route('admin.permissions.destroy', id));
        }
    };

    return (
        <AppLayout>
            <Head title={t('permissions.title')} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="mb-6 flex items-center justify-between">
                        <div>
                            <h2 className="text-3xl font-bold tracking-tight">{t('permissions.title')}</h2>
                            <p className="text-muted-foreground mt-2">{t('permissions.description')}</p>
                        </div>
                        <Link href={route('admin.permissions.create')}>
                            <Button>
                                <Plus className="mr-2 h-4 w-4" />
                                {t('permissions.addPermission')}
                            </Button>
                        </Link>
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle>{t('permissions.allPermissions')}</CardTitle>
                            <CardDescription>
                                {t('permissions.total', { count: permissions.length })}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>{t('permissions.name')}</TableHead>
                                        <TableHead>{t('permissions.guard')}</TableHead>
                                        <TableHead>{t('permissions.roles')}</TableHead>
                                        <TableHead className="text-right">{t('permissions.actions')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {permissions.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={4} className="text-center">
                                                {t('permissions.noPermissions')}
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        permissions.map((permission) => (
                                            <TableRow key={permission.id}>
                                                <TableCell className="font-medium">
                                                    {t(`permissions.names.${permission.name}`, {
                                                        defaultValue: permission.name,
                                                    })}
                                                </TableCell>
                                                <TableCell>{permission.guard_name}</TableCell>
                                                <TableCell>
                                                    <div className="flex flex-wrap gap-1">
                                                        {permission.roles.map((role) => (
                                                            <Badge key={role.id} variant="secondary">
                                                                {role.name}
                                                            </Badge>
                                                        ))}
                                                    </div>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        onClick={() => handleDelete(permission.id)}
                                                        disabled={permission.roles.length > 0}
                                                    >
                                                        <Trash2 className="h-4 w-4 text-red-600" />
                                                    </Button>
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
