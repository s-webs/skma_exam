<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\Exam;
use App\Models\ExamRegistration;
use App\Models\ExamType;
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

        // Handle file uploads
        if ($request->hasFile('document_front')) {
            $path = $request->file('document_front')->store('applicants/documents', 'public');
            $applicant->update(['document_front' => $path]);
        }

        if ($request->hasFile('document_back')) {
            $path = $request->file('document_back')->store('applicants/documents', 'public');
            $applicant->update(['document_back' => $path]);
        }

        if ($request->hasFile('diplom')) {
            $path = $request->file('diplom')->store('applicants/diploms', 'public');
            $applicant->update(['diplom' => $path]);
        }

        if ($request->hasFile('certificate')) {
            $path = $request->file('certificate')->store('applicants/certificates', 'public');
            $applicant->update(['certificate' => $path]);
        }

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('applicants/photos', 'public');
            $applicant->update(['photo' => $path]);
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
