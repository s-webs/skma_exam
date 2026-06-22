import { Head, useForm, Link } from '@inertiajs/react';
import { FormEvent } from 'react';
import { ArrowLeft } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { PermissionCheckboxes } from '@/components/permission-checkboxes';

interface Role {
    id: number;
    name: string;
}

interface PermissionGroup {
    label: string;
    permissions: string[];
}

interface EditProps {
    role: Role;
    permissionGroups: Record<string, PermissionGroup>;
    assignedPermissionNames: string[];
}

export default function Edit({ role, permissionGroups, assignedPermissionNames }: EditProps) {
    const { t } = useTranslation();
    const { data, setData, put, processing, errors } = useForm({
        name: role.name,
        permission_names: assignedPermissionNames,
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        put(route('admin.roles.update', role.id));
    };

    return (
        <AppLayout>
            <Head title={t('roles.editTitle')} />

            <div className="py-12">
                <div className="mx-auto max-w-3xl sm:px-6 lg:px-8">
                    <div className="mb-6">
                        <Link href={route('admin.roles.index')}>
                            <Button variant="ghost" size="sm">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                {t('roles.backToRoles')}
                            </Button>
                        </Link>
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle>{t('roles.editTitle')}</CardTitle>
                            <CardDescription>{t('roles.editDescription')}</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={submit} className="space-y-6">
                                <div className="space-y-2">
                                    <Label htmlFor="name">{t('roles.name')}</Label>
                                    <Input
                                        id="name"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        required
                                        disabled={role.name === 'developer'}
                                    />
                                    {errors.name && <p className="text-sm text-red-600">{errors.name}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label>{t('roles.permissions')}</Label>
                                    <PermissionCheckboxes
                                        permissionGroups={permissionGroups}
                                        selected={data.permission_names}
                                        onChange={(permissions) => setData('permission_names', permissions)}
                                    />
                                </div>

                                <div className="flex justify-end gap-4">
                                    <Link href={route('admin.roles.index')}>
                                        <Button type="button" variant="outline">
                                            {t('roles.cancel')}
                                        </Button>
                                    </Link>
                                    <Button type="submit" disabled={processing}>
                                        {processing ? t('roles.updating') : t('roles.update')}
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
