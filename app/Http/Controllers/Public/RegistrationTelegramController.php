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
            $canResume = isset($errors['email']) || isset($errors['identifier']);

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

        $personal = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'identifier' => $validated['identifier'],
            'address' => $validated['address'],
            'phone' => $validated['phone'],
        ];

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
            'email' => 'required|email',
            'identifier' => 'required|string|size:12',
        ]);

        $examBelongsToType = $examType->exams()->where('id', $validated['exam_id'])->exists();
        if (! $examBelongsToType) {
            return response()->json(['message' => 'Выбранный экзамен недоступен.'], 422);
        }

        $byEmail = Applicant::where('email', $validated['email'])->first();
        $byIdentifier = Applicant::where('identifier', $validated['identifier'])->first();

        if ($byEmail && $byIdentifier && $byEmail->id !== $byIdentifier->id) {
            return response()->json([
                'message' => 'Email и ИИН относятся к разным заявкам. Проверьте данные или обратитесь в приёмную комиссию.',
            ], 422);
        }

        $applicant = $byEmail ?? $byIdentifier;

        if (! $applicant) {
            return response()->json([
                'message' => 'Заявка с такими данными не найдена.',
            ], 404);
        }

        if ($applicant->email !== $validated['email'] || $applicant->identifier !== $validated['identifier']) {
            return response()->json([
                'message' => 'Укажите email и ИИН, совпадающие с вашей существующей заявкой.',
            ], 422);
        }

        $registrationTelegram->clearSession($request);

        $result = $registrationTelegram->createDraftFromApplicant(
            $slug,
            (string) $validated['exam_id'],
            $applicant
        );

        $request->session()->put(RegistrationTelegramService::SESSION_TOKEN_KEY, $result['token']);
        $request->session()->forget(RegistrationTelegramService::SESSION_VERIFIED_KEY);

        return response()->json([
            'token' => $result['token'],
            'bot_username' => config('services.telegram.bot_username'),
            'bot_url' => $telegram->buildBotUrl(),
            'linked' => false,
            'verified' => false,
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
