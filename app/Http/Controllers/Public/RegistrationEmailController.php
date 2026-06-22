<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\Exam;
use App\Models\ExamType;
use App\Services\RegistrationEmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RegistrationEmailController extends Controller
{
    public function init(
        Request $request,
        string $slug,
        RegistrationEmailService $registrationEmail
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

        $exam = Exam::findOrFail($preliminary['exam_id']);
        if ($exam->require_telegram_verification) {
            return response()->json(['message' => __('registration.telegram_required')], 422);
        }

        $normalizedIdentifier = $registrationEmail->normalizePersonal($preliminary)['identifier'];
        $existingByIdentifier = Applicant::where('identifier', $normalizedIdentifier)->first();

        if ($existingByIdentifier) {
            return response()->json([
                'message' => __('registration.existing_by_identifier_email'),
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
                'message' => collect($errors)->flatten()->first() ?? __('registration.validation_failed'),
                'errors' => $errors,
                'can_resume' => $canResume,
            ], 422);
        }

        $examBelongsToType = $examType->exams()->where('id', $validated['exam_id'])->exists();
        if (! $examBelongsToType) {
            return response()->json(['message' => __('registration.exam_unavailable')], 422);
        }

        $personal = $registrationEmail->normalizePersonal($validated);

        $registrationEmail->clearSession($request);

        $result = $registrationEmail->createDraft($slug, (string) $validated['exam_id'], $personal);
        $request->session()->put(RegistrationEmailService::SESSION_TOKEN_KEY, $result['token']);
        $request->session()->forget(RegistrationEmailService::SESSION_VERIFIED_KEY);

        if (! $result['code_sent']) {
            return response()->json(['message' => __('registration.code_send_failed')], 500);
        }

        return response()->json([
            'token' => $result['token'],
            'email' => $personal['email'],
            'verified' => false,
        ]);
    }

    public function resume(
        Request $request,
        string $slug,
        RegistrationEmailService $registrationEmail
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

        $exam = Exam::findOrFail($validated['exam_id']);
        if ($exam->require_telegram_verification) {
            return response()->json(['message' => __('registration.telegram_required')], 422);
        }

        $examBelongsToType = $examType->exams()->where('id', $validated['exam_id'])->exists();
        if (! $examBelongsToType) {
            return response()->json(['message' => __('registration.exam_unavailable')], 422);
        }

        $personal = $registrationEmail->normalizePersonal($validated);

        $applicant = Applicant::where('identifier', $personal['identifier'])->first();

        if (! $applicant) {
            return response()->json([
                'message' => __('registration.identifier_not_found'),
            ], 404);
        }

        $emailTaken = Applicant::where('email', $personal['email'])
            ->where('id', '!=', $applicant->id)
            ->exists();

        if ($emailTaken) {
            return response()->json([
                'message' => __('registration.email_taken'),
                'errors' => ['email' => [__('registration.email_taken')]],
            ], 422);
        }

        $applicant->update([
            'name' => $personal['name'],
            'email' => $personal['email'],
            'identifier' => $personal['identifier'],
            'address' => $personal['address'],
            'phone' => $personal['phone'],
        ]);

        $registrationEmail->clearSession($request);

        $result = $registrationEmail->createDraftFromApplicant(
            $slug,
            (string) $validated['exam_id'],
            $applicant,
            $personal
        );

        $request->session()->put(RegistrationEmailService::SESSION_TOKEN_KEY, $result['token']);
        $request->session()->forget(RegistrationEmailService::SESSION_VERIFIED_KEY);

        if (! $result['code_sent']) {
            return response()->json(['message' => __('registration.code_send_failed')], 500);
        }

        return response()->json([
            'token' => $result['token'],
            'email' => $personal['email'],
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

    public function reset(Request $request, string $slug, RegistrationEmailService $registrationEmail): JsonResponse
    {
        ExamType::where('slug', $slug)->firstOrFail();
        $registrationEmail->clearSession($request);

        return response()->json(['ok' => true]);
    }

    public function verify(
        Request $request,
        string $slug,
        RegistrationEmailService $registrationEmail
    ): JsonResponse {
        ExamType::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $token = $request->session()->get(RegistrationEmailService::SESSION_TOKEN_KEY);
        if (! $token) {
            return response()->json(['message' => __('registration.session_expired_personal')], 422);
        }

        $draft = $registrationEmail->getDraft($token);
        if (! $draft || ($draft['slug'] ?? '') !== $slug) {
            return response()->json(['message' => __('registration.session_expired')], 422);
        }

        if (! $registrationEmail->verifyCode($token, $validated['code'])) {
            return response()->json(['message' => __('registration.invalid_code_email')], 422);
        }

        $registrationEmail->markSessionVerified($request, $token);

        return response()->json(['verified' => true]);
    }

    public function resend(
        Request $request,
        string $slug,
        RegistrationEmailService $registrationEmail
    ): JsonResponse {
        ExamType::where('slug', $slug)->firstOrFail();

        $token = $request->session()->get(RegistrationEmailService::SESSION_TOKEN_KEY);
        if (! $token) {
            return response()->json(['message' => __('registration.session_expired')], 422);
        }

        $draft = $registrationEmail->getDraft($token);
        if (! $draft || ($draft['slug'] ?? '') !== $slug) {
            return response()->json(['message' => __('registration.session_expired')], 422);
        }

        if (! $registrationEmail->resendCode($token)) {
            return response()->json(['message' => __('registration.resend_failed')], 500);
        }

        return response()->json(['message' => __('registration.code_resent_email')]);
    }
}
