import { Head, Link, router } from '@inertiajs/react';
import { Plus, Pencil, Trash2 } from 'lucide-react';
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

interface Role {
    id: number;
    name: string;
    permissions: Array<{ id: number; name: string }>;
    users_count: number;
}

interface IndexProps {
    roles: Role[];
}

export default function Index({ roles }: IndexProps) {
    const { t } = useTranslation();

    const handleDelete = (id: number) => {
        if (confirm(t('roles.deleteConfirm'))) {
            router.delete(route('admin.roles.destroy', id));
        }
    };

    return (
        <AppLayout>
            <Head title={t('roles.title')} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="mb-6 flex items-center justify-between">
                        <div>
                            <h2 className="text-3xl font-bold tracking-tight">{t('roles.title')}</h2>
                            <p className="text-muted-foreground mt-2">{t('roles.description')}</p>
                        </div>
                        <Link href={route('admin.roles.create')}>
                            <Button>
                                <Plus className="mr-2 h-4 w-4" />
                                {t('roles.addRole')}
                            </Button>
                        </Link>
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle>{t('roles.allRoles')}</CardTitle>
                            <CardDescription>{t('roles.total', { count: roles.length })}</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>{t('roles.name')}</TableHead>
                                        <TableHead>{t('roles.permissionsCount')}</TableHead>
                                        <TableHead>{t('roles.usersCount')}</TableHead>
                                        <TableHead className="text-right">{t('roles.actions')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {roles.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={4} className="text-center">
                                                {t('roles.noRoles')}
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        roles.map((role) => (
                                            <TableRow key={role.id}>
                                                <TableCell className="font-medium">
                                                    <Badge>{t(`users.${role.name}`, { defaultValue: role.name })}</Badge>
                                                </TableCell>
                                                <TableCell>{role.permissions.length}</TableCell>
                                                <TableCell>{role.users_count}</TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex justify-end gap-2">
                                                        <Link href={route('admin.roles.edit', role.id)}>
                                                            <Button variant="outline" size="sm">
                                                                <Pencil className="h-4 w-4" />
                                                            </Button>
                                                        </Link>
                                                        {role.name !== 'developer' && (
                                                            <Button
                                                                variant="outline"
                                                                size="sm"
                                                                onClick={() => handleDelete(role.id)}
                                                            >
                                                                <Trash2 className="h-4 w-4 text-red-600" />
                                                            </Button>
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
