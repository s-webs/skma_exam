import { Head, useForm } from '@inertiajs/react';
import { FormEvent, useCallback, useEffect, useRef, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { compressImageFile } from '@/lib/compress-image';
import {
    GraduationCap,
    User,
    Mail,
    Phone,
    MapPin,
    Building2,
    Calendar,
    BookOpen,
    Upload,
    CheckCircle2,
    ChevronRight,
    MessageCircle,
    ExternalLink,
    Loader2,
    Copy,
    Check,
} from 'lucide-react';

type RegistrationStep = 'exam' | 'personal' | 'telegram' | 'education' | 'documents';

function getCsrfToken(): string {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : '';
}

async function registrationJson<T>(
    url: string,
    options: RequestInit = {},
): Promise<
    | { ok: true; data: T }
    | { ok: false; message: string; errors?: Record<string, string[]>; can_resume?: boolean }
> {
    const response = await fetch(url, {
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-XSRF-TOKEN': getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
            ...options.headers,
        },
        ...options,
    });

    const body = await response.json().catch(() => ({}));

    if (!response.ok) {
        const errors = body.errors as Record<string, string[]> | undefined;
        const firstFieldError = errors
            ? Object.values(errors).flat()[0]
            : undefined;

        return {
            ok: false,
            message: firstFieldError ?? body.message ?? 'Произошла ошибка. Попробуйте снова.',
            errors,
            can_resume: body.can_resume === true,
        };
    }

    return { ok: true, data: body as T };
}

interface Exam {
    id: number;
    name: string;
    language: string;
}

interface ExamType {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    exams: Exam[];
}

interface RegistrationIndexProps {
    examType: ExamType;
    telegramBotUsername: string | null;
}

