<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Question;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class QuestionController extends Controller
{
    public function index(Exam $exam)
    {
        $questions = $exam->questions()
            ->with('answers')
            ->withCount('answers')
            ->latest()
            ->get();

        return Inertia::render('Admin/Questions/Index', [
            'exam' => $exam,
            'questions' => $questions,
        ]);
    }

    public function create(Exam $exam)
    {
        return Inertia::render('Admin/Questions/Create', [
            'exam' => $exam,
        ]);
    }

    public function store(Request $request, Exam $exam)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'explanation' => 'nullable|string',
            'answers' => 'required|array|min:2|max:6',
            'answers.*.content' => 'required|string',
            'answers.*.is_correct' => 'required|boolean',
        ]);

        // Проверяем что есть хотя бы один правильный ответ
        $hasCorrectAnswer = collect($validated['answers'])->contains('is_correct', true);
        if (!$hasCorrectAnswer) {
            return back()->withErrors(['answers' => 'Должен быть хотя бы один правильный ответ']);
        }

        $question = $exam->questions()->create([
            'content' => $validated['content'],
            'explanation' => $validated['explanation'] ?? null,
            'is_active' => true,
            'created_by_user_id' => auth()->id(),
        ]);

        // Загрузка изображения если есть
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('questions', 'public');
            $question->update(['image_path' => $path]);
        }

        // Создаем ответы
        foreach ($validated['answers'] as $answerData) {
            $question->answers()->create([
                'content' => $answerData['content'],
                'is_correct' => $answerData['is_correct'],
                'created_by_user_id' => auth()->id(),
            ]);
        }

        return redirect()->route('admin.exams.questions.index', $exam)
            ->with('success', 'Вопрос успешно создан');
    }

    public function edit(Question $question)
    {
        $question->load('answers', 'exam');

        return Inertia::render('Admin/Questions/Edit', [
            'question' => $question,
        ]);
    }

    public function update(Request $request, Question $question)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'explanation' => 'nullable|string',
            'is_active' => 'boolean',
            'answers' => 'required|array|min:2|max:6',
            'answers.*.id' => 'nullable|exists:answers,id',
            'answers.*.content' => 'required|string',
            'answers.*.is_correct' => 'required|boolean',
        ]);

        $hasCorrectAnswer = collect($validated['answers'])->contains('is_correct', true);
        if (!$hasCorrectAnswer) {
            return back()->withErrors(['answers' => 'Должен быть хотя бы один правильный ответ']);
        }

        $question->update([
            'content' => $validated['content'],
            'explanation' => $validated['explanation'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('questions', 'public');
            $question->update(['image_path' => $path]);
        }

        // Обновляем ответы
        $existingAnswerIds = [];
        foreach ($validated['answers'] as $answerData) {
            if (isset($answerData['id'])) {
                $answer = $question->answers()->find($answerData['id']);
                if ($answer) {
                    $answer->update([
                        'content' => $answerData['content'],
                        'is_correct' => $answerData['is_correct'],
                    ]);
                    $existingAnswerIds[] = $answer->id;
                }
            } else {
                $newAnswer = $question->answers()->create([
                    'content' => $answerData['content'],
                    'is_correct' => $answerData['is_correct'],
                    'created_by_user_id' => auth()->id(),
                ]);
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
        $examId = $question->exam_id;
        $question->delete();

        return redirect()->route('admin.exams.questions.index', $examId)
            ->with('success', 'Вопрос успешно удален');
    }
}
