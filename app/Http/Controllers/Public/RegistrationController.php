<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\Exam;
use App\Models\ExamRegistration;
use App\Models\ExamType;
use App\Services\ImageOptimizationService;
use App\Services\RegistrationEmailService;
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

    public function store(
        Request $request,
        $slug,
        RegistrationTelegramService $registrationTelegram,
        RegistrationEmailService $registrationEmail
    ) {
        ExamType::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'identifier' => 'required|string|size:12',
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

        $exam = Exam::findOrFail($validated['exam_id']);

        if ($exam->require_telegram_verification) {
            return $this->storeWithTelegram($request, $slug, $registrationTelegram, $validated, $exam);
        }

        return $this->storeWithEmail($request, $slug, $registrationEmail, $validated, $exam);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function storeWithTelegram(
        Request $request,
        string $slug,
        RegistrationTelegramService $registrationTelegram,
        array $validated,
        Exam $exam
    ) {
        if (! $registrationTelegram->isSessionVerified($request)) {
            return back()->withErrors([
                'telegram' => __('registration.verify_telegram_before_submit'),
            ]);
        }

        $telegramDraft = $registrationTelegram->getVerifiedDraft($request);
        if (! $telegramDraft || ($telegramDraft['slug'] ?? '') !== $slug) {
            return back()->withErrors([
                'telegram' => __('registration.telegram_session_expired'),
            ]);
        }

        $existingApplicantId = $telegramDraft['applicant_id'] ?? null;

        $validated = $this->validateApplicantUniqueness($request, $validated, $existingApplicantId);

        if (! $registrationTelegram->personalMatches($telegramDraft['personal'] ?? [], $validated)) {
            $registrationTelegram->invalidateVerification($request);

            return back()->withErrors([
                'telegram' => __('registration.personal_changed_telegram'),
            ]);
        }

        if ((string) $validated['exam_id'] !== (string) ($telegramDraft['exam_id'] ?? '')) {
            return back()->withErrors([
                'telegram' => __('registration.exam_mismatch_telegram'),
            ]);
        }

        $normalizedPersonal = $registrationTelegram->normalizePersonal($validated);
        $validated['name'] = $normalizedPersonal['name'];
        $validated['email'] = $normalizedPersonal['email'];
        $validated['identifier'] = $normalizedPersonal['identifier'];
        $validated['address'] = $normalizedPersonal['address'];
        $validated['phone'] = $normalizedPersonal['phone'];
        $validated['language'] = $exam->language;
        $validated['telegram_token'] = $request->session()->get(RegistrationTelegramService::SESSION_TOKEN_KEY);
        $validated['telegram_chat_id'] = $telegramDraft['chat_id'] ?? null;

        $applicant = $this->persistApplicant($request, $validated, $existingApplicantId);

        ExamRegistration::create([
            'applicant_id' => $applicant->id,
            'exam_id' => $validated['exam_id'],
            'date' => now()->toDateString(),
        ]);

        $registrationTelegram->clearSession($request);

        return Inertia::render('Public/Registration/Success', [
            'applicant' => $applicant,
            'deliveryMethod' => 'telegram',
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function storeWithEmail(
        Request $request,
        string $slug,
        RegistrationEmailService $registrationEmail,
        array $validated,
        Exam $exam
    ) {
        if (! $registrationEmail->isSessionVerified($request)) {
            return back()->withErrors([
                'email' => __('registration.verify_email_before_submit'),
            ]);
        }

        $emailDraft = $registrationEmail->getVerifiedDraft($request);
        if (! $emailDraft || ($emailDraft['slug'] ?? '') !== $slug) {
            return back()->withErrors([
                'email' => __('registration.email_session_expired'),
            ]);
        }

        $existingApplicantId = $emailDraft['applicant_id'] ?? null;

        $validated = $this->validateApplicantUniqueness($request, $validated, $existingApplicantId);

        if (! $registrationEmail->personalMatches($emailDraft['personal'] ?? [], $validated)) {
            $registrationEmail->invalidateVerification($request);

            return back()->withErrors([
                'email' => __('registration.personal_changed_email'),
            ]);
        }

        if ((string) $validated['exam_id'] !== (string) ($emailDraft['exam_id'] ?? '')) {
            return back()->withErrors([
                'email' => __('registration.exam_mismatch_email'),
            ]);
        }

        $normalizedPersonal = $registrationEmail->normalizePersonal($validated);
        $validated['name'] = $normalizedPersonal['name'];
        $validated['email'] = $normalizedPersonal['email'];
        $validated['identifier'] = $normalizedPersonal['identifier'];
        $validated['address'] = $normalizedPersonal['address'];
        $validated['phone'] = $normalizedPersonal['phone'];
        $validated['language'] = $exam->language;
        unset($validated['telegram_token'], $validated['telegram_chat_id']);

        $applicant = $this->persistApplicant($request, $validated, $existingApplicantId);

        ExamRegistration::create([
            'applicant_id' => $applicant->id,
            'exam_id' => $validated['exam_id'],
            'date' => now()->toDateString(),
        ]);

        $registrationEmail->clearSession($request);

        return Inertia::render('Public/Registration/Success', [
            'applicant' => $applicant,
            'deliveryMethod' => 'email',
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function validateApplicantUniqueness(Request $request, array $validated, ?int $existingApplicantId): array
    {
        return $request->validate([
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
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function persistApplicant(Request $request, array $validated, ?int $existingApplicantId): Applicant
    {
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

        return $applicant;
    }
}
