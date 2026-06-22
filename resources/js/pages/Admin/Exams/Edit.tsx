import { Head, useForm, Link } from '@inertiajs/react';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

import { LocalizedNameFields } from '@/components/localized-name-fields';

interface ExamType {
    id: number;
    name_ru: string;
    name_kk?: string | null;
    name_en?: string | null;
}

interface Exam {
    id: number;
    exam_type_id: number;
    name_ru: string;
    name_kk: string | null;
    name_en: string | null;
    description: string | null;
    language: string;
    duration_minutes: number;
    questions_count: number;
    passing_score: number;
    max_attempts: number | null;
    is_active: boolean;
    require_telegram_verification: boolean;
}

interface EditProps {
    exam: Exam;
    examTypes: ExamType[];
}

export default function Edit({ exam, examTypes }: EditProps) {
    const { t } = useTranslation();
    const { data, setData, put, processing, errors } = useForm({
        exam_type_id: exam.exam_type_id.toString(),
        name_ru: exam.name_ru ?? '',
        name_kk: exam.name_kk || '',
        name_en: exam.name_en || '',
        description: exam.description || '',
        language: exam.language,
        duration_minutes: exam.duration_minutes,
        questions_count: exam.questions_count,
        passing_score: exam.passing_score,
        max_attempts: exam.max_attempts,
        is_active: exam.is_active,
        require_telegram_verification: exam.require_telegram_verification ?? true,
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        put(route('admin.exams.update', exam.id));
    };

    return (
        <AppLayout>
            <Head title={t('exams.editTitle')} />

            <div className="py-12">
                <div className="mx-auto max-w-3xl sm:px-6 lg:px-8">
                    <div className="mb-6">
                        <Link href={route('admin.exams.index')}>
                            <Button variant="ghost" size="sm">
                                <ArrowLeft className="mr-2 h-4 w-4" />
                                {t('exams.backToExams')}
                            </Button>
                        </Link>
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle>{t('exams.editTitle')}</CardTitle>
                            <CardDescription>
                                {t('exams.editDescription')}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={submit} className="space-y-6">
                                <div className="space-y-2">
                                    <Label htmlFor="exam_type_id">{t('exams.examType')}</Label>
                                    <Select
                                        value={data.exam_type_id}
                                        onValueChange={(value) => setData('exam_type_id', value)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {examTypes.map((type) => (
                                                <SelectItem key={type.id} value={type.id.toString()}>
                                                    {type.name_ru}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.exam_type_id && (
                                        <p className="text-sm text-red-600">{errors.exam_type_id}</p>
                                    )}
                                </div>

                                <LocalizedNameFields
                                    values={data}
                                    onChange={(field, value) => setData(field, value)}
                                    errors={errors}
                                    idPrefix="exam"
                                />

                                <div className="space-y-2">
                                    <Label htmlFor="description">{t('exams.descriptionLabel')}</Label>
                                    <Textarea
                                        id="description"
                                        value={data.description}
                                        onChange={(e) => setData('description', e.target.value)}
                                        rows={3}
                                    />
                                    {errors.description && (
                                        <p className="text-sm text-red-600">{errors.description}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="language">{t('exams.examLanguage')}</Label>
                                    <Select
                                        value={data.language}
                                        onValueChange={(value) => setData('language', value)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="ru">{t('exams.russian')}</SelectItem>
                                            <SelectItem value="kz">{t('exams.kazakh')}</SelectItem>
                                            <SelectItem value="en">{t('exams.english')}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    {errors.language && (
                                        <p className="text-sm text-red-600">{errors.language}</p>
                                    )}
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="duration_minutes">{t('exams.duration')}</Label>
                                        <Input
                                            id="duration_minutes"
                                            type="number"
                                            value={data.duration_minutes}
                                            onChange={(e) => setData('duration_minutes', parseInt(e.target.value))}
                                            required
                                            min="1"
                                            max="300"
                                        />
                                        {errors.duration_minutes && (
                                            <p className="text-sm text-red-600">{errors.duration_minutes}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="questions_count">{t('exams.questionsCount')}</Label>
                                        <Input
                                            id="questions_count"
                                            type="number"
                                            value={data.questions_count}
                                            onChange={(e) => setData('questions_count', parseInt(e.target.value))}
                                            required
                                            min="1"
                                            max="200"
                                        />
                                        {errors.questions_count && (
                                            <p className="text-sm text-red-600">{errors.questions_count}</p>
                                        )}
                                    </div>
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="passing_score">{t('exams.passingScore')}</Label>
                                        <Input
                                            id="passing_score"
                                            type="number"
                                            value={data.passing_score}
                                            onChange={(e) => setData('passing_score', parseInt(e.target.value))}
                                            required
                                            min="1"
                                        />
                                        {errors.passing_score && (
                                            <p className="text-sm text-red-600">{errors.passing_score}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="max_attempts">{t('exams.maxAttempts')}</Label>
                                        <Input
                                            id="max_attempts"
                                            type="number"
                                            value={data.max_attempts || ''}
                                            onChange={(e) => setData('max_attempts', e.target.value ? parseInt(e.target.value) : null)}
                                            min="1"
                                            placeholder={t('exams.maxAttemptsPlaceholder')}
                                        />
                                        {errors.max_attempts && (
                                            <p className="text-sm text-red-600">{errors.max_attempts}</p>
                                        )}
                                    </div>
                                </div>

                                <div className="flex items-center space-x-2">
                                    <Checkbox
                                        id="is_active"
                                        checked={data.is_active}
                                        onCheckedChange={(checked) => setData('is_active', checked as boolean)}
                                    />
                                    <Label htmlFor="is_active" className="cursor-pointer">
                                        {t('exams.isActive')}
                                    </Label>
                                </div>

                                <div className="space-y-2 rounded-lg border p-4">
                                    <div className="flex items-center space-x-2">
                                        <Checkbox
                                            id="require_telegram_verification"
                                            checked={data.require_telegram_verification}
                                            onCheckedChange={(checked) =>
                                                setData('require_telegram_verification', checked as boolean)
                                            }
                                        />
                                        <Label htmlFor="require_telegram_verification" className="cursor-pointer">
                                            {t('exams.requireTelegramVerification')}
                                        </Label>
                                    </div>
                                    <p className="text-sm text-muted-foreground">
                                        {t('exams.requireTelegramVerificationHint')}
                                    </p>
                                </div>

                                <div className="flex justify-end gap-4">
                                    <Link href={route('admin.exams.index')}>
                                        <Button type="button" variant="outline">
                                            {t('exams.cancel')}
                                        </Button>
                                    </Link>
                                    <Button type="submit" disabled={processing}>
                                        {processing ? t('exams.saving') : t('exams.saveChanges')}
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
