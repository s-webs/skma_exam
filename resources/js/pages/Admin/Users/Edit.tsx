import { Head, useForm, Link } from '@inertiajs/react';
import { FormEvent } from 'react';
import { ArrowLeft } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { PermissionCheckboxes } from '@/components/permission-checkboxes';

interface Role {
    id: number;
    name: string;
}

interface PermissionGroup {
    label: string;
    permissions: string[];
}

interface User {
    id: number;
    name: string;
    email: string;
    roles: Role[];
}

interface EditProps {
    user: User;
    roles: Role[];
    permissionGroups: Record<string, PermissionGroup>;
    assignedRoleIds: number[];
    assignedPermissionNames: string[];
}

export default function Edit({
    user,
    roles,
    permissionGroups,
    assignedRoleIds,
    assignedPermissionNames,
}: EditProps) {
    const { t } = useTranslation();
    const { data, setData, put, processing, errors, transform } = useForm({
        name: user.name,
        email: user.email,
        password: '',
        password_confirmation: '',
        role_ids: assignedRoleIds,
        permission_names: assignedPermissionNames,
    });

    transform((formData) => {
        if (!formData.password) {
            const { password: _password, password_confirmation: _confirmation, ...rest } = formData;

            return rest;
        }

        return formData;
    });

    const toggleRole = (roleId: number, checked: boolean) => {
        setData(
            'role_ids',
            checked ? [...data.role_ids, roleId] : data.role_ids.filter((id) => id !== roleId),
        );
    };

    const submit = (e: FormEvent) => {
        e.preventDefault();
        put(route('admin.users.update', user.id));
    };

    return (
        <AppLayout>
            <Head title={t('users.editTitle')} />

            <div className="py-12">
                <div className="mx-auto max-w-3xl sm:px-6 lg:px-8">
                    <div className="mb-6">
                        <Link href={route('admin.users.index')}>
                            <Button variant="ghost" size="sm">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                {t('users.backToUsers')}
                            </Button>
                        </Link>
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle>{t('users.editTitle')}</CardTitle>
                            <CardDescription>{t('users.editDescription')}</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={submit} className="space-y-6">
                                <div className="space-y-2">
                                    <Label htmlFor="name">{t('users.name')}</Label>
                                    <Input
                                        id="name"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        required
                                    />
                                    {errors.name && <p className="text-sm text-red-600">{errors.name}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="email">{t('users.email')}</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        required
                                    />
                                    {errors.email && <p className="text-sm text-red-600">{errors.email}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label>{t('users.roles')}</Label>
                                    <div className="grid gap-2 sm:grid-cols-2">
                                        {roles.map((role) => (
                                            <div key={role.id} className="flex items-center gap-2">
                                                <Checkbox
                                                    id={`role-${role.id}`}
                                                    checked={data.role_ids.includes(role.id)}
                                                    onCheckedChange={(checked) =>
                                                        toggleRole(role.id, checked === true)
                                                    }
                                                />
                                                <Label htmlFor={`role-${role.id}`} className="font-normal">
                                                    {t(`users.${role.name}`, { defaultValue: role.name })}
                                                </Label>
                                            </div>
                                        ))}
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <Label>{t('users.directPermissions')}</Label>
                                    <PermissionCheckboxes
                                        permissionGroups={permissionGroups}
                                        selected={data.permission_names}
                                        onChange={(permissions) => setData('permission_names', permissions)}
                                        idPrefix="user-perm"
                                    />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="password">{t('users.newPasswordOptional')}</Label>
                                    <Input
                                        id="password"
                                        type="password"
                                        autoComplete="new-password"
                                        value={data.password}
                                        onChange={(e) => setData('password', e.target.value)}
                                    />
                                    {errors.password && (
                                        <p className="text-sm text-red-600">{errors.password}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="password_confirmation">{t('users.confirmPassword')}</Label>
                                    <Input
                                        id="password_confirmation"
                                        type="password"
                                        autoComplete="new-password"
                                        value={data.password_confirmation}
                                        onChange={(e) => setData('password_confirmation', e.target.value)}
                                    />
                                </div>

                                <div className="flex justify-end gap-4">
                                    <Link href={route('admin.users.index')}>
                                        <Button type="button" variant="outline">
                                            {t('users.cancel')}
                                        </Button>
                                    </Link>
                                    <Button type="submit" disabled={processing}>
                                        {processing ? t('users.updating') : t('users.update')}
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
