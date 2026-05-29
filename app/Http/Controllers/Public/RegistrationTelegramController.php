<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\ExamType;
use App\Services\RegistrationTelegramService;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RegistrationTelegramController extends Controller
{
    public function init(
        Request $request,
        string $slug,
        RegistrationTelegramService $registrationTelegram
    ): JsonResponse {
        $examType = ExamType::where('slug', $slug)->firstOrFail();

        $preliminary = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'identifier' => 'required|string|size:12',
            'address' => 'required|string',
            'phone' => 'required|string',
        ]);

        $normalizedIdentifier = $registrationTelegram->normalizePersonal($preliminary)['identifier'];
        $existingByIdentifier = Applicant::where('identifier', $normalizedIdentifier)->first();

        if ($existingByIdentifier) {
            return response()->json([
                'message' => 'Заявка с таким ИИН уже зарегистрирована. Продолжите подтверждение через Telegram.',
                'can_resume' => true,
                'existing_by' => 'identifier',
            ], 422);
        }

        try {
            $validated = $request->validate([
                'exam_id' => 'required|exists:exams,id',
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:applicants,email',
                'identifier' => 'required|string|size:12|unique:applicants,identifier',
                'address' => 'required|string',
                'phone' => 'required|string',
            ]);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $canResume = isset($errors['identifier']);

            return response()->json([
                'message' => collect($errors)->flatten()->first() ?? 'Ошибка проверки данных.',
                'errors' => $errors,
                'can_resume' => $canResume,
            ], 422);
        }

        $examBelongsToType = $examType->exams()->where('id', $validated['exam_id'])->exists();
        if (! $examBelongsToType) {
            return response()->json(['message' => 'Выбранный экзамен недоступен.'], 422);
        }

        $personal = $registrationTelegram->normalizePersonal([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'identifier' => $validated['identifier'],
            'address' => $validated['address'],
            'phone' => $validated['phone'],
        ]);

        $registrationTelegram->clearSession($request);

        $result = $registrationTelegram->createDraft($slug, (string) $validated['exam_id'], $personal);
        $request->session()->put(RegistrationTelegramService::SESSION_TOKEN_KEY, $result['token']);
        $request->session()->forget(RegistrationTelegramService::SESSION_VERIFIED_KEY);

        return response()->json([
            'token' => $result['token'],
            'bot_username' => config('services.telegram.bot_username'),
            'bot_url' => app(TelegramService::class)->buildBotUrl(),
            'linked' => false,
            'verified' => false,
        ]);
    }

    public function resume(
        Request $request,
        string $slug,
        RegistrationTelegramService $registrationTelegram,
        TelegramService $telegram
    ): JsonResponse {
        $examType = ExamType::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'identifier' => 'required|string|size:12',
            'address' => 'required|string',
            'phone' => 'required|string',
        ]);

        $examBelongsToType = $examType->exams()->where('id', $validated['exam_id'])->exists();
        if (! $examBelongsToType) {
            return response()->json(['message' => 'Выбранный экзамен недоступен.'], 422);
        }

        $personal = $registrationTelegram->normalizePersonal($validated);

        $applicant = Applicant::where('identifier', $personal['identifier'])->first();

        if (! $applicant) {
            return response()->json([
                'message' => 'Заявка с таким ИИН не найдена.',
            ], 404);
        }

        $emailTaken = Applicant::where('email', $personal['email'])
            ->where('id', '!=', $applicant->id)
            ->exists();

        if ($emailTaken) {
            return response()->json([
                'message' => 'Этот email уже используется другой заявкой.',
                'errors' => ['email' => ['Этот email уже используется другой заявкой.']],
            ], 422);
        }

        $applicant->update([
            'name' => $personal['name'],
            'email' => $personal['email'],
            'identifier' => $personal['identifier'],
            'address' => $personal['address'],
            'phone' => $personal['phone'],
        ]);

        $registrationTelegram->clearSession($request);

        $result = $registrationTelegram->createDraftFromApplicant(
            $slug,
            (string) $validated['exam_id'],
            $applicant,
            $personal
        );

        $request->session()->put(RegistrationTelegramService::SESSION_TOKEN_KEY, $result['token']);
        $request->session()->forget(RegistrationTelegramService::SESSION_VERIFIED_KEY);

        return response()->json([
            'token' => $result['token'],
            'bot_username' => config('services.telegram.bot_username'),
            'bot_url' => $telegram->buildBotUrl(),
            'linked' => $result['linked'],
            'verified' => false,
            'resumed_from_existing' => true,
            'applicant' => [
                'name' => $applicant->name,
                'email' => $applicant->email,
                'identifier' => $applicant->identifier,
                'address' => $applicant->address,
                'phone' => $applicant->phone,
                'graduate_organization' => $applicant->graduate_organization,
                'graduate_year' => $applicant->graduate_year,
                'speciality' => $applicant->speciality,
            ],
        ]);
    }

    public function reset(Request $request, string $slug, RegistrationTelegramService $registrationTelegram): JsonResponse
    {
        ExamType::where('slug', $slug)->firstOrFail();
        $registrationTelegram->clearSession($request);

        return response()->json(['ok' => true]);
    }

    public function status(
        Request $request,
        string $slug,
        RegistrationTelegramService $registrationTelegram
    ): JsonResponse {
        ExamType::where('slug', $slug)->firstOrFail();

        $token = $request->session()->get(RegistrationTelegramService::SESSION_TOKEN_KEY);
        if (! $token) {
            return response()->json(['linked' => false, 'verified' => false], 404);
        }

        $draft = $registrationTelegram->getDraft($token);
        if (! $draft || ($draft['slug'] ?? '') !== $slug) {
            return response()->json(['linked' => false, 'verified' => false], 404);
        }

        return response()->json([
            'linked' => ! empty($draft['chat_id']),
            'verified' => ($draft['verified'] ?? false) === true,
        ]);
    }

    public function verify(
        Request $request,
        string $slug,
        RegistrationTelegramService $registrationTelegram
    ): JsonResponse {
        ExamType::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $token = $request->session()->get(RegistrationTelegramService::SESSION_TOKEN_KEY);
        if (! $token) {
            return response()->json(['message' => 'Сессия регистрации истекла. Вернитесь к шагу с личными данными.'], 422);
        }

        $draft = $registrationTelegram->getDraft($token);
        if (! $draft || ($draft['slug'] ?? '') !== $slug) {
            return response()->json(['message' => 'Сессия регистрации истекла.'], 422);
        }

        if (empty($draft['chat_id'])) {
            return response()->json(['message' => 'Сначала откройте бота в Telegram и нажмите «Start».'], 422);
        }

        if (! $registrationTelegram->verifyCode($token, $validated['code'])) {
            return response()->json(['message' => 'Неверный или просроченный код. Запросите новый в боте.'], 422);
        }

        $registrationTelegram->markSessionVerified($request, $token);

        return response()->json(['verified' => true]);
    }

    public function resend(
        Request $request,
        string $slug,
        RegistrationTelegramService $registrationTelegram,
        TelegramService $telegram
    ): JsonResponse {
        ExamType::where('slug', $slug)->firstOrFail();

        $token = $request->session()->get(RegistrationTelegramService::SESSION_TOKEN_KEY);
        if (! $token) {
            return response()->json(['message' => 'Сессия регистрации истекла.'], 422);
        }

        $draft = $registrationTelegram->getDraft($token);
        if (! $draft || ($draft['slug'] ?? '') !== $slug) {
            return response()->json(['message' => 'Сессия регистрации истекла.'], 422);
        }

        if (empty($draft['chat_id'])) {
            return response()->json(['message' => 'Сначала привяжите Telegram через бота.'], 422);
        }

        if (! $registrationTelegram->resendCode($token, $telegram)) {
            return response()->json(['message' => 'Не удалось отправить код. Попробуйте позже.'], 500);
        }

        return response()->json(['message' => 'Новый код отправлен в Telegram.']);
    }
}
