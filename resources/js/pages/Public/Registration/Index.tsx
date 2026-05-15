import { Head, useForm } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent } from '@/components/ui/card';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { GraduationCap, User, Mail, Phone, MapPin, Building2, Calendar, BookOpen, Upload, CheckCircle2, ChevronRight } from 'lucide-react';

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
}

export default function Index({ examType }: RegistrationIndexProps) {
    const [selectedExam, setSelectedExam] = useState<number | null>(null);
    const [currentStep, setCurrentStep] = useState<'exam' | 'personal' | 'education' | 'documents'>('exam');

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
    const canProceedToEducation = data.name && data.email && data.identifier && data.phone && data.address;
    const canProceedToDocuments = data.graduate_organization && data.graduate_year && data.speciality;

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post(route('public.registration.store', examType.slug));
    };

    const steps = [
        { id: 'exam', label: 'Экзамен', icon: GraduationCap },
        { id: 'personal', label: 'Личные данные', icon: User },
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

                                        <div className="space-y-4">
                                            <div className="space-y-2">
                                                <Label htmlFor="name" className="flex items-center gap-2">
                                                    <User className="h-4 w-4 text-gray-500" />
                                                    ФИО *
                                                </Label>
                                                <Input
                                                    id="name"
                                                    value={data.name}
                                                    onChange={(e) => setData('name', e.target.value)}
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
                                                        onChange={(e) => setData('identifier', e.target.value)}
                                                        maxLength={12}
                                                        placeholder="123456789012"
                                                        required
                                                        className="h-12"
                                                    />
                                                    {errors.identifier && (
                                                        <p className="text-sm text-red-600">{errors.identifier}</p>
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
                                                        onChange={(e) => setData('phone', e.target.value)}
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
                                                    onChange={(e) => setData('email', e.target.value)}
                                                    placeholder="example@mail.com"
                                                    required
                                                    className="h-12"
                                                />
                                                {errors.email && (
                                                    <p className="text-sm text-red-600">{errors.email}</p>
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
                                                    onChange={(e) => setData('address', e.target.value)}
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
                                                onClick={() => setCurrentStep('education')}
                                                disabled={!canProceedToEducation}
                                                className="flex-1"
                                                size="lg"
                                            >
                                                Продолжить
                                            </Button>
                                        </div>
                                    </div>
                                )}

                                {/* Step 3: Education */}
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
                                                onClick={() => setCurrentStep('personal')}
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
                                                Загрузите необходимые документы (необязательно)
                                            </p>
                                        </div>

                                        <div className="grid gap-4 sm:grid-cols-2">
                                            <div className="space-y-2">
                                                <Label htmlFor="document_front">Документ (лицевая)</Label>
                                                <Input
                                                    id="document_front"
                                                    type="file"
                                                    accept="image/*"
                                                    onChange={(e) => setData('document_front', e.target.files?.[0] || null)}
                                                    className="h-12"
                                                />
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
                                                    onChange={(e) => setData('document_back', e.target.files?.[0] || null)}
                                                    className="h-12"
                                                />
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
                                                    onChange={(e) => setData('diplom', e.target.files?.[0] || null)}
                                                    className="h-12"
                                                />
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
                                                    onChange={(e) => setData('certificate', e.target.files?.[0] || null)}
                                                    className="h-12"
                                                />
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
                                                    onChange={(e) => setData('photo', e.target.files?.[0] || null)}
                                                    className="h-12"
                                                />
                                                {errors.photo && (
                                                    <p className="text-sm text-red-600">{errors.photo}</p>
                                                )}
                                            </div>
                                        </div>

                                        <div className="rounded-lg border border-blue-200 bg-blue-50 p-4">
                                            <p className="text-sm text-blue-800">
                                                <strong>Обратите внимание:</strong> После отправки заявки ваши данные будут проверены.
                                                Вы получите уведомление на email после одобрения.
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
                                                disabled={processing}
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
        </>
    );
}
