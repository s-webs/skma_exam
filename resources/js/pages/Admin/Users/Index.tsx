import { Head, Link, router } from '@inertiajs/react';
import { Plus, Pencil, Trash2 } from 'lucide-react';
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

interface User {
    id: number;
    name: string;
    email: string;
    roles: Array<{ name: string }>;
    created_at: string;
}

interface UsersIndexProps {
    users: {
        data: User[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

export default function Index({ users }: UsersIndexProps) {
    const { t } = useTranslation();

    const handleDelete = (id: number) => {
        if (confirm(t('users.deleteConfirm'))) {
            router.delete(route('admin.users.destroy', id));
        }
    };

    const getRoleBadgeColor = (role: string) => {
        switch (role) {
            case 'developer':
                return 'bg-purple-100 text-purple-800';
            case 'ktbo':
                return 'bg-blue-100 text-blue-800';
            case 'registrator':
                return 'bg-green-100 text-green-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    return (
        <AppLayout>
            <Head title={t('users.title')} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="mb-6 flex items-center justify-between">
                        <div>
                            <h2 className="text-3xl font-bold tracking-tight">{t('users.title')}</h2>
                            <p className="text-muted-foreground mt-2">
                                {t('users.description')}
                            </p>
                        </div>
                        <Link href={route('admin.users.create')}>
                            <Button>
                                <Plus className="mr-2 h-4 w-4" />
                                {t('users.addUser')}
                            </Button>
                        </Link>
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle>{t('users.allUsers')}</CardTitle>
                            <CardDescription>
                                {t('users.total', { count: users.total })}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>{t('users.name')}</TableHead>
                                        <TableHead>{t('users.email')}</TableHead>
                                        <TableHead>{t('users.role')}</TableHead>
                                        <TableHead>{t('users.created')}</TableHead>
                                        <TableHead className="text-right">{t('users.actions')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {users.data.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={5} className="text-center">
                                                {t('users.noUsers')}
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        users.data.map((user) => (
                                            <TableRow key={user.id}>
                                                <TableCell className="font-medium">{user.name}</TableCell>
                                                <TableCell>{user.email}</TableCell>
                                                <TableCell>
                                                    <Badge
                                                        className={getRoleBadgeColor(user.roles[0]?.name)}
                                                    >
                                                        {t(`users.${user.roles[0]?.name}`) || user.roles[0]?.name}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    {new Date(user.created_at).toLocaleDateString()}
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex justify-end gap-2">
                                                        <Link href={route('admin.users.edit', user.id)}>
                                                            <Button variant="outline" size="sm">
                                                                <Pencil className="h-4 w-4" />
                                                            </Button>
                                                        </Link>
                                                        <Button
                                                            variant="outline"
                                                            size="sm"
                                                            onClick={() => handleDelete(user.id)}
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
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
