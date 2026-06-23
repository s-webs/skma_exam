import { Head, useForm } from '@inertiajs/react';
import { FormEvent, useCallback, useEffect, useMemo, useRef, useState } from 'react';
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
import { useTranslation } from 'react-i18next';
import LanguageSwitcher from '@/components/language-switcher';
import i18n from '@/i18n/config';

type RegistrationStep = 'exam' | 'personal' | 'telegram' | 'email' | 'education' | 'documents';

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
            message: firstFieldError ?? body.message ?? i18n.t('publicRegistration.genericError'),
            errors,
            can_resume: body.can_resume === true,
        };
    }

    return { ok: true, data: body as T };
}

import { getLocalizedName, LocalizedNameFields } from '@/lib/localized-name';

interface Exam extends LocalizedNameFields {
    id: number;
    language: string;
    require_telegram_verification: boolean;
}

interface ExamType extends LocalizedNameFields {
    id: number;
    slug: string;
    description: string | null;
    exams: Exam[];
}

interface RegistrationIndexProps {
    examType: ExamType;
    telegramBotUsername: string | null;
}

export default function Index({ examType, telegramBotUsername }: RegistrationIndexProps) {
    const { t, i18n } = useTranslation();
    const [selectedExam, setSelectedExam] = useState<number | null>(null);
    const [currentStep, setCurrentStep] = useState<RegistrationStep>('exam');
    const [telegramBotUrl, setTelegramBotUrl] = useState<string | null>(null);
    const [verificationToken, setVerificationToken] = useState<string | null>(null);
    const [tokenCopied, setTokenCopied] = useState(false);
    const [telegramLinked, setTelegramLinked] = useState(false);
    const [telegramVerified, setTelegramVerified] = useState(false);
    const [verificationCode, setVerificationCode] = useState('');
    const [verificationError, setVerificationError] = useState<string | null>(null);
    const [verificationLoading, setVerificationLoading] = useState(false);
    const [emailSentTo, setEmailSentTo] = useState<string | null>(null);
    const [resendLoading, setResendLoading] = useState(false);
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
        | 'certificate';

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
    });

    const handleExamSelect = (examId: string) => {
        setSelectedExam(Number(examId));
        setData('exam_id', examId);
        void resetVerificationState();
    };

    const selectedExamConfig = useMemo(
        () => examType.exams.find((exam) => exam.id.toString() === data.exam_id) ?? null,
        [examType.exams, data.exam_id],
    );

    const requiresTelegram = selectedExamConfig?.require_telegram_verification ?? true;
    const verificationStep: 'telegram' | 'email' = requiresTelegram ? 'telegram' : 'email';

    const canProceedToPersonal = data.exam_id !== '';
    const canProceedToVerification = data.name && data.email && data.identifier && data.phone && data.address;
    const canProceedToEducation = telegramVerified;
    const canProceedToDocuments = data.graduate_organization && data.graduate_year && data.speciality;
    const canSubmitDocuments =
        data.document_front !== null &&
        data.document_back !== null &&
        data.diplom !== null &&
        data.certificate !== null;

    const resetTelegramSessionOnServer = useCallback(async () => {
        await registrationJson(route('public.registration.telegram.reset', examType.slug), {
            method: 'POST',
        });
    }, [examType.slug]);

    const resetEmailSessionOnServer = useCallback(async () => {
        await registrationJson(route('public.registration.email.reset', examType.slug), {
            method: 'POST',
        });
    }, [examType.slug]);

    const resetVerificationSessionOnServer = useCallback(async () => {
        await Promise.all([resetTelegramSessionOnServer(), resetEmailSessionOnServer()]);
    }, [resetTelegramSessionOnServer, resetEmailSessionOnServer]);

    const resetVerificationState = useCallback(async () => {
        await resetVerificationSessionOnServer();
        setTelegramBotUrl(null);
        setVerificationToken(null);
        setTokenCopied(false);
        setTelegramLinked(false);
        setTelegramVerified(false);
        setVerificationCode('');
        setVerificationError(null);
        setEmailSentTo(null);
        setLoadedFromExistingApplicant(false);
    }, [resetVerificationSessionOnServer]);

    const invalidateVerificationAfterPersonalChange = useCallback(async () => {
        if (!telegramVerified && !verificationToken && !emailSentTo) {
            return;
        }

        await resetVerificationSessionOnServer();
        setTelegramVerified(false);
        setTelegramLinked(false);
        setVerificationCode('');
        setVerificationToken(null);
        setEmailSentTo(null);
        setVerificationError(
            requiresTelegram
                ? t('publicRegistration.personalChangedTelegram')
                : t('publicRegistration.personalChangedEmail'),
        );
    }, [telegramVerified, verificationToken, emailSentTo, resetVerificationSessionOnServer, requiresTelegram, t]);

    const handlePersonalFieldChange = (field: 'name' | 'email' | 'identifier' | 'address' | 'phone', value: string) => {
        setData(field, value);
        if ((!telegramVerified && !verificationToken && !emailSentTo) || personalEditInvalidatedRef.current) {
            return;
        }
        personalEditInvalidatedRef.current = true;
        void invalidateVerificationAfterPersonalChange();
    };

    const goBackToPersonal = () => {
        void resetVerificationState().then(() => setCurrentStep('personal'));
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
            setVerificationError(t('publicRegistration.copyTokenFailed'));
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
            setVerificationError(formErrors.telegram);
            setCurrentStep('telegram');
        }
        if (formErrors.email) {
            setVerificationError(formErrors.email);
            setCurrentStep('email');
        }
    }, [formErrors.telegram, formErrors.email]);

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
            const applicant = initData.applicant;
            setData((current) => ({
                ...current,
                name: current.name || applicant.name || '',
                email: current.email || applicant.email || '',
                identifier: current.identifier || applicant.identifier || '',
                address: current.address || applicant.address || '',
                phone: current.phone || applicant.phone || '',
                graduate_organization:
                    current.graduate_organization || applicant.graduate_organization || '',
                graduate_year: current.graduate_year || applicant.graduate_year || '',
                speciality: current.speciality || applicant.speciality || '',
            }));
        }

        setCurrentStep('telegram');
    };

    const applyEmailInitResult = (initData: {
        email: string;
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
        setEmailSentTo(initData.email);
        setTelegramVerified(initData.verified);
        setVerificationCode('');
        setExistingAccountModalOpen(false);
        setInitFieldErrors({});
        setLoadedFromExistingApplicant(initData.resumed_from_existing === true);
        personalEditInvalidatedRef.current = false;

        if (initData.applicant) {
            const applicant = initData.applicant;
            setData((current) => ({
                ...current,
                name: current.name || applicant.name || '',
                email: current.email || applicant.email || '',
                identifier: current.identifier || applicant.identifier || '',
                address: current.address || applicant.address || '',
                phone: current.phone || applicant.phone || '',
                graduate_organization:
                    current.graduate_organization || applicant.graduate_organization || '',
                graduate_year: current.graduate_year || applicant.graduate_year || '',
                speciality: current.speciality || applicant.speciality || '',
            }));
        }

        setCurrentStep('email');
    };

    const isExistingAccountByIdentifier = (
        fieldErrors?: Record<string, string[]>,
        canResume?: boolean,
    ) => canResume === true || Boolean(fieldErrors?.identifier?.length);

    const initTelegramVerification = async () => {
        setVerificationLoading(true);
        setVerificationError(null);
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

        setVerificationLoading(false);

        if (!result.ok) {
            if (isExistingAccountByIdentifier(result.errors, result.can_resume)) {
                setExistingAccountMessage(result.message);
                setExistingAccountModalOpen(true);
                setVerificationError(null);
            } else {
                setVerificationError(result.message);
                if (result.errors) {
                    setInitFieldErrors(result.errors);
                }
            }
            return;
        }

        applyTelegramInitResult(result.data);
    };

    const initEmailVerification = async () => {
        setVerificationLoading(true);
        setVerificationError(null);
        setExistingAccountModalOpen(false);
        setInitFieldErrors({});

        const result = await registrationJson<{
            email: string;
            verified: boolean;
        }>(route('public.registration.email.init', examType.slug), {
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

        setVerificationLoading(false);

        if (!result.ok) {
            if (isExistingAccountByIdentifier(result.errors, result.can_resume)) {
                setExistingAccountMessage(result.message);
                setExistingAccountModalOpen(true);
                setVerificationError(null);
            } else {
                setVerificationError(result.message);
                if (result.errors) {
                    setInitFieldErrors(result.errors);
                }
            }
            return;
        }

        applyEmailInitResult(result.data);
    };

    const initVerification = () => {
        if (requiresTelegram) {
            void initTelegramVerification();
        } else {
            void initEmailVerification();
        }
    };

    const resumeExistingAccountVerification = async () => {
        setResumeLoading(true);
        setVerificationError(null);

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
        }>(
            requiresTelegram
                ? route('public.registration.telegram.resume', examType.slug)
                : route('public.registration.email.resume', examType.slug),
            {
                method: 'POST',
                body: JSON.stringify({
                    exam_id: data.exam_id,
                    name: data.name,
                    email: data.email,
                    identifier: data.identifier,
                    address: data.address,
                    phone: data.phone,
                }),
            },
        );

        setResumeLoading(false);

        if (!result.ok) {
            setExistingAccountModalOpen(false);
            setVerificationError(result.message);
            if (result.errors) {
                setInitFieldErrors(result.errors);
            }
            return;
        }

        if (requiresTelegram) {
            applyTelegramInitResult(result.data);
        } else {
            applyEmailInitResult({
                email: result.data.applicant?.email ?? data.email,
                verified: result.data.verified,
                resumed_from_existing: result.data.resumed_from_existing,
                applicant: result.data.applicant,
            });
        }
    };

    const handleResumeFromModal = () => {
        void resumeExistingAccountVerification();
    };

    const verifyTelegramCode = async () => {
        setVerifyLoading(true);
        setVerificationError(null);

        const result = await registrationJson<{ verified: boolean }>(
            route('public.registration.telegram.verify', examType.slug),
            {
                method: 'POST',
                body: JSON.stringify({ code: verificationCode }),
            },
        );

        setVerifyLoading(false);

        if (!result.ok) {
            setVerificationError(result.message);
            return;
        }

        setTelegramVerified(true);
    };

    const verifyEmailCode = async () => {
        setVerifyLoading(true);
        setVerificationError(null);

        const result = await registrationJson<{ verified: boolean }>(
            route('public.registration.email.verify', examType.slug),
            {
                method: 'POST',
                body: JSON.stringify({ code: verificationCode }),
            },
        );

        setVerifyLoading(false);

        if (!result.ok) {
            setVerificationError(result.message);
            return;
        }

        setTelegramVerified(true);
    };

    const resendEmailCode = async () => {
        setResendLoading(true);
        setVerificationError(null);

        const result = await registrationJson<{ message: string }>(
            route('public.registration.email.resend', examType.slug),
            { method: 'POST' },
        );

        setResendLoading(false);

        if (!result.ok) {
            setVerificationError(result.message);
            return;
        }

        setVerificationError(null);
    };

    const submit = (e: FormEvent) => {
        e.preventDefault();

        if (loadedFromExistingApplicant) {
            if (currentStep !== 'education') {
                return;
            }
        } else if (currentStep !== 'documents') {
            return;
        }

        post(route('public.registration.store', examType.slug));
    };

    const allSteps = useMemo(() => {
        const verification = requiresTelegram
            ? { id: 'telegram' as const, label: 'Telegram', icon: MessageCircle }
            : { id: 'email' as const, label: 'Email', icon: Mail };

        return [
            { id: 'exam' as const, label: t('publicRegistration.steps.exam'), icon: GraduationCap },
            { id: 'personal' as const, label: t('publicRegistration.steps.personal'), icon: User },
            verification,
            { id: 'education' as const, label: t('publicRegistration.steps.education'), icon: BookOpen },
            { id: 'documents' as const, label: t('publicRegistration.steps.documents'), icon: Upload },
        ];
    }, [requiresTelegram, t]);

    const steps = useMemo(
        () =>
            loadedFromExistingApplicant
                ? allSteps.filter((step) => step.id !== 'documents')
                : allSteps,
        [allSteps, loadedFromExistingApplicant],
    );

    const getStepIndex = (step: RegistrationStep) => steps.findIndex((s) => s.id === step);
    const currentStepIndex = getStepIndex(currentStep);

    useEffect(() => {
        if (loadedFromExistingApplicant && currentStep === 'documents') {
            setCurrentStep('education');
        }
    }, [loadedFromExistingApplicant, currentStep]);

    return (
        <>
            <Head title={`${t('publicRegistration.pageTitle')} - ${getLocalizedName(examType, i18n.language)}`} />

            <div className="min-h-screen bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-700">
                {/* Header */}
                <div className="bg-white/10 backdrop-blur-md border-b border-white/20">
                    <div className="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
                        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div className="flex items-center gap-3">
                                <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm">
                                    <GraduationCap className="h-6 w-6 text-white" />
                                </div>
                                <div className="min-w-0">
                                    <h1 className="text-xl font-bold text-white sm:text-2xl">
                                        {getLocalizedName(examType, i18n.language)}
                                    </h1>
                                    {examType.description && (
                                        <p className="text-sm text-white/80">{examType.description}</p>
                                    )}
                                </div>
                            </div>
                            <div className="flex w-full justify-center sm:w-auto sm:ml-auto">
                                <LanguageSwitcher />
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
                                                className={`flex h-9 w-9 sm:h-14 sm:w-14 items-center justify-center rounded-full transition-all ${
                                                    isCompleted
                                                        ? 'bg-green-500 text-white'
                                                        : isActive
                                                        ? 'bg-white text-indigo-600 shadow-lg'
                                                        : 'bg-white/20 text-white/60'
                                                }`}
                                            >
                                                {isCompleted ? (
                                                    <CheckCircle2 className="h-4 w-4 sm:h-7 sm:w-7" />
                                                ) : (
                                                    <Icon className="h-4 w-4 sm:h-7 sm:w-7" />
                                                )}
                                            </div>
                                        </div>
                                        {index < steps.length - 1 && (
                                            <div className="flex items-center mx-1.5 sm:mx-4">
                                                <ChevronRight
                                                    className={`h-4 w-4 sm:h-8 sm:w-8 transition-all ${
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
                                                {t('publicRegistration.exam.title')}
                                            </h2>
                                            <p className="text-gray-600">
                                                {t('publicRegistration.exam.description')}
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
                                                            <p className="font-semibold text-gray-900">
                                                                {getLocalizedName(exam, exam.language)}
                                                            </p>
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
                                            {t('publicRegistration.actions.continue')}
                                        </Button>
                                    </div>
                                )}

                                {/* Step 2: Personal Information */}
                                {currentStep === 'personal' && (
                                    <div className="space-y-6">
                                        <div>
                                            <h2 className="text-2xl font-bold text-gray-900 mb-2">
                                                {t('publicRegistration.personal.title')}
                                            </h2>
                                            <p className="text-gray-600">
                                                {t('publicRegistration.personal.description')}
                                            </p>
                                        </div>

                                        {loadedFromExistingApplicant && (
                                            <div className="rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
                                                {t('publicRegistration.personal.existingHint', {
                                                    channel: requiresTelegram
                                                        ? t('publicRegistration.personal.viaTelegram')
                                                        : t('publicRegistration.personal.viaEmail'),
                                                })}
                                            </div>
                                        )}

                                        <div className="space-y-4">
                                            <div className="space-y-2">
                                                <Label htmlFor="name" className="flex items-center gap-2">
                                                    <User className="h-4 w-4 text-gray-500" />
                                                    {t('publicRegistration.personal.fullName')} *
                                                </Label>
                                                <Input
                                                    id="name"
                                                    value={data.name}
                                                    onChange={(e) => handlePersonalFieldChange('name', e.target.value)}
                                                    placeholder={t('publicRegistration.personal.fullNamePlaceholder')}
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
                                                        {t('publicRegistration.personal.iin')} *
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
                                                        {t('publicRegistration.personal.phone')} *
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
                                                    {t('publicRegistration.personal.address')} *
                                                </Label>
                                                <Textarea
                                                    id="address"
                                                    value={data.address}
                                                    onChange={(e) => handlePersonalFieldChange('address', e.target.value)}
                                                    placeholder={t('publicRegistration.personal.addressPlaceholder')}
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
                                            {t('publicRegistration.actions.back')}
                                            </Button>
                                            <Button
                                                type="button"
                                                onClick={initVerification}
                                                disabled={!canProceedToVerification || verificationLoading}
                                                className="flex-1"
                                                size="lg"
                                            >
                                                {verificationLoading ? (
                                                    <>
                                                        <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                                        {t('publicRegistration.actions.checking')}
                                                    </>
                                                ) : (
                                                    t('publicRegistration.actions.continue')
                                                )}
                                            </Button>
                                        </div>
                                        {verificationError && (
                                            <p className="text-sm text-red-600">{verificationError}</p>
                                        )}
                                    </div>
                                )}

                                {currentStep === 'telegram' && (
                                    <div className="space-y-6">
                                        <div>
                                            <h2 className="text-2xl font-bold text-gray-900 mb-2">
                                                {t('publicRegistration.telegram.title')}
                                            </h2>
                                            <p className="text-gray-600">
                                                {t('publicRegistration.telegram.description')}
                                            </p>
                                        </div>

                                        {loadedFromExistingApplicant && (
                                            <div className="rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
                                                {t('publicRegistration.telegram.resumeHint')}
                                            </div>
                                        )}

                                        <div className="space-y-4 rounded-xl border border-indigo-100 bg-indigo-50/50 p-4 sm:p-5">
                                            {verificationToken && (
                                                <div className="space-y-2">
                                                    <Label className="text-gray-800">{t('publicRegistration.telegram.tokenLabel')}</Label>
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
                                                            title={t('publicRegistration.telegram.copyToken')}
                                                        >
                                                            {tokenCopied ? (
                                                                <Check className="h-4 w-4 text-green-600" />
                                                            ) : (
                                                                <Copy className="h-4 w-4" />
                                                            )}
                                                        </Button>
                                                    </div>
                                                    <p className="text-xs text-gray-600">
                                                        {t('publicRegistration.telegram.copyTokenHint')}
                                                    </p>
                                                </div>
                                            )}

                                            <ol className="list-decimal list-inside space-y-2 text-sm text-gray-700">
                                                <li>{t('publicRegistration.telegram.step1')}</li>
                                                <li>{t('publicRegistration.telegram.step2')}</li>
                                                <li>{t('publicRegistration.telegram.step3')}</li>
                                                <li>{t('publicRegistration.telegram.step4')}</li>
                                            </ol>

                                            {telegramBotUrl ? (
                                                <Button type="button" className="w-full" size="lg" asChild>
                                                    <a href={telegramBotUrl} target="_blank" rel="noopener noreferrer">
                                                        <ExternalLink className="mr-2 h-4 w-4" />
                                                        {t('publicRegistration.telegram.openBot')}{' '}
                                                        {telegramBotUsername
                                                            ? `@${telegramBotUsername.replace(/^@/, '')}`
                                                            : t('publicRegistration.telegram.telegramBot')}
                                                    </a>
                                                </Button>
                                            ) : (
                                                <p className="text-sm text-amber-700">
                                                    {t('publicRegistration.telegram.botNotConfigured')}
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
                                                    ? t('publicRegistration.telegram.tokenAccepted')
                                                    : t('publicRegistration.telegram.waitingToken')}
                                            </div>
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="verification_code">{t('publicRegistration.telegram.codeLabel')} *</Label>
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

                                        {verificationError && (
                                            <p className="text-sm text-red-600">{verificationError}</p>
                                        )}

                                        {formErrors.telegram && (
                                            <p className="text-sm text-red-600">{formErrors.telegram}</p>
                                        )}

                                        {telegramVerified && (
                                            <div className="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                                                {t('publicRegistration.telegram.verified')}
                                            </div>
                                        )}

                                        <div className="flex gap-3">
                                            <Button
                                                type="button"
                                                onClick={goBackToPersonal}
                                                variant="outline"
                                                className="flex-1"
                                                size="lg"
                                            >
                                            {t('publicRegistration.actions.back')}
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
                                                            {t('publicRegistration.actions.checking')}
                                                        </>
                                                    ) : (
                                                        t('publicRegistration.actions.confirmCode')
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
                                                    {t('publicRegistration.actions.continue')}
                                                </Button>
                                            )}
                                        </div>
                                    </div>
                                )}

                                {currentStep === 'email' && (
                                    <div className="space-y-6">
                                        <div>
                                            <h2 className="text-2xl font-bold text-gray-900 mb-2">
                                                {t('publicRegistration.emailVerify.title')}
                                            </h2>
                                            <p className="text-gray-600">
                                                {t('publicRegistration.emailVerify.description')}
                                            </p>
                                        </div>

                                        {loadedFromExistingApplicant && (
                                            <div className="rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
                                                {t('publicRegistration.emailVerify.resumeHint')}
                                            </div>
                                        )}

                                        <div className="rounded-xl border border-indigo-100 bg-indigo-50/50 p-4 sm:p-5 space-y-3">
                                            <p className="text-sm text-gray-700">
                                                {t('publicRegistration.emailVerify.codeSentTo')}{' '}
                                                <strong>{emailSentTo ?? data.email}</strong>
                                            </p>
                                            <Button
                                                type="button"
                                                variant="outline"
                                                onClick={resendEmailCode}
                                                disabled={resendLoading}
                                            >
                                                {resendLoading ? (
                                                    <>
                                                        <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                                        {t('publicRegistration.actions.sending')}
                                                    </>
                                                ) : (
                                                    t('publicRegistration.emailVerify.resendCode')
                                                )}
                                            </Button>
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="email_verification_code">{t('publicRegistration.emailVerify.codeLabel')} *</Label>
                                            <Input
                                                id="email_verification_code"
                                                value={verificationCode}
                                                onChange={(e) =>
                                                    setVerificationCode(e.target.value.replace(/\D/g, '').slice(0, 6))
                                                }
                                                placeholder="000000"
                                                maxLength={6}
                                                inputMode="numeric"
                                                className="h-12 text-center text-lg tracking-widest font-mono"
                                                disabled={telegramVerified}
                                            />
                                        </div>

                                        {verificationError && (
                                            <p className="text-sm text-red-600">{verificationError}</p>
                                        )}

                                        {formErrors.email && (
                                            <p className="text-sm text-red-600">{formErrors.email}</p>
                                        )}

                                        {telegramVerified && (
                                            <div className="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                                                {t('publicRegistration.emailVerify.verified')}
                                            </div>
                                        )}

                                        <div className="flex gap-3">
                                            <Button
                                                type="button"
                                                onClick={goBackToPersonal}
                                                variant="outline"
                                                className="flex-1"
                                                size="lg"
                                            >
                                            {t('publicRegistration.actions.back')}
                                            </Button>
                                            {!telegramVerified && (
                                                <Button
                                                    type="button"
                                                    onClick={verifyEmailCode}
                                                    disabled={
                                                        verificationCode.length !== 6 || verifyLoading
                                                    }
                                                    className="flex-1"
                                                    size="lg"
                                                >
                                                    {verifyLoading ? (
                                                        <>
                                                            <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                                            {t('publicRegistration.actions.checking')}
                                                        </>
                                                    ) : (
                                                        t('publicRegistration.actions.confirmCode')
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
                                                    {t('publicRegistration.actions.continue')}
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
                                                {t('publicRegistration.education.title')}
                                            </h2>
                                            <p className="text-gray-600">
                                                {t('publicRegistration.education.description')}
                                            </p>
                                        </div>

                                        {loadedFromExistingApplicant && (
                                            <div className="rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
                                                {t('publicRegistration.education.existingDocsHint')}
                                            </div>
                                        )}

                                        <div className="space-y-4">
                                            <div className="space-y-2">
                                                <Label htmlFor="graduate_organization" className="flex items-center gap-2">
                                                    <Building2 className="h-4 w-4 text-gray-500" />
                                                    {t('publicRegistration.education.organization')} *
                                                </Label>
                                                <Input
                                                    id="graduate_organization"
                                                    value={data.graduate_organization}
                                                    onChange={(e) => setData('graduate_organization', e.target.value)}
                                                    placeholder={t('publicRegistration.education.organizationPlaceholder')}
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
                                                        {t('publicRegistration.education.graduateYear')} *
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
                                                        {t('publicRegistration.education.speciality')} *
                                                    </Label>
                                                    <Input
                                                        id="speciality"
                                                        value={data.speciality}
                                                        onChange={(e) => setData('speciality', e.target.value)}
                                                        placeholder={t('publicRegistration.education.specialityPlaceholder')}
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
                                                onClick={() => setCurrentStep(verificationStep)}
                                                variant="outline"
                                                className="flex-1"
                                                size="lg"
                                            >
                                            {t('publicRegistration.actions.back')}
                                            </Button>
                                            {loadedFromExistingApplicant ? (
                                                <Button
                                                    type="submit"
                                                    disabled={
                                                        !canProceedToDocuments ||
                                                        processing ||
                                                        compressingField !== null
                                                    }
                                                    className="flex-1"
                                                    size="lg"
                                                >
                                                    {processing ? t('publicRegistration.actions.sending') : t('publicRegistration.actions.submit')}
                                                </Button>
                                            ) : (
                                                <Button
                                                    type="button"
                                                    onClick={() => setCurrentStep('documents')}
                                                    disabled={!canProceedToDocuments}
                                                    className="flex-1"
                                                    size="lg"
                                                >
                                                    {t('publicRegistration.actions.continue')}
                                                </Button>
                                            )}
                                        </div>
                                    </div>
                                )}

                                {/* Step 5: Documents (only for new applicants) */}
                                {!loadedFromExistingApplicant && currentStep === 'documents' && (
                                    <div className="space-y-6">
                                        <div>
                                            <h2 className="text-2xl font-bold text-gray-900 mb-2">
                                                {t('publicRegistration.documents.title')}
                                            </h2>
                                            <p className="text-gray-600">
                                                {t('publicRegistration.documents.description')}
                                            </p>
                                        </div>

                                        {errors.documents && (
                                            <p className="text-sm text-red-600">{errors.documents}</p>
                                        )}

                                        <div className="grid gap-4 sm:grid-cols-2">
                                            <div className="space-y-2">
                                                <Label htmlFor="document_front">{t('publicRegistration.documents.documentFront')} *</Label>
                                                <Input
                                                    id="document_front"
                                                    type="file"
                                                    accept="image/*"
                                                    required
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
                                                    <p className="text-xs text-gray-500">{t('publicRegistration.documents.compressing')}</p>
                                                )}
                                                {errors.document_front && (
                                                    <p className="text-sm text-red-600">{errors.document_front}</p>
                                                )}
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="document_back">{t('publicRegistration.documents.documentBack')} *</Label>
                                                <Input
                                                    id="document_back"
                                                    type="file"
                                                    accept="image/*"
                                                    required
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
                                                    <p className="text-xs text-gray-500">{t('publicRegistration.documents.compressing')}</p>
                                                )}
                                                {errors.document_back && (
                                                    <p className="text-sm text-red-600">{errors.document_back}</p>
                                                )}
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="diplom">{t('publicRegistration.documents.diplom')} *</Label>
                                                <Input
                                                    id="diplom"
                                                    type="file"
                                                    accept="image/*"
                                                    required
                                                    disabled={compressingField === 'diplom'}
                                                    onChange={(e) =>
                                                        void handleDocumentFile('diplom', e.target.files?.[0] || null)
                                                    }
                                                    className="h-12"
                                                />
                                                {compressingField === 'diplom' && (
                                                    <p className="text-xs text-gray-500">{t('publicRegistration.documents.compressing')}</p>
                                                )}
                                                {errors.diplom && (
                                                    <p className="text-sm text-red-600">{errors.diplom}</p>
                                                )}
                                            </div>

                                            <div className="space-y-2">
                                                <Label htmlFor="certificate">{t('publicRegistration.documents.certificate')} *</Label>
                                                <Input
                                                    id="certificate"
                                                    type="file"
                                                    accept="image/*"
                                                    required
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
                                                    <p className="text-xs text-gray-500">{t('publicRegistration.documents.compressing')}</p>
                                                )}
                                                {errors.certificate && (
                                                    <p className="text-sm text-red-600">{errors.certificate}</p>
                                                )}
                                            </div>
                                        </div>

                                        <div className="rounded-lg border border-blue-200 bg-blue-50 p-4">
                                            <p className="text-sm text-blue-800">
                                                {t('publicRegistration.documents.notice', {
                                                    channel: requiresTelegram
                                                        ? t('publicRegistration.documents.viaTelegram')
                                                        : t('publicRegistration.documents.viaEmail'),
                                                })}
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
                                            {t('publicRegistration.actions.back')}
                                            </Button>
                                            <Button
                                                type="submit"
                                                disabled={!canSubmitDocuments || processing || compressingField !== null}
                                                className="flex-1"
                                                size="lg"
                                            >
                                                {processing ? t('publicRegistration.actions.sending') : t('publicRegistration.actions.submit')}
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
                        <DialogTitle>{t('publicRegistration.existingAccount.title')}</DialogTitle>
                        <DialogDescription>
                            {existingAccountMessage ||
                                t('publicRegistration.existingAccount.message', {
                                    channel: requiresTelegram
                                        ? t('publicRegistration.existingAccount.channelTelegram')
                                        : t('publicRegistration.existingAccount.channelEmail'),
                                })}
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter className="gap-2 sm:gap-0">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => setExistingAccountModalOpen(false)}
                            disabled={resumeLoading}
                        >
                            {t('publicRegistration.actions.cancel')}
                        </Button>
                        <Button
                            type="button"
                            onClick={handleResumeFromModal}
                            disabled={resumeLoading || !data.identifier}
                        >
                            {resumeLoading ? (
                                <>
                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                    {t('publicRegistration.actions.loading')}
                                </>
                            ) : (
                                t('publicRegistration.actions.goToVerification')
                            )}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}
