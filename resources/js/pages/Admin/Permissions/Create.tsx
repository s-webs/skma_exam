import { Head, useForm, Link } from '@inertiajs/react';
import { FormEvent } from 'react';
import { ArrowLeft } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

export default function Create() {
    const { t } = useTranslation();
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        guard_name: 'web',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post(route('admin.permissions.store'));
    };

    return (
        <AppLayout>
            <Head title={t('permissions.createTitle')} />

            <div className="py-12">
                <div className="mx-auto max-w-2xl sm:px-6 lg:px-8">
                    <div className="mb-6">
                        <Link href={route('admin.permissions.index')}>
                            <Button variant="ghost" size="sm">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                {t('permissions.backToPermissions')}
                            </Button>
                        </Link>
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle>{t('permissions.createTitle')}</CardTitle>
                            <CardDescription>{t('permissions.createDescription')}</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={submit} className="space-y-6">
                                <div className="space-y-2">
                                    <Label htmlFor="name">{t('permissions.name')}</Label>
                                    <Input
                                        id="name"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        placeholder="resource.action"
                                        required
                                    />
                                    {errors.name && <p className="text-sm text-red-600">{errors.name}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="guard_name">{t('permissions.guard')}</Label>
                                    <Input
                                        id="guard_name"
                                        value={data.guard_name}
                                        onChange={(e) => setData('guard_name', e.target.value)}
                                        required
                                    />
                                    {errors.guard_name && (
                                        <p className="text-sm text-red-600">{errors.guard_name}</p>
                                    )}
                                </div>

                                <div className="flex justify-end gap-4">
                                    <Link href={route('admin.permissions.index')}>
                                        <Button type="button" variant="outline">
                                            {t('permissions.cancel')}
                                        </Button>
                                    </Link>
                                    <Button type="submit" disabled={processing}>
                                        {processing ? t('permissions.creating') : t('permissions.create')}
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
