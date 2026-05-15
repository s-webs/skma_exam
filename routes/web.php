<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ExamTypeController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\ApplicantController;
use App\Http\Controllers\Public\RegistrationController;
use App\Http\Controllers\TelegramWebhookController;
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
Route::post('/locale', function (Illuminate\Http\Request $request) {
    $locale = $request->input('locale');
    if (in_array($locale, ['ru', 'kk', 'en'])) {
        $request->session()->put('locale', $locale);
    }
    return back();
})->name('locale.set');

// Public registration routes
Route::get('/register/{slug}', [RegistrationController::class, 'index'])->name('public.registration.index');
Route::post('/register/{slug}', [RegistrationController::class, 'store'])->name('public.registration.store');

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
                'user' => auth()->user()->load('roles')
            ]
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
        Route::post('applicants/{applicant}/approve', [ApplicantController::class, 'approve'])->name('applicants.approve');
        Route::post('applicants/{applicant}/unapprove', [ApplicantController::class, 'unapprove'])->name('applicants.unapprove');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