export default function Index({ examType, telegramBotUsername }: RegistrationIndexProps) {
    const [selectedExam, setSelectedExam] = useState<number | null>(null);
    const [currentStep, setCurrentStep] = useState<RegistrationStep>('exam');
    const [telegramBotUrl, setTelegramBotUrl] = useState<string | null>(null);
    const [verificationToken, setVerificationToken] = useState<string | null>(null);
    const [tokenCopied, setTokenCopied] = useState(false);
    const [telegramLinked, setTelegramLinked] = useState(false);
    const [telegramVerified, setTelegramVerified] = useState(false);
    const [verificationCode, setVerificationCode] = useState('');
    const [telegramError, setTelegramError] = useState<string | null>(null);
    const [telegramLoading, setTelegramLoading] = useState(false);
    const [resumeLoading, setResumeLoading] = useState(false);
    const [existingAccountModalOpen, setExistingAccountModalOpen] = useState(false);
    const [existingAccountMessage, setExistingAccountMessage] = useState<string | null>(null);
    const [loadedFromExistingApplicant, setLoadedFromExistingApplicant] = useState(false);
    const [initFieldErrors, setInitFieldErrors] = useState<Record<string, string[]>>({});
    const [verifyLoading, setVerifyLoading] = useState(false);
    const [compressingField, setCompressingField] = useState<string | null>(null);
    const personalEditInvalidatedRef = useRef(false);

    type DocumentField =
        | 'document_front'
        | 'document_back'
        | 'diplom'
        | 'certificate'
        | 'photo';

    const handleDocumentFile = async (field: DocumentField, file: File | null) => {
        if (!file) {
            setData(field, null);
            return;
        }

        setCompressingField(field);
        try {
            const compressed = await compressImageFile(file);
            setData(field, compressed);
        } catch {
            setData(field, file);
        } finally {
            setCompressingField(null);
        }
    };

    const { data, setData, post, processing, errors } = useForm({
        exam_id: '',
        name: '',
        email: '',
        identifier: '',
        address: '',
        phone: '',
        graduate_organization: '',
        graduate_year: '',
        speciality: '',
        document_front: null as File | null,
        document_back: null as File | null,
        diplom: null as File | null,
        certificate: null as File | null,
        photo: null as File | null,
    });

    const handleExamSelect = (examId: string) => {
        setSelectedExam(Number(examId));
        setData('exam_id', examId);
    };

    const canProceedToPersonal = data.exam_id !== '';
    const canProceedToTelegram = data.name && data.email && data.identifier && data.phone && data.address;
    const canProceedToEducation = telegramVerified;
    const canProceedToDocuments = data.graduate_organization && data.graduate_year && data.speciality;

    const resetTelegramSessionOnServer = useCallback(async () => {
        await registrationJson(route('public.registration.telegram.reset', examType.slug), {
            method: 'POST',
        });
    }, [examType.slug]);

    const resetTelegramState = useCallback(async () => {
        await resetTelegramSessionOnServer();
        setTelegramBotUrl(null);
        setVerificationToken(null);
        setTokenCopied(false);
        setTelegramLinked(false);
        setTelegramVerified(false);
        setVerificationCode('');
        setTelegramError(null);
        setLoadedFromExistingApplicant(false);
    }, [resetTelegramSessionOnServer]);

    const invalidateVerificationAfterPersonalChange = useCallback(async () => {
        if (!telegramVerified && !verificationToken) {
            return;
        }

        await resetTelegramSessionOnServer();
        setTelegramVerified(false);
        setTelegramLinked(false);
        setVerificationCode('');
        setVerificationToken(null);
        setTelegramError('Личные данные изменены — нажмите «Продолжить» и снова пройдите Telegram.');
    }, [telegramVerified, verificationToken, resetTelegramSessionOnServer]);

    const handlePersonalFieldChange = (field: 'name' | 'email' | 'identifier' | 'address' | 'phone', value: string) => {
        setData(field, value);
        if ((!telegramVerified && !verificationToken) || personalEditInvalidatedRef.current) {
            return;
        }
        personalEditInvalidatedRef.current = true;
        void invalidateVerificationAfterPersonalChange();
    };

    const goBackToPersonal = () => {
        void resetTelegramState().then(() => setCurrentStep('personal'));
    };

    const copyVerificationToken = async () => {
        if (!verificationToken) {
            return;
        }

        try {
            await navigator.clipboard.writeText(verificationToken);
            setTokenCopied(true);
            setTimeout(() => setTokenCopied(false), 2000);
        } catch {
            setTelegramError('Не удалось скопировать токен. Выделите и скопируйте вручную.');
        }
    };

    const pollTelegramStatus = useCallback(async () => {
        const result = await registrationJson<{ linked: boolean; verified: boolean }>(
            route('public.registration.telegram.status', examType.slug),
        );

        if (result.ok) {
            setTelegramLinked(result.data.linked);
            if (result.data.verified) {
                setTelegramVerified(true);
            }
        }
    }, [examType.slug]);

    useEffect(() => {
        if (currentStep !== 'telegram' || telegramVerified) {
            return;
        }

        pollTelegramStatus();
        const interval = setInterval(pollTelegramStatus, 3000);

        return () => clearInterval(interval);
    }, [currentStep, telegramVerified, pollTelegramStatus]);

    const formErrors = errors as Record<string, string | undefined>;

    useEffect(() => {
        if (formErrors.telegram) {
            setTelegramError(formErrors.telegram);
            setCurrentStep('telegram');
        }
    }, [formErrors.telegram]);

    const applyTelegramInitResult = (initData: {
        token?: string;
        bot_url: string | null;
        linked: boolean;
        verified: boolean;
        resumed_from_existing?: boolean;
        applicant?: {
            name: string;
            email: string;
            identifier: string;
            address: string;
            phone: string;
            graduate_organization: string;
            graduate_year: string;
            speciality: string;
        };
    }) => {
        setTelegramBotUrl(initData.bot_url);
        setVerificationToken(initData.token ?? null);
        setTokenCopied(false);
        setTelegramLinked(initData.linked);
        setTelegramVerified(initData.verified);
        setVerificationCode('');
        setExistingAccountModalOpen(false);
        setInitFieldErrors({});
        setLoadedFromExistingApplicant(initData.resumed_from_existing === true);
        personalEditInvalidatedRef.current = false;

        if (initData.applicant) {
            setData((current) => ({
                ...current,
                graduate_organization:
                    current.graduate_organization ||
                    initData.applicant!.graduate_organization ||
                    '',
                graduate_year:
                    current.graduate_year || initData.applicant!.graduate_year || '',
                speciality: current.speciality || initData.applicant!.speciality || '',
            }));
        }

        setCurrentStep('telegram');
    };

    const isExistingAccountByIdentifier = (
        fieldErrors?: Record<string, string[]>,
        canResume?: boolean,
    ) => canResume === true || Boolean(fieldErrors?.identifier?.length);

    const initTelegramVerification = async () => {
        setTelegramLoading(true);
        setTelegramError(null);
        setExistingAccountModalOpen(false);
        setInitFieldErrors({});

        const result = await registrationJson<{
            token: string;
            bot_url: string | null;
            linked: boolean;
            verified: boolean;
        }>(route('public.registration.telegram.init', examType.slug), {
            method: 'POST',
            body: JSON.stringify({
                exam_id: data.exam_id,
                name: data.name,
                email: data.email,
                identifier: data.identifier,
                address: data.address,
                phone: data.phone,
            }),
        });

        setTelegramLoading(false);

        if (!result.ok) {
            if (isExistingAccountByIdentifier(result.errors, result.can_resume)) {
                setExistingAccountMessage(result.message);
                setExistingAccountModalOpen(true);
                setTelegramError(null);
            } else {
                setTelegramError(result.message);
                if (result.errors) {
                    setInitFieldErrors(result.errors);
                }
            }
            return;
        }

        applyTelegramInitResult(result.data);
    };

    const resumeExistingAccountVerification = async () => {
        setResumeLoading(true);
        setTelegramError(null);

        const result = await registrationJson<{
            token: string;
            bot_url: string | null;
            linked: boolean;
            verified: boolean;
            resumed_from_existing: boolean;
            applicant: {
                name: string;
                email: string;
                identifier: string;
                address: string;
                phone: string;
                graduate_organization: string;
                graduate_year: string;
                speciality: string;
            };
        }>(route('public.registration.telegram.resume', examType.slug), {
            method: 'POST',
            body: JSON.stringify({
                exam_id: data.exam_id,
                name: data.name,
                email: data.email,
                identifier: data.identifier,
                address: data.address,
                phone: data.phone,
            }),
        });

        setResumeLoading(false);

        if (!result.ok) {
            setExistingAccountModalOpen(false);
            setTelegramError(result.message);
            if (result.errors) {
                setInitFieldErrors(result.errors);
            }
            return;
        }

        applyTelegramInitResult(result.data);
    };

    const handleResumeFromModal = () => {
        void resumeExistingAccountVerification();
    };

    const verifyTelegramCode = async () => {
        setVerifyLoading(true);
        setTelegramError(null);

        const result = await registrationJson<{ verified: boolean }>(
            route('public.registration.telegram.verify', examType.slug),
            {
                method: 'POST',
                body: JSON.stringify({ code: verificationCode }),
            },
        );

        setVerifyLoading(false);

        if (!result.ok) {
            setTelegramError(result.message);
            return;
        }

        setTelegramVerified(true);
    };

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post(route('public.registration.store', examType.slug));
    };

    const steps = [
        { id: 'exam', label: 'Экзамен', icon: GraduationCap },
        { id: 'personal', label: 'Личные данные', icon: User },
        { id: 'telegram', label: 'Telegram', icon: MessageCircle },
        { id: 'education', label: 'Образование', icon: BookOpen },
        { id: 'documents', label: 'Документы', icon: Upload },
    ];

    const getStepIndex = (step: string) => steps.findIndex(s => s.id === step);
    const currentStepIndex = getStepIndex(currentStep);

    return (
        <>
            <Head title={`Регистрация - ${examType.name}`} />

            <div className="min-h-screen bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-700">
                {/* Header */}
                <div className="bg-white/10 backdrop-blur-md border-b border-white/20">
                    <div className="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
                        <div className="flex items-center gap-3">
                            <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm">
                                <GraduationCap className="h-6 w-6 text-white" />
                            </div>
                            <div>
                                <h1 className="text-xl sm:text-2xl font-bold text-white">
                                    {examType.name}
                                </h1>
                                {examType.description && (
                                    <p className="text-sm text-white/80">{examType.description}</p>
                                )}
                            </div>
                        </div>
                    </div>
                </div>

                {/* Progress Steps */}
                <div className="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
                    <div className="mb-8">
                        <div className="flex items-center justify-center">
                            {steps.map((step, index) => {
                                const Icon = step.icon;
                                const isActive = currentStep === step.id;
                                const isCompleted = index < currentStepIndex;

                                return (
                                    <div key={step.id} className="flex items-center">
                                        <div className="flex flex-col items-center">
                                            <div
                                                className={`flex h-12 w-12 sm:h-14 sm:w-14 items-center justify-center rounded-full transition-all ${
                                                    isCompleted
                                                        ? 'bg-green-500 text-white'
                                                        : isActive
                                                        ? 'bg-white text-indigo-600 shadow-lg'
                                                        : 'bg-white/20 text-white/60'
                                                }`}
                                            >
                                                {isCompleted ? (
                                                    <CheckCircle2 className="h-6 w-6 sm:h-7 sm:w-7" />
                                                ) : (
                                                    <Icon className="h-6 w-6 sm:h-7 sm:w-7" />
                                                )}
                                            </div>
                                        </div>
                                        {index < steps.length - 1 && (
                                            <div className="flex items-center mx-3 sm:mx-4">
                                                <ChevronRight
                                                    className={`h-6 w-6 sm:h-8 sm:w-8 transition-all ${
                                                        isCompleted ? 'text-green-500' : 'text-white/30'
                                                    }`}
                                                />
                                            </div>
                                        )}
                                    </div>
                                );
                            })}
                        </div>
                    </div>

                    {/* Form Card */}
                    <Card className="border-0 shadow-2xl">
                        <CardContent className="p-4 sm:p-6 lg:p-8">
                            <form onSubmit={submit} className="space-y-6">
                                {/* Step 1: Exam Selection */}
                                {currentStep === 'exam' && (
                                    <div className="space-y-6">
                                        <div>
                                            <h2 className="text-2xl font-bold text-gray-900 mb-2">
                                                Выберите экзамен
                                            </h2>
                                            <p className="text-gray-600">
                                                Выберите язык, на котором вы хотите сдавать экзамен
                                            </p>
                                        </div>

                                        <RadioGroup value={data.exam_id} onValueChange={handleExamSelect}>
                                            <div className="grid gap-3">
                                                {examType.exams.map((exam) => (
                                                    <label
                                                        key={exam.id}
                                                        htmlFor={`exam-${exam.id}`}
                                                        className={`flex items-center gap-4 rounded-xl border-2 p-4 cursor-pointer transition-all ${
                                                            data.exam_id === exam.id.toString()
                                                                ? 'border-indigo-600 bg-indigo-50'
                                                                : 'border-gray-200 hover:border-indigo-300 hover:bg-gray-50'
                                                        }`}
                                                    >
                                                        <RadioGroupItem value={exam.id.toString()} id={`exam-${exam.id}`} />
                                                        <div className="flex-1">
                                                            <p className="font-semibold text-gray-900">{exam.name}</p>
                                                        </div>
                                                    </label>
                                                ))}
                                            </div>
                                        </RadioGroup>
                                        {errors.exam_id && (
                                            <p className="text-sm text-red-600">{errors.exam_id}</p>
                                        )}

                                        <Button
                                            type="button"
                                            onClick={() => setCurrentStep('personal')}
                                            disabled={!canProceedToPersonal}
                                            className="w-full"
                                            size="lg"
                                        >
                                            Продолжить
                                        </Button>
                                    </div>
                                )}

                                {/* Step 2: Personal Information */}
                                {currentStep === 'personal' && (
                                    <div className="space-y-6">
                                        <div>
                                            <h2 className="text-2xl font-bold text-gray-900 mb-2">
                                                Личные данные
                                            </h2>
                                            <p className="text-gray-600">
                                                Заполните ваши контактные данные
                                            </p>
                                        </div>

                                        {loadedFromExistingApplicant && (
                                            <div className="rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
                                                Данные подставлены из вашей существующей заявки. Проверьте их
                                                и при необходимости измените перед подтверждением в Telegram.
                                            </div>
                                        )}

                                        <div className="space-y-4">
                                            <div className="space-y-2">
                                                <Label htmlFor="name" className="flex items-center gap-2">
                                                    <User className="h-4 w-4 text-gray-500" />
                                                    ФИО *
                                                </Label>
                                                <Input
                                                    id="name"
                                                    value={data.name}
                                                    onChange={(e) => handlePersonalFieldChange('name', e.target.value)}
                                                    placeholder="Иванов Иван Иванович"
                                                    required
                                                    className="h-12"
                                                />
                                                {errors.name && (
                                                    <p className="text-sm text-red-600">{errors.name}</p>
                                                )}
                                            </div>

                                            <div className="grid gap-4 sm:grid-cols-2">
                                                <div className="space-y-2">
                                                    <Label htmlFor="identifier" className="flex items-center gap-2">
                                                        <User className="h-4 w-4 text-gray-500" />
                                                        ИИН *
                                                    </Label>
                                                    <Input
                                                        id="identifier"
                                                        value={data.identifier}
                                                        onChange={(e) => handlePersonalFieldChange('identifier', e.target.value)}
                                                        maxLength={12}
                                                        placeholder="123456789012"
                                                        required
                                                        className="h-12"
                                                    />
                                                    {(errors.identifier || initFieldErrors.identifier?.[0]) && (
                                                        <p className="text-sm text-red-600">
                                                            {errors.identifier || initFieldErrors.identifier?.[0]}
                                                        </p>
                                                    )}
                                                </div>

                                                <div className="space-y-2">
                                                    <Label htmlFor="phone" className="flex items-center gap-2">
                                                        <Phone className="h-4 w-4 text-gray-500" />
                                                        Телефон *
                                                    </Label>
                                                    <Input
                                                        id="phone"
                                                        value={data.phone}
                                                        onChange={(e) => handlePersonalFieldChange('phone', e.target.value)}
                                                        placeholder="+7 (___) ___-__-__"
                                                        required
                                                        className="h-12"
                                                    />
                                                    {errors.phone && (
                                                        <p className="text-sm text-red-600">{errors.phone}</p>
                                                    )}
                                                </div>
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="email" className="flex items-center gap-2">
                                                    <Mail className="h-4 w-4 text-gray-500" />
                                                    Email *
                                                </Label>
                                                <Input
                                                    id="email"
                                                    type="email"
                                                    value={data.email}
                                                    onChange={(e) => handlePersonalFieldChange('email', e.target.value)}
                                                    placeholder="example@mail.com"
                                                    required
                                                    className="h-12"
                                                />
                                                {(errors.email || initFieldErrors.email?.[0]) && (
                                                    <p className="text-sm text-red-600">
                                                        {errors.email || initFieldErrors.email?.[0]}
                                                    </p>
                                                )}
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="address" className="flex items-center gap-2">
                                                    <MapPin className="h-4 w-4 text-gray-500" />
                                                    Адрес *
                                                </Label>
                                                <Textarea
                                                    id="address"
                                                    value={data.address}
                                                    onChange={(e) => handlePersonalFieldChange('address', e.target.value)}
                                                    placeholder="Город, улица, дом, квартира"
                                                    required
                                                    rows={3}
                                                />
                                                {errors.address && (
                                                    <p className="text-sm text-red-600">{errors.address}</p>
                                                )}
                                            </div>
                                        </div>

                                        <div className="flex gap-3">
                                            <Button
                                                type="button"
                                                onClick={() => setCurrentStep('exam')}
                                                variant="outline"
                                                className="flex-1"
                                                size="lg"
                                            >
                                                Назад
                                            </Button>
                                            <Button
                                                type="button"
                                                onClick={initTelegramVerification}
                                                disabled={!canProceedToTelegram || telegramLoading}
                                                className="flex-1"
                                                size="lg"
                                            >
                                                {telegramLoading ? (
                                                    <>
                                                        <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                                        Проверка...
                                                    </>
                                                ) : (
                                                    'Продолжить'
                                                )}
                                            </Button>
                                        </div>
                                        {telegramError && (
                                            <p className="text-sm text-red-600">{telegramError}</p>
                                        )}
                                    </div>
                                )}

                                {currentStep === 'telegram' && (
                                    <div className="space-y-6">
                                        <div>
                                            <h2 className="text-2xl font-bold text-gray-900 mb-2">
                                                Подтверждение через Telegram
                                            </h2>
                                            <p className="text-gray-600">
                                                Привяжите Telegram и введите код из бота. Без этого шага продолжить регистрацию нельзя.
                                            </p>
                                        </div>

                                        {loadedFromExistingApplicant && (
                                            <div className="rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
                                                Вы продолжаете регистрацию существующей заявки. Личные данные должны
                                                совпадать с теми, что вы подтвердите в Telegram.
                                            </div>
                                        )}

                                        <div className="space-y-4 rounded-xl border border-indigo-100 bg-indigo-50/50 p-4 sm:p-5">
                                            {verificationToken && (
                                                <div className="space-y-2">
                                                    <Label className="text-gray-800">Токен верификации</Label>
                                                    <div className="flex gap-2">
                                                        <Input
                                                            readOnly
                                                            value={verificationToken}
                                                            className="h-12 font-mono text-sm bg-white"
                                                        />
                                                        <Button
                                                            type="button"
                                                            variant="outline"
                                                            size="icon"
                                                            className="h-12 w-12 shrink-0"
                                                            onClick={copyVerificationToken}
                                                            title="Скопировать токен"
                                                        >
                                                            {tokenCopied ? (
                                                                <Check className="h-4 w-4 text-green-600" />
                                                            ) : (
                                                                <Copy className="h-4 w-4" />
                                                            )}
                                                        </Button>
                                                    </div>
                                                    <p className="text-xs text-gray-600">
                                                        Скопируйте токен — он понадобится в боте
                                                    </p>
                                                </div>
                                            )}

                                            <ol className="list-decimal list-inside space-y-2 text-sm text-gray-700">
                                                <li>Откройте бота в Telegram</li>
                                                <li>Нажмите «Получить код верификации»</li>
                                                <li>Отправьте боту скопированный токен</li>
                                                <li>Введите полученный код ниже на сайте</li>
                                            </ol>

                                            {telegramBotUrl ? (
                                                <Button type="button" variant="outline" className="w-full" size="lg" asChild>
                                                    <a href={telegramBotUrl} target="_blank" rel="noopener noreferrer">
                                                        <ExternalLink className="mr-2 h-4 w-4" />
                                                        Открыть{' '}
                                                        {telegramBotUsername
                                                            ? `@${telegramBotUsername.replace(/^@/, '')}`
                                                            : 'Telegram-бота'}
                                                    </a>
                                                </Button>
                                            ) : (
                                                <p className="text-sm text-amber-700">
                                                    Бот не настроен (TELEGRAM_BOT_USERNAME). Обратитесь к администратору.
                                                </p>
                                            )}

                                            <div
                                                className={`flex items-center gap-2 text-sm ${
                                                    telegramLinked ? 'text-green-700' : 'text-gray-600'
                                                }`}
                                            >
                                                {telegramLinked ? (
                                                    <CheckCircle2 className="h-4 w-4 shrink-0" />
                                                ) : (
                                                    <Loader2 className="h-4 w-4 shrink-0 animate-spin" />
                                                )}
                                                {telegramLinked
                                                    ? 'Токен принят — проверьте код в Telegram'
                                                    : 'Ожидаем отправку токена в боте...'}
                                            </div>
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="verification_code">Код из Telegram *</Label>
                                            <Input
                                                id="verification_code"
                                                value={verificationCode}
                                                onChange={(e) =>
                                                    setVerificationCode(e.target.value.replace(/\D/g, '').slice(0, 6))
                                                }
                                                placeholder="000000"
                                                maxLength={6}
                                                inputMode="numeric"
                                                className="h-12 text-center text-lg tracking-widest font-mono"
                                                disabled={!telegramLinked || telegramVerified}
                                            />
                                        </div>

                                        {telegramError && (
                                            <p className="text-sm text-red-600">{telegramError}</p>
                                        )}

                                        {formErrors.telegram && (
                                            <p className="text-sm text-red-600">{formErrors.telegram}</p>
                                        )}

                                        {telegramVerified && (
                                            <div className="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                                                Telegram подтверждён. Можно перейти к следующему шагу.
                                            </div>
                                        )}

                                        <div className="flex flex-col gap-3 sm:flex-row">
                                            <Button
                                                type="button"
                                                onClick={goBackToPersonal}
                                                variant="outline"
                                                className="flex-1"
                                                size="lg"
                                            >
                                                Назад
                                            </Button>
                                            {!telegramVerified && (
                                                <Button
                                                    type="button"
                                                    onClick={verifyTelegramCode}
                                                    disabled={
                                                        !telegramLinked ||
                                                        verificationCode.length !== 6 ||
                                                        verifyLoading
                                                    }
                                                    className="flex-1"
                                                    size="lg"
                                                >
                                                    {verifyLoading ? (
                                                        <>
                                                            <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                                            Проверка...
                                                        </>
                                                    ) : (
                                                        'Подтвердить код'
                                                    )}
                                                </Button>
                                            )}
                                            {telegramVerified && (
                                                <Button
                                                    type="button"
                                                    onClick={() => setCurrentStep('education')}
                                                    className="flex-1"
                                                    size="lg"
                                                >
                                                    Продолжить
                                                </Button>
                                            )}
                                        </div>
                                    </div>
                                )}

                                {/* Step 4: Education */}
                                {currentStep === 'education' && (
                                    <div className="space-y-6">
                                        <div>
                                            <h2 className="text-2xl font-bold text-gray-900 mb-2">
                                                Образование
                                            </h2>
                                            <p className="text-gray-600">
                                                Информация о вашем образовании
                                            </p>
                                        </div>

                                        <div className="space-y-4">
                                            <div className="space-y-2">
                                                <Label htmlFor="graduate_organization" className="flex items-center gap-2">
                                                    <Building2 className="h-4 w-4 text-gray-500" />
                                                    Учебное заведение *
                                                </Label>
                                                <Input
                                                    id="graduate_organization"
                                                    value={data.graduate_organization}
                                                    onChange={(e) => setData('graduate_organization', e.target.value)}
                                                    placeholder="Название университета/колледжа"
                                                    required
                                                    className="h-12"
                                                />
                                                {errors.graduate_organization && (
                                                    <p className="text-sm text-red-600">{errors.graduate_organization}</p>
                                                )}
                                            </div>

                                            <div className="grid gap-4 sm:grid-cols-2">
                                                <div className="space-y-2">
                                                    <Label htmlFor="graduate_year" className="flex items-center gap-2">
                                                        <Calendar className="h-4 w-4 text-gray-500" />
                                                        Год окончания *
                                                    </Label>
                                                    <Input
                                                        id="graduate_year"
                                                        value={data.graduate_year}
                                                        onChange={(e) => setData('graduate_year', e.target.value)}
                                                        placeholder="2024"
                                                        required
                                                        className="h-12"
                                                    />
                                                    {errors.graduate_year && (
                                                        <p className="text-sm text-red-600">{errors.graduate_year}</p>
                                                    )}
                                                </div>

                                                <div className="space-y-2">
                                                    <Label htmlFor="speciality" className="flex items-center gap-2">
                                                        <GraduationCap className="h-4 w-4 text-gray-500" />
                                                        Специальность *
                                                    </Label>
                                                    <Input
                                                        id="speciality"
                                                        value={data.speciality}
                                                        onChange={(e) => setData('speciality', e.target.value)}
                                                        placeholder="Ваша специальность"
                                                        required
                                                        className="h-12"
                                                    />
                                                    {errors.speciality && (
                                                        <p className="text-sm text-red-600">{errors.speciality}</p>
                                                    )}
                                                </div>
                                            </div>
                                        </div>

                                        <div className="flex gap-3">
                                            <Button
                                                type="button"
                                                onClick={() => setCurrentStep('telegram')}
                                                variant="outline"
                                                className="flex-1"
                                                size="lg"
                                            >
                                                Назад
                                            </Button>
                                            <Button
                                                type="button"
                                                onClick={() => setCurrentStep('documents')}
                                                disabled={!canProceedToDocuments}
                                                className="flex-1"
                                                size="lg"
                                            >
                                                Продолжить
                                            </Button>
                                        </div>
                                    </div>
                                )}

                                {/* Step 4: Documents */}
                                {currentStep === 'documents' && (
                                    <div className="space-y-6">
                                        <div>
                                            <h2 className="text-2xl font-bold text-gray-900 mb-2">
                                                Документы
                                            </h2>
                                            <p className="text-gray-600">
                                                Загрузите фото документов (необязательно). Снимки с телефона
                                                автоматически сжимаются перед отправкой.
                                            </p>
                                        </div>

                                        {errors.documents && (
                                            <p className="text-sm text-red-600">{errors.documents}</p>
                                        )}

                                        <div className="grid gap-4 sm:grid-cols-2">
                                            <div className="space-y-2">
                                                <Label htmlFor="document_front">Документ (лицевая)</Label>
                                                <Input
                                                    id="document_front"
                                                    type="file"
                                                    accept="image/*"
                                                    disabled={compressingField === 'document_front'}
                                                    onChange={(e) =>
                                                        void handleDocumentFile(
                                                            'document_front',
                                                            e.target.files?.[0] || null,
                                                        )
                                                    }
                                                    className="h-12"
                                                />
                                                {compressingField === 'document_front' && (
                                                    <p className="text-xs text-gray-500">Сжатие изображения…</p>
                                                )}
                                                {errors.document_front && (
                                                    <p className="text-sm text-red-600">{errors.document_front}</p>
                                                )}
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="document_back">Документ (обратная)</Label>
                                                <Input
                                                    id="document_back"
                                                    type="file"
                                                    accept="image/*"
                                                    disabled={compressingField === 'document_back'}
                                                    onChange={(e) =>
                                                        void handleDocumentFile(
                                                            'document_back',
                                                            e.target.files?.[0] || null,
                                                        )
                                                    }
                                                    className="h-12"
                                                />
                                                {compressingField === 'document_back' && (
                                                    <p className="text-xs text-gray-500">Сжатие изображения…</p>
                                                )}
                                                {errors.document_back && (
                                                    <p className="text-sm text-red-600">{errors.document_back}</p>
                                                )}
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="diplom">Диплом</Label>
                                                <Input
                                                    id="diplom"
                                                    type="file"
                                                    accept="image/*"
                                                    disabled={compressingField === 'diplom'}
                                                    onChange={(e) =>
                                                        void handleDocumentFile('diplom', e.target.files?.[0] || null)
                                                    }
                                                    className="h-12"
                                                />
                                                {compressingField === 'diplom' && (
                                                    <p className="text-xs text-gray-500">Сжатие изображения…</p>
                                                )}
                                                {errors.diplom && (
                                                    <p className="text-sm text-red-600">{errors.diplom}</p>
                                                )}
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="certificate">Сертификат</Label>
                                                <Input
                                                    id="certificate"
                                                    type="file"
                                                    accept="image/*"
                                                    disabled={compressingField === 'certificate'}
                                                    onChange={(e) =>
                                                        void handleDocumentFile(
                                                            'certificate',
                                                            e.target.files?.[0] || null,
                                                        )
                                                    }
                                                    className="h-12"
                                                />
                                                {compressingField === 'certificate' && (
                                                    <p className="text-xs text-gray-500">Сжатие изображения…</p>
                                                )}
                                                {errors.certificate && (
                                                    <p className="text-sm text-red-600">{errors.certificate}</p>
                                                )}
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="photo">Фото</Label>
                                                <Input
                                                    id="photo"
                                                    type="file"
                                                    accept="image/*"
                                                    disabled={compressingField === 'photo'}
                                                    onChange={(e) =>
                                                        void handleDocumentFile('photo', e.target.files?.[0] || null)
                                                    }
                                                    className="h-12"
                                                />
                                                {compressingField === 'photo' && (
                                                    <p className="text-xs text-gray-500">Сжатие изображения…</p>
                                                )}
                                                {errors.photo && (
                                                    <p className="text-sm text-red-600">{errors.photo}</p>
                                                )}
                                            </div>
                                        </div>

                                        <div className="rounded-lg border border-blue-200 bg-blue-50 p-4">
                                            <p className="text-sm text-blue-800">
                                                <strong>Обратите внимание:</strong> После отправки заявки ваши данные будут проверены.
                                                После одобрения ссылка на экзамен придёт в Telegram.
                                            </p>
                                        </div>

                                        <div className="flex gap-3">
                                            <Button
                                                type="button"
                                                onClick={() => setCurrentStep('education')}
                                                variant="outline"
                                                className="flex-1"
                                                size="lg"
                                            >
                                                Назад
                                            </Button>
                                            <Button
                                                type="submit"
                                                disabled={processing || compressingField !== null}
                                                className="flex-1"
                                                size="lg"
                                            >
                                                {processing ? 'Отправка...' : 'Зарегистрироваться'}
                                            </Button>
                                        </div>
                                    </div>
                                )}
                            </form>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <Dialog open={existingAccountModalOpen} onOpenChange={setExistingAccountModalOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>У вас уже есть аккаунт</DialogTitle>
                        <DialogDescription>
                            {existingAccountMessage ||
                                'Заявка с таким ИИН уже зарегистрирована. Перейдите к подтверждению через Telegram, чтобы продолжить регистрацию.'}
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter className="gap-2 sm:gap-0">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => setExistingAccountModalOpen(false)}
                            disabled={resumeLoading}
                        >
                            Отмена
                        </Button>
                        <Button
                            type="button"
                            onClick={handleResumeFromModal}
                            disabled={resumeLoading || !data.identifier}
                        >
                            {resumeLoading ? (
                                <>
                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                    Загрузка...
                                </>
                            ) : (
                                'Перейти к подтверждению'
                            )}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}
