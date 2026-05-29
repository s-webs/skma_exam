<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\Exam;
use App\Models\ExamRegistration;
use App\Models\ExamType;
use App\Services\ImageOptimizationService;
use App\Services\RegistrationTelegramService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class RegistrationController extends Controller
{
    public function index($slug)
    {
        $examType = ExamType::where('slug', $slug)
            ->with(['exams' => function ($query) {
                $query->where('is_active', true);
            }])
            ->firstOrFail();

        return Inertia::render('Public/Registration/Index', [
            'examType' => $examType,
            'telegramBotUsername' => config('services.telegram.bot_username'),
        ]);
    }

    public function store(Request $request, $slug, RegistrationTelegramService $registrationTelegram)
    {
        $examType = ExamType::where('slug', $slug)->firstOrFail();

        if (! $registrationTelegram->isSessionVerified($request)) {
            return back()->withErrors([
                'telegram' => 'Подтвердите аккаунт через Telegram перед отправкой заявки.',
            ]);
        }

        $telegramDraft = $registrationTelegram->getVerifiedDraft($request);
        if (! $telegramDraft || ($telegramDraft['slug'] ?? '') !== $slug) {
            return back()->withErrors([
                'telegram' => 'Сессия подтверждения Telegram истекла. Пройдите шаг верификации снова.',
            ]);
        }

        $existingApplicantId = $telegramDraft['applicant_id'] ?? null;

        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('applicants', 'email')->ignore($existingApplicantId),
            ],
            'identifier' => [
                'required',
                'string',
                'size:12',
                Rule::unique('applicants', 'identifier')->ignore($existingApplicantId),
            ],
            'address' => 'required|string',
            'phone' => 'required|string',
            'graduate_organization' => 'required|string',
            'graduate_year' => 'required|string',
            'speciality' => 'required|string',
            'document_front' => 'nullable|image|max:2048',
            'document_back' => 'nullable|image|max:2048',
            'diplom' => 'nullable|image|max:2048',
            'certificate' => 'nullable|image|max:2048',
            'photo' => 'nullable|image|max:2048',
        ]);

        if (! $registrationTelegram->personalMatches($telegramDraft['personal'] ?? [], $validated)) {
            $registrationTelegram->invalidateVerification($request);

            return back()->withErrors([
                'telegram' => 'Личные данные изменились после подтверждения Telegram. Вернитесь на шаг «Личные данные» и пройдите верификацию снова.',
            ]);
        }

        $normalizedPersonal = $registrationTelegram->normalizePersonal($validated);
        $validated['name'] = $normalizedPersonal['name'];
        $validated['email'] = $normalizedPersonal['email'];
        $validated['identifier'] = $normalizedPersonal['identifier'];
        $validated['address'] = $normalizedPersonal['address'];
        $validated['phone'] = $normalizedPersonal['phone'];

        if ((string) $validated['exam_id'] !== (string) ($telegramDraft['exam_id'] ?? '')) {
            return back()->withErrors([
                'telegram' => 'Выбранный экзамен не совпадает с подтверждённой регистрацией.',
            ]);
        }

        $exam = Exam::findOrFail($validated['exam_id']);
        $validated['language'] = $exam->language;
        $validated['telegram_token'] = $request->session()->get(RegistrationTelegramService::SESSION_TOKEN_KEY);
        $validated['telegram_chat_id'] = $telegramDraft['chat_id'] ?? null;

        unset($validated['document_front'], $validated['document_back'], $validated['diplom'], $validated['certificate'], $validated['photo']);

        if ($existingApplicantId) {
            $applicant = Applicant::findOrFail($existingApplicantId);
            $applicant->update($validated);
        } else {
            $applicant = Applicant::create($validated);
        }

        $imageService = app(ImageOptimizationService::class);

        if ($request->hasFile('document_front')) {
            $filename = $imageService->optimizeAndStore($request->file('document_front'), 'applicants/documents');
            $applicant->update(['document_front' => 'applicants/documents/'.$filename]);
        }

        if ($request->hasFile('document_back')) {
            $filename = $imageService->optimizeAndStore($request->file('document_back'), 'applicants/documents');
            $applicant->update(['document_back' => 'applicants/documents/'.$filename]);
        }

        if ($request->hasFile('diplom')) {
            $filename = $imageService->optimizeAndStore($request->file('diplom'), 'applicants/diploms');
            $applicant->update(['diplom' => 'applicants/diploms/'.$filename]);
        }

        if ($request->hasFile('certificate')) {
            $filename = $imageService->optimizeAndStore($request->file('certificate'), 'applicants/certificates');
            $applicant->update(['certificate' => 'applicants/certificates/'.$filename]);
        }

        if ($request->hasFile('photo')) {
            $filename = $imageService->optimizeAndStore($request->file('photo'), 'applicants/photos');
            $applicant->update(['photo' => 'applicants/photos/'.$filename]);
        }

        ExamRegistration::updateOrCreate(
            [
                'applicant_id' => $applicant->id,
                'exam_id' => $validated['exam_id'],
            ],
            []
        );

        $registrationTelegram->clearSession($request);

        return Inertia::render('Public/Registration/Success', [
            'applicant' => $applicant,
        ]);
    }
}
