import { Head, useForm, Link } from '@inertiajs/react';
import { FormEvent } from 'react';
import { ArrowLeft, Image as ImageIcon } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';

export default function Create() {
    const { t } = useTranslation();
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        identifier: '',
        address: '',
        phone: '',
        graduate_organization: '',
        graduate_year: '',
        speciality: '',
        language: 'ru',
        verified: false,
        document_front: null as File | null,
        document_back: null as File | null,
        diplom: null as File | null,
        certificate: null as File | null,
        photo: null as File | null,
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post(route('admin.applicants.store'));
    };

    return (
        <AppLayout>
            <Head title={t('applicants.createTitle')} />

            <div className="py-12">
                <div className="mx-auto max-w-3xl sm:px-6 lg:px-8">
                    <div className="mb-6">
                        <Link href={route('admin.applicants.index')}>
                            <Button variant="ghost" size="sm">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                {t('applicants.backToApplicants')}
                            </Button>
                        </Link>
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle>{t('applicants.createTitle')}</CardTitle>
                            <CardDescription>
                                {t('applicants.createDescription')}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={submit} className="space-y-6">
                                <div className="grid gap-6 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="name">{t('applicants.fullName')}</Label>
                                        <Input
                                            id="name"
                                            value={data.name}
                                            onChange={(e) => setData('name', e.target.value)}
                                            placeholder={t('applicants.fullNamePlaceholder')}
                                            required
                                        />
                                        {errors.name && (
                                            <p className="text-sm text-red-600">{errors.name}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="identifier">{t('applicants.iinLabel')}</Label>
                                        <Input
                                            id="identifier"
                                            value={data.identifier}
                                            onChange={(e) => setData('identifier', e.target.value)}
                                            placeholder={t('applicants.iinPlaceholder')}
                                            maxLength={12}
                                            required
                                        />
                                        {errors.identifier && (
                                            <p className="text-sm text-red-600">{errors.identifier}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="email">{t('applicants.email')}</Label>
                                        <Input
                                            id="email"
                                            type="email"
                                            value={data.email}
                                            onChange={(e) => setData('email', e.target.value)}
                                            placeholder={t('applicants.emailPlaceholder')}
                                            required
                                        />
                                        {errors.email && (
                                            <p className="text-sm text-red-600">{errors.email}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="phone">{t('applicants.phoneLabel')}</Label>
                                        <Input
                                            id="phone"
                                            value={data.phone}
                                            onChange={(e) => setData('phone', e.target.value)}
                                            placeholder={t('applicants.phonePlaceholder')}
                                            required
                                        />
                                        {errors.phone && (
                                            <p className="text-sm text-red-600">{errors.phone}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2 md:col-span-2">
                                        <Label htmlFor="address">{t('common.address')}</Label>
                                        <Textarea
                                            id="address"
                                            value={data.address}
                                            onChange={(e) => setData('address', e.target.value)}
                                            required
                                            rows={3}
                                        />
                                        {errors.address && (
                                            <p className="text-sm text-red-600">{errors.address}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="graduate_organization">{t('common.graduateOrganization')}</Label>
                                        <Input
                                            id="graduate_organization"
                                            value={data.graduate_organization}
                                            onChange={(e) => setData('graduate_organization', e.target.value)}
                                            required
                                        />
                                        {errors.graduate_organization && (
                                            <p className="text-sm text-red-600">{errors.graduate_organization}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="graduate_year">{t('common.graduateYear')}</Label>
                                        <Input
                                            id="graduate_year"
                                            value={data.graduate_year}
                                            onChange={(e) => setData('graduate_year', e.target.value)}
                                            required
                                        />
                                        {errors.graduate_year && (
                                            <p className="text-sm text-red-600">{errors.graduate_year}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="speciality">{t('common.speciality')}</Label>
                                        <Input
                                            id="speciality"
                                            value={data.speciality}
                                            onChange={(e) => setData('speciality', e.target.value)}
                                            required
                                        />
                                        {errors.speciality && (
                                            <p className="text-sm text-red-600">{errors.speciality}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="language">{t('applicants.languageLabel')}</Label>
                                        <Select
                                            value={data.language}
                                            onValueChange={(value) => setData('language', value)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="kz">{t('applicants.kazakh')}</SelectItem>
                                                <SelectItem value="ru">{t('applicants.russian')}</SelectItem>
                                                <SelectItem value="en">{t('applicants.english')}</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        {errors.language && (
                                            <p className="text-sm text-red-600">{errors.language}</p>
                                        )}
                                    </div>

                                    <div className="flex items-center space-x-2">
                                        <Checkbox
                                            id="verified"
                                            checked={data.verified}
                                            onCheckedChange={(checked) => setData('verified', checked as boolean)}
                                        />
                                        <Label htmlFor="verified" className="cursor-pointer">
                                            {t('applicants.isVerified')}
                                        </Label>
                                    </div>
                                </div>

                                <div className="space-y-4">
                                    <h3 className="text-lg font-semibold">{t('common.documents')}</h3>

                                    <div className="grid gap-4 md:grid-cols-2">
                                        <div className="space-y-2">
                                            <div className="flex items-center justify-between">
                                                <Label htmlFor="document_front">{t('common.documentFront')}</Label>
                                                <input
                                                    id="document_front"
                                                    type="file"
                                                    accept="image/*"
                                                    onChange={(e) => setData('document_front', e.target.files?.[0] || null)}
                                                    className="hidden"
                                                />
                                                <label
                                                    htmlFor="document_front"
                                                    className="cursor-pointer rounded-md border border-input bg-background p-2 hover:bg-accent hover:text-accent-foreground"
                                                    title={t('questions.selectImage')}
                                                >
                                                    <ImageIcon className="h-5 w-5" />
                                                </label>
                                            </div>
                                            {data.document_front && (
                                                <div className="mt-2">
                                                    <img
                                                        src={URL.createObjectURL(data.document_front)}
                                                        alt="Document front preview"
                                                        className="max-w-xs rounded border"
                                                    />
                                                </div>
                                            )}
                                            {errors.document_front && (
                                                <p className="text-sm text-red-600">{errors.document_front}</p>
                                            )}
                                        </div>

                                        <div className="space-y-2">
                                            <div className="flex items-center justify-between">
                                                <Label htmlFor="document_back">{t('common.documentBack')}</Label>
                                                <input
                                                    id="document_back"
                                                    type="file"
                                                    accept="image/*"
                                                    onChange={(e) => setData('document_back', e.target.files?.[0] || null)}
                                                    className="hidden"
                                                />
                                                <label
                                                    htmlFor="document_back"
                                                    className="cursor-pointer rounded-md border border-input bg-background p-2 hover:bg-accent hover:text-accent-foreground"
                                                    title={t('questions.selectImage')}
                                                >
                                                    <ImageIcon className="h-5 w-5" />
                                                </label>
                                            </div>
                                            {data.document_back && (
                                                <div className="mt-2">
                                                    <img
                                                        src={URL.createObjectURL(data.document_back)}
                                                        alt="Document back preview"
                                                        className="max-w-xs rounded border"
                                                    />
                                                </div>
                                            )}
                                            {errors.document_back && (
                                                <p className="text-sm text-red-600">{errors.document_back}</p>
                                            )}
                                        </div>

                                        <div className="space-y-2">
                                            <div className="flex items-center justify-between">
                                                <Label htmlFor="diplom">{t('common.diplom')}</Label>
                                                <input
                                                    id="diplom"
                                                    type="file"
                                                    accept="image/*"
                                                    onChange={(e) => setData('diplom', e.target.files?.[0] || null)}
                                                    className="hidden"
                                                />
                                                <label
                                                    htmlFor="diplom"
                                                    className="cursor-pointer rounded-md border border-input bg-background p-2 hover:bg-accent hover:text-accent-foreground"
                                                    title={t('questions.selectImage')}
                                                >
                                                    <ImageIcon className="h-5 w-5" />
                                                </label>
                                            </div>
                                            {data.diplom && (
                                                <div className="mt-2">
                                                    <img
                                                        src={URL.createObjectURL(data.diplom)}
                                                        alt="Diplom preview"
                                                        className="max-w-xs rounded border"
                                                    />
                                                </div>
                                            )}
                                            {errors.diplom && (
                                                <p className="text-sm text-red-600">{errors.diplom}</p>
                                            )}
                                        </div>

                                        <div className="space-y-2">
                                            <div className="flex items-center justify-between">
                                                <Label htmlFor="certificate">{t('common.certificate')}</Label>
                                                <input
                                                    id="certificate"
                                                    type="file"
                                                    accept="image/*"
                                                    onChange={(e) => setData('certificate', e.target.files?.[0] || null)}
                                                    className="hidden"
                                                />
                                                <label
                                                    htmlFor="certificate"
                                                    className="cursor-pointer rounded-md border border-input bg-background p-2 hover:bg-accent hover:text-accent-foreground"
                                                    title={t('questions.selectImage')}
                                                >
                                                    <ImageIcon className="h-5 w-5" />
                                                </label>
                                            </div>
                                            {data.certificate && (
                                                <div className="mt-2">
                                                    <img
                                                        src={URL.createObjectURL(data.certificate)}
                                                        alt="Certificate preview"
                                                        className="max-w-xs rounded border"
                                                    />
                                                </div>
                                            )}
                                            {errors.certificate && (
                                                <p className="text-sm text-red-600">{errors.certificate}</p>
                                            )}
                                        </div>

                                        <div className="space-y-2">
                                            <div className="flex items-center justify-between">
                                                <Label htmlFor="photo">{t('common.photo')}</Label>
                                                <input
                                                    id="photo"
                                                    type="file"
                                                    accept="image/*"
                                                    onChange={(e) => setData('photo', e.target.files?.[0] || null)}
                                                    className="hidden"
                                                />
                                                <label
                                                    htmlFor="photo"
                                                    className="cursor-pointer rounded-md border border-input bg-background p-2 hover:bg-accent hover:text-accent-foreground"
                                                    title={t('questions.selectImage')}
                                                >
                                                    <ImageIcon className="h-5 w-5" />
                                                </label>
                                            </div>
                                            {data.photo && (
                                                <div className="mt-2">
                                                    <img
                                                        src={URL.createObjectURL(data.photo)}
                                                        alt="Photo preview"
                                                        className="max-w-xs rounded border"
                                                    />
                                                </div>
                                            )}
                                            {errors.photo && (
                                                <p className="text-sm text-red-600">{errors.photo}</p>
                                            )}
                                        </div>
                                    </div>
                                </div>

                                <div className="flex justify-end gap-4">
                                    <Link href={route('admin.applicants.index')}>
                                        <Button type="button" variant="outline">
                                            {t('applicants.cancel')}
                                        </Button>
                                    </Link>
                                    <Button type="submit" disabled={processing}>
                                        {processing ? t('applicants.creating') : t('applicants.create')}
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
