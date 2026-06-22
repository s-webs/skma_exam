<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Question;
use App\Services\ExamTypeAccessService;
use App\Services\ImageOptimizationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QuestionController extends Controller
{
    public function __construct(
        protected ExamTypeAccessService $examTypeAccess
    ) {}

    private function ensureCanManageQuestions(): void
    {
        if (auth()->user()->hasRole('registrator')) {
            abort(403, 'Регистратор может только просматривать вопросы.');
        }
    }

    public function index(Exam $exam)
    {
        $this->examTypeAccess->ensureCanAccessExam(auth()->user(), $exam);

        $questions = $exam->questions()
            ->with('answers')
            ->withCount('answers')
            ->latest()
            ->get();

        $exam->loadMissing('examType');

        return Inertia::render('Admin/Questions/Index', [
            'exam' => $exam,
            'questions' => $questions,
            'canManageQuestions' => ! auth()->user()->hasRole('registrator'),
            'backUrl' => auth()->user()->hasRole('registrator')
                ? route('admin.exam-types.show', $exam->exam_type_id)
                : route('admin.exams.index'),
        ]);
    }

    public function create(Exam $exam)
    {
        $this->ensureCanManageQuestions();
        $this->examTypeAccess->ensureCanAccessExam(auth()->user(), $exam);
        return Inertia::render('Admin/Questions/Create', [
            'exam' => $exam,
        ]);
    }

    public function store(Request $request, Exam $exam)
    {
        $this->ensureCanManageQuestions();
        $this->examTypeAccess->ensureCanAccessExam(auth()->user(), $exam);

        $validated = $request->validate([
            'content' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'explanation' => 'nullable|string',
            'answers' => 'required|array|min:2|max:6',
            'answers.*.content' => 'nullable|string',
            'answers.*.image' => 'nullable|image|max:2048',
            'answers.*.is_correct' => 'required|boolean',
        ]);

        // Проверяем что у вопроса есть либо текст либо изображение
        if (empty($validated['content']) && ! $request->hasFile('image')) {
            return back()->withErrors(['content' => 'Необходимо заполнить текст вопроса или загрузить изображение']);
        }

        // Проверяем что у каждого ответа есть либо текст либо изображение
        foreach ($validated['answers'] as $index => $answer) {
            if (empty($answer['content']) && ! $request->hasFile("answers.{$index}.image")) {
                return back()->withErrors(["answers.{$index}.content" => 'Ответ '.($index + 1).': необходимо заполнить текст или загрузить изображение']);
            }
        }

        // Проверяем что есть хотя бы один правильный ответ
        $hasCorrectAnswer = collect($validated['answers'])->contains('is_correct', true);
        if (! $hasCorrectAnswer) {
            return back()->withErrors(['answers' => 'Должен быть хотя бы один правильный ответ']);
        }

        $question = $exam->questions()->create([
            'content' => $validated['content'] ?? '',
            'explanation' => $validated['explanation'] ?? null,
            'is_active' => true,
            'created_by_user_id' => auth()->id(),
        ]);

        // Загрузка изображения если есть
        if ($request->hasFile('image')) {
            $imageService = app(ImageOptimizationService::class);
            $filename = $imageService->optimizeAndStore($request->file('image'), 'questions');
            $question->update(['image_path' => $filename]);
        }

        // Создаем ответы
        foreach ($validated['answers'] as $index => $answerData) {
            $createData = [
                'content' => $answerData['content'] ?? '',
                'is_correct' => $answerData['is_correct'],
                'created_by_user_id' => auth()->id(),
            ];

            // Обрабатываем загрузку изображения для ответа
            if ($request->hasFile("answers.{$index}.image")) {
                $imageService = app(ImageOptimizationService::class);
                $filename = $imageService->optimizeAndStore(
                    $request->file("answers.{$index}.image"),
                    'answers'
                );
                $createData['image_path'] = $filename;
            }

            $question->answers()->create($createData);
        }

        return redirect()->route('admin.exams.questions.index', $exam)
            ->with('success', 'Вопрос успешно создан');
    }

    public function edit(Question $question)
    {
        $this->ensureCanManageQuestions();
        $question->load('answers', 'exam');
        $this->examTypeAccess->ensureCanAccessExam(auth()->user(), $question->exam);

        return Inertia::render('Admin/Questions/Edit', [
            'question' => [
                'id' => $question->id,
                'exam_id' => $question->exam_id,
                'content' => $question->content,
                'image_path' => $question->image_path,
                'image_url' => $question->imageUrl(),
                'explanation' => $question->explanation,
                'is_active' => $question->is_active,
                'exam' => $question->exam->only(['id', 'name']),
                'answers' => $question->answers->map(fn ($answer) => [
                    'id' => $answer->id,
                    'content' => $answer->content,
                    'image_path' => $answer->image_path,
                    'image_url' => $answer->imageUrl(),
                    'is_correct' => $answer->is_correct,
                ])->values()->all(),
            ],
        ]);
    }

    public function update(Request $request, Question $question)
    {
        $this->ensureCanManageQuestions();
        $question->loadMissing('exam');
        $this->examTypeAccess->ensureCanAccessExam(auth()->user(), $question->exam);

        $validated = $request->validate([
            'content' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'explanation' => 'nullable|string',
            'is_active' => 'boolean',
            'answers' => 'required|array|min:2|max:6',
            'answers.*.id' => 'nullable|exists:answers,id',
            'answers.*.content' => 'nullable|string',
            'answers.*.image' => 'nullable|image|max:2048',
            'answers.*.is_correct' => 'required|boolean',
        ]);

        // Проверяем что у вопроса есть либо текст либо изображение
        if (empty($validated['content']) && ! $request->hasFile('image') && ! $question->image_path) {
            return back()->withErrors(['content' => 'Необходимо заполнить текст вопроса или загрузить изображение']);
        }

        // Проверяем что у каждого ответа есть либо текст либо изображение
        foreach ($validated['answers'] as $index => $answerData) {
            $existingAnswer = isset($answerData['id']) ? $question->answers()->find($answerData['id']) : null;
            $hasExistingImage = $existingAnswer && $existingAnswer->image_path;

            if (empty($answerData['content']) && ! $request->hasFile("answers.{$index}.image") && ! $hasExistingImage) {
                return back()->withErrors(["answers.{$index}.content" => 'Ответ '.($index + 1).': необходимо заполнить текст или загрузить изображение']);
            }
        }

        $hasCorrectAnswer = collect($validated['answers'])->contains('is_correct', true);
        if (! $hasCorrectAnswer) {
            return back()->withErrors(['answers' => 'Должен быть хотя бы один правильный ответ']);
        }

        $question->update([
            'content' => $validated['content'] ?? '',
            'explanation' => $validated['explanation'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Обрабатываем загрузку изображения вопроса
        if ($request->hasFile('image')) {
            $imageService = app(ImageOptimizationService::class);
            $filename = $imageService->optimizeAndStore($request->file('image'), 'questions');

            // Удаляем старое изображение
            if ($question->image_path) {
                $imageService->deleteOldImage($question->image_path, 'questions');
            }

            $question->update(['image_path' => $filename]);
        }

        // Обновляем ответы
        $existingAnswerIds = [];
        foreach ($validated['answers'] as $index => $answerData) {
            if (isset($answerData['id'])) {
                $answer = $question->answers()->find($answerData['id']);
                if ($answer) {
                    $updateData = [
                        'content' => $answerData['content'],
                        'is_correct' => $answerData['is_correct'],
                    ];

                    // Обрабатываем загрузку изображения для ответа
                    if ($request->hasFile("answers.{$index}.image")) {
                        $imageService = app(ImageOptimizationService::class);
                        $filename = $imageService->optimizeAndStore(
                            $request->file("answers.{$index}.image"),
                            'answers'
                        );

                        // Удаляем старое изображение
                        if ($answer->image_path) {
                            $imageService->deleteOldImage($answer->image_path, 'answers');
                        }

                        $updateData['image_path'] = $filename;
                    }

                    $answer->update($updateData);
                    $existingAnswerIds[] = $answer->id;
                }
            } else {
                $createData = [
                    'content' => $answerData['content'] ?? '',
                    'is_correct' => $answerData['is_correct'],
                    'created_by_user_id' => auth()->id(),
                ];

                // Обрабатываем загрузку изображения для нового ответа
                if ($request->hasFile("answers.{$index}.image")) {
                    $imageService = app(ImageOptimizationService::class);
                    $filename = $imageService->optimizeAndStore(
                        $request->file("answers.{$index}.image"),
                        'answers'
                    );
                    $createData['image_path'] = $filename;
                }

                $newAnswer = $question->answers()->create($createData);
                $existingAnswerIds[] = $newAnswer->id;
            }
        }

        // Удаляем ответы которых нет в новом списке
        $question->answers()->whereNotIn('id', $existingAnswerIds)->delete();

        return redirect()->route('admin.exams.questions.index', $question->exam_id)
            ->with('success', 'Вопрос успешно обновлен');
    }

    public function destroy(Question $question)
    {
        $this->ensureCanManageQuestions();
        $question->loadMissing('exam');
        $this->examTypeAccess->ensureCanAccessExam(auth()->user(), $question->exam);

        $examId = $question->exam_id;
        $question->delete();

        return redirect()->route('admin.exams.questions.index', $examId)
            ->with('success', 'Вопрос успешно удален');
    }
}
