import { Head, useForm, Link, usePage } from '@inertiajs/react';
import { FormEvent } from 'react';
import { ArrowLeft } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';

interface ExamType {
    id: number;
    name: string;
    description: string | null;
    is_active: boolean;
}

interface AssignableUser {
    id: number;
    name: string;
    email: string;
}

interface AssignableRole {
    id: number;
    name: string;
}

interface EditProps {
    examType: ExamType;
    assignableUsers: AssignableUser[];
    assignableRoles: AssignableRole[];
    assignedUserIds: number[];
    assignedRoleIds: number[];
}

interface SharedAuth {
    auth: {
        isDeveloper: boolean;
    };
}

export default function Edit({
    examType,
    assignableUsers,
    assignableRoles,
    assignedUserIds,
    assignedRoleIds,
}: EditProps) {
    const { t } = useTranslation();
    const { auth } = usePage<SharedAuth>().props;
    const isDeveloper = auth.isDeveloper;

    const { data, setData, put, processing, errors } = useForm({
        name: examType.name,
        description: examType.description || '',
        is_active: examType.is_active,
        user_ids: assignedUserIds,
        role_ids: assignedRoleIds,
    });

    const toggleUser = (userId: number, checked: boolean) => {
        setData(
            'user_ids',
            checked
                ? [...data.user_ids, userId]
                : data.user_ids.filter((id) => id !== userId),
        );
    };

    const toggleRole = (roleId: number, checked: boolean) => {
        setData(
            'role_ids',
            checked
                ? [...data.role_ids, roleId]
                : data.role_ids.filter((id) => id !== roleId),
        );
    };

    const submit = (e: FormEvent) => {
        e.preventDefault();
        put(route('admin.exam-types.update', examType.id));
    };

    return (
        <AppLayout>
            <Head title={t('examTypes.editTitle')} />

            <div className="py-12">
                <div className="mx-auto max-w-3xl sm:px-6 lg:px-8">
                    <div className="mb-6">
                        <Link href={route('admin.exam-types.index')}>
                            <Button variant="ghost" size="sm">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                {t('examTypes.backToTypes')}
                            </Button>
                        </Link>
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle>{t('examTypes.editTitle')}</CardTitle>
                            <CardDescription>
                                {t('examTypes.editDescription')}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={submit} className="space-y-6">
                                <div className="space-y-2">
                                    <Label htmlFor="name">{t('examTypes.name')}</Label>
                                    <Input
                                        id="name"
                                        type="text"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        required
                                        autoFocus
                                    />
                                    {errors.name && (
                                        <p className="text-sm text-red-600">{errors.name}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="description">{t('examTypes.descriptionLabel')}</Label>
                                    <Textarea
                                        id="description"
                                        value={data.description}
                                        onChange={(e) => setData('description', e.target.value)}
                                        rows={4}
                                    />
                                    {errors.description && (
                                        <p className="text-sm text-red-600">{errors.description}</p>
                                    )}
                                </div>

                                <div className="flex items-center space-x-2">
                                    <Checkbox
                                        id="is_active"
                                        checked={data.is_active}
                                        onCheckedChange={(checked) => setData('is_active', checked as boolean)}
                                    />
                                    <Label htmlFor="is_active" className="cursor-pointer">
                                        {t('examTypes.isActive')}
                                    </Label>
                                </div>

                                {isDeveloper && (
                                    <div className="space-y-4 rounded-lg border p-4">
                                        <div>
                                            <h3 className="font-medium">{t('examTypes.accessTitle')}</h3>
                                            <p className="text-sm text-muted-foreground mt-1">
                                                {t('examTypes.accessHint')}
                                            </p>
                                        </div>

                                        <div className="space-y-2">
                                            <Label>{t('examTypes.accessUsers')}</Label>
                                            <div className="max-h-48 space-y-2 overflow-y-auto rounded-md border p-3">
                                                {assignableUsers.length === 0 ? (
                                                    <p className="text-sm text-muted-foreground">
                                                        {t('examTypes.noAssignableUsers')}
                                                    </p>
                                                ) : (
                                                    assignableUsers.map((user) => (
                                                        <div key={user.id} className="flex items-center space-x-2">
                                                            <Checkbox
                                                                id={`user-${user.id}`}
                                                                checked={data.user_ids.includes(user.id)}
                                                                onCheckedChange={(checked) =>
                                                                    toggleUser(user.id, checked as boolean)
                                                                }
                                                            />
                                                            <Label
                                                                htmlFor={`user-${user.id}`}
                                                                className="cursor-pointer font-normal"
                                                            >
                                                                {user.name} ({user.email})
                                                            </Label>
                                                        </div>
                                                    ))
                                                )}
                                            </div>
                                        </div>

                                        <div className="space-y-2">
                                            <Label>{t('examTypes.accessRoles')}</Label>
                                            <div className="space-y-2 rounded-md border p-3">
                                                {assignableRoles.map((role) => (
                                                    <div key={role.id} className="flex items-center space-x-2">
                                                        <Checkbox
                                                            id={`role-${role.id}`}
                                                            checked={data.role_ids.includes(role.id)}
                                                            onCheckedChange={(checked) =>
                                                                toggleRole(role.id, checked as boolean)
                                                            }
                                                        />
                                                        <Label
                                                            htmlFor={`role-${role.id}`}
                                                            className="cursor-pointer font-normal uppercase"
                                                        >
                                                            {role.name}
                                                        </Label>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    </div>
                                )}

                                <div className="flex justify-end gap-4">
                                    <Link href={route('admin.exam-types.index')}>
                                        <Button type="button" variant="outline">
                                            {t('examTypes.cancel')}
                                        </Button>
                                    </Link>
                                    <Button type="submit" disabled={processing}>
                                        {processing ? t('examTypes.saving') : t('examTypes.saveChanges')}
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
