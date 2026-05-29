<?php

use App\Http\Controllers\Admin\ApplicantController;
use App\Http\Controllers\Admin\ExamAttemptController as AdminExamAttemptController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\ExamRegistrationController;
use App\Http\Controllers\Admin\ExamTypeController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Public\ExamAttemptController;
use App\Http\Controllers\Public\ExamResultReportController;
use App\Http\Controllers\Public\PublicMediaController;
use App\Http\Controllers\Public\RegistrationController;
use App\Http\Controllers\Public\RegistrationTelegramController;
use App\Http\Controllers\TelegramWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public routes
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('login');
})->name('home');

// Language switcher
Route::post('/locale', function (Request $request) {
    $locale = $request->input('locale');
    if (in_array($locale, ['ru', 'kk', 'en'])) {
        $request->session()->put('locale', $locale);
    }

    return back();
})->name('locale.set');

// Public registration routes
Route::get('/register/{slug}', [RegistrationController::class, 'index'])->name('public.registration.index');
Route::post('/register/{slug}', [RegistrationController::class, 'store'])->name('public.registration.store');
Route::post('/register/{slug}/telegram/init', [RegistrationTelegramController::class, 'init'])->name('public.registration.telegram.init');
Route::post('/register/{slug}/telegram/resume', [RegistrationTelegramController::class, 'resume'])->name('public.registration.telegram.resume');
Route::get('/register/{slug}/telegram/status', [RegistrationTelegramController::class, 'status'])->name('public.registration.telegram.status');
Route::post('/register/{slug}/telegram/verify', [RegistrationTelegramController::class, 'verify'])->name('public.registration.telegram.verify');
Route::post('/register/{slug}/telegram/resend', [RegistrationTelegramController::class, 'resend'])->name('public.registration.telegram.resend');
Route::post('/register/{slug}/telegram/reset', [RegistrationTelegramController::class, 'reset'])->name('public.registration.telegram.reset');

// Exam images (not /media/*.png — nginx may treat that as a static file and return 404)
Route::get('/exam-media/{filename}', [PublicMediaController::class, 'show'])
    ->where('filename', '[a-zA-Z0-9._-]+')
    ->name('public.exam-media.show');

// Public exam routes (token-based access)
Route::get('/exam/{token}', [ExamAttemptController::class, 'show'])->name('public.exam.show');
Route::post('/exam/{token}/start', [ExamAttemptController::class, 'start'])->name('public.exam.start');
Route::get('/exam/{token}/take', [ExamAttemptController::class, 'take'])->name('public.exam.take');
Route::post('/exam/{token}/answers', [ExamAttemptController::class, 'saveAnswer'])->name('public.exam.answers');
Route::post('/exam/{token}/finish', [ExamAttemptController::class, 'finish'])->name('public.exam.finish');
Route::get('/exam/{token}/complete', [ExamAttemptController::class, 'complete'])->name('public.exam.complete');
Route::get('/exam/{token}/report.pdf', [ExamResultReportController::class, 'show'])->name('public.exam.report');

// Telegram webhook
Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle'])->name('telegram.webhook');

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
});

Route::post('logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Admin routes (authenticated users only)
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Admin/Dashboard', [
            'auth' => [
                'user' => auth()->user()->load('roles'),
            ],
        ]);
    })->name('dashboard');

    // User management (only for developer role)
    Route::middleware(['role:developer'])->group(function () {
        Route::resource('users', UserController::class);
    });

    // Exam management (developer and ktbo)
    Route::middleware(['role_or_permission:developer|ktbo'])->group(function () {
        Route::resource('exam-types', ExamTypeController::class);
        Route::get('exam-types/{examType}/applicants', [ExamTypeController::class, 'applicants'])->name('exam-types.applicants');
        Route::resource('exams', ExamController::class);
        Route::get('exams/{exam}/applicants', [ExamController::class, 'applicants'])->name('exams.applicants');
        Route::get('exams/{exam}/questions', [QuestionController::class, 'index'])->name('exams.questions.index');
        Route::get('exams/{exam}/questions/create', [QuestionController::class, 'create'])->name('exams.questions.create');
        Route::post('exams/{exam}/questions', [QuestionController::class, 'store'])->name('exams.questions.store');
        Route::get('questions/{question}/edit', [QuestionController::class, 'edit'])->name('questions.edit');
        Route::put('questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
        Route::delete('questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');
    });

    // Applicant management (developer, ktbo, and registrator)
    Route::middleware(['role_or_permission:developer|ktbo|registrator'])->group(function () {
        Route::resource('applicants', ApplicantController::class);
        Route::post('exam-registrations/{examRegistration}/approve', [ExamRegistrationController::class, 'approve'])->name('exam-registrations.approve');
        Route::post('exam-registrations/{examRegistration}/unapprove', [ExamRegistrationController::class, 'unapprove'])->name('exam-registrations.unapprove');
        Route::delete('exam-attempts/{examAttempt}', [AdminExamAttemptController::class, 'destroy'])->name('exam-attempts.destroy');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
