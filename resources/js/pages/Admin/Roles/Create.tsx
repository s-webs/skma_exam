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

interface PermissionGroup {
    label: string;
    permissions: string[];
}

interface CreateProps {
    permissionGroups: Record<string, PermissionGroup>;
}

export default function Create({ permissionGroups }: CreateProps) {
    const { t } = useTranslation();
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        permission_names: [] as string[],
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post(route('admin.roles.store'));
    };

    return (
        <AppLayout>
            <Head title={t('roles.createTitle')} />

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
                            <CardTitle>{t('roles.createTitle')}</CardTitle>
                            <CardDescription>{t('roles.createDescription')}</CardDescription>
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
                                        {processing ? t('roles.creating') : t('roles.create')}
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
