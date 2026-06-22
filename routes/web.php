<?php

use App\Http\Controllers\Admin\ApplicantController;
use App\Http\Controllers\Admin\ExamAttemptController as AdminExamAttemptController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\ExamRegistrationController;
use App\Http\Controllers\Admin\ExamTypeController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Public\ExamAttemptController;
use App\Http\Controllers\Public\ExamResultReportController;
use App\Http\Controllers\Public\RegistrationController;
use App\Http\Controllers\Public\RegistrationEmailController;
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
Route::post('/register/{slug}/email/init', [RegistrationEmailController::class, 'init'])->name('public.registration.email.init');
Route::post('/register/{slug}/email/resume', [RegistrationEmailController::class, 'resume'])->name('public.registration.email.resume');
Route::post('/register/{slug}/email/verify', [RegistrationEmailController::class, 'verify'])->name('public.registration.email.verify');
Route::post('/register/{slug}/email/resend', [RegistrationEmailController::class, 'resend'])->name('public.registration.email.resend');
Route::post('/register/{slug}/email/reset', [RegistrationEmailController::class, 'reset'])->name('public.registration.email.reset');

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
        return Inertia::render('Admin/Dashboard');
    })->name('dashboard');

    // User management
    Route::middleware(['permission:users.view'])->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
    });
    Route::middleware(['permission:users.create'])->group(function () {
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
    });
    Route::middleware(['permission:users.edit'])->group(function () {
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::match(['put', 'patch'], 'users/{user}', [UserController::class, 'update'])->name('users.update');
    });
    Route::middleware(['permission:users.delete'])->group(function () {
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // Role management
    Route::middleware(['permission:roles.view'])->group(function () {
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
    });
    Route::middleware(['permission:roles.create'])->group(function () {
        Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
    });
    Route::middleware(['permission:roles.edit'])->group(function () {
        Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::match(['put', 'patch'], 'roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    });
    Route::middleware(['permission:roles.delete'])->group(function () {
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });

    // Permission management
    Route::middleware(['permission:permissions.view'])->group(function () {
        Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
    });
    Route::middleware(['permission:permissions.create'])->group(function () {
        Route::get('permissions/create', [PermissionController::class, 'create'])->name('permissions.create');
        Route::post('permissions', [PermissionController::class, 'store'])->name('permissions.store');
    });
    Route::middleware(['permission:permissions.delete'])->group(function () {
        Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');
    });

    // Exam type create/destroy
    Route::middleware(['permission:exam-types.create'])->group(function () {
        Route::get('exam-types/create', [ExamTypeController::class, 'create'])->name('exam-types.create');
        Route::post('exam-types', [ExamTypeController::class, 'store'])->name('exam-types.store');
    });
    Route::middleware(['permission:exam-types.delete'])->group(function () {
        Route::delete('exam-types/{examType}', [ExamTypeController::class, 'destroy'])->name('exam-types.destroy');
    });

    // Exam type read + registration review/approve
    Route::middleware(['permission:exam-types.view'])->group(function () {
        Route::get('exam-types', [ExamTypeController::class, 'index'])->name('exam-types.index');
        Route::get('exam-types/{examType}', [ExamTypeController::class, 'show'])->name('exam-types.show');
        Route::get('exam-types/{examType}/applicants', [ExamTypeController::class, 'applicants'])->name('exam-types.applicants');
    });
    Route::middleware(['permission:questions.view'])->group(function () {
        Route::get('exams/{exam}/questions', [QuestionController::class, 'index'])->name('exams.questions.index');
    });
    Route::middleware(['permission:exam-registrations.view'])->group(function () {
        Route::get('exam-registrations/{examRegistration}/review', [ExamRegistrationController::class, 'review'])->name('exam-registrations.review');
    });
    Route::middleware(['permission:exam-registrations.approve'])->group(function () {
        Route::post('exam-registrations/{examRegistration}/approve', [ExamRegistrationController::class, 'approve'])->name('exam-registrations.approve');
        Route::post('exam-registrations/bulk-approve', [ExamRegistrationController::class, 'bulkApprove'])->name('exam-registrations.bulk-approve');
    });
    Route::middleware(['permission:exam-registrations.edit-date'])->group(function () {
        Route::patch('exam-registrations/{examRegistration}/date', [ExamRegistrationController::class, 'updateDate'])->name('exam-registrations.update-date');
        Route::post('exam-registrations/bulk-update-date', [ExamRegistrationController::class, 'bulkUpdateDate'])->name('exam-registrations.bulk-update-date');
    });

    // Exam type edit + exam/question management
    Route::middleware(['permission:exam-types.edit'])->group(function () {
        Route::get('exam-types/{examType}/edit', [ExamTypeController::class, 'edit'])->name('exam-types.edit');
        Route::match(['put', 'patch'], 'exam-types/{examType}', [ExamTypeController::class, 'update'])->name('exam-types.update');
    });
    Route::middleware(['permission:exams.view'])->group(function () {
        Route::get('exams', [ExamController::class, 'index'])->name('exams.index');
        Route::get('exams/{exam}', [ExamController::class, 'show'])->name('exams.show');
        Route::get('exams/{exam}/applicants', [ExamController::class, 'applicants'])->name('exams.applicants');
    });
    Route::middleware(['permission:exams.create'])->group(function () {
        Route::get('exams/create', [ExamController::class, 'create'])->name('exams.create');
        Route::post('exams', [ExamController::class, 'store'])->name('exams.store');
    });
    Route::middleware(['permission:exams.edit'])->group(function () {
        Route::get('exams/{exam}/edit', [ExamController::class, 'edit'])->name('exams.edit');
        Route::match(['put', 'patch'], 'exams/{exam}', [ExamController::class, 'update'])->name('exams.update');
    });
    Route::middleware(['permission:exams.delete'])->group(function () {
        Route::delete('exams/{exam}', [ExamController::class, 'destroy'])->name('exams.destroy');
    });
    Route::middleware(['permission:questions.create'])->group(function () {
        Route::get('exams/{exam}/questions/create', [QuestionController::class, 'create'])->name('exams.questions.create');
        Route::post('exams/{exam}/questions', [QuestionController::class, 'store'])->name('exams.questions.store');
    });
    Route::middleware(['permission:questions.edit'])->group(function () {
        Route::get('questions/{question}/edit', [QuestionController::class, 'edit'])->name('questions.edit');
        Route::put('questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
    });
    Route::middleware(['permission:questions.delete'])->group(function () {
        Route::delete('questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');
    });
    Route::middleware(['permission:exam-registrations.unapprove'])->group(function () {
        Route::post('exam-registrations/{examRegistration}/unapprove', [ExamRegistrationController::class, 'unapprove'])->name('exam-registrations.unapprove');
    });
    Route::middleware(['permission:exam-attempts.delete'])->group(function () {
        Route::delete('exam-attempts/{examAttempt}', [AdminExamAttemptController::class, 'destroy'])->name('exam-attempts.destroy');
    });

    // Applicant management
    Route::middleware(['permission:applicants.view'])->group(function () {
        Route::get('applicants', [ApplicantController::class, 'index'])->name('applicants.index');
        Route::get('applicants/{applicant}', [ApplicantController::class, 'show'])->name('applicants.show');
    });
    Route::middleware(['permission:applicants.create'])->group(function () {
        Route::get('applicants/create', [ApplicantController::class, 'create'])->name('applicants.create');
        Route::post('applicants', [ApplicantController::class, 'store'])->name('applicants.store');
    });
    Route::middleware(['permission:applicants.edit'])->group(function () {
        Route::get('applicants/{applicant}/edit', [ApplicantController::class, 'edit'])->name('applicants.edit');
        Route::match(['put', 'patch'], 'applicants/{applicant}', [ApplicantController::class, 'update'])->name('applicants.update');
    });
    Route::middleware(['permission:applicants.delete'])->group(function () {
        Route::delete('applicants/{applicant}', [ApplicantController::class, 'destroy'])->name('applicants.destroy');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
