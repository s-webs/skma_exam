<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\ExamType;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Answer;

class PsihotestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Подключение к старой базе MySQL
        $mysqlConnection = DB::connection('mysql_old');

        // Создаем или находим тип экзамена
        $examType = ExamType::firstOrCreate(
            ['slug' => 'psixotest-50-minut'],
            [
                'name' => 'Психотест (50 минут)',
                'description' => 'Психологическое тестирование для поступающих',
                'is_active' => true,
            ]
        );

        $this->command->info('Тип экзамена: ' . $examType->name);

        // Создаем или находим экзамен на русском языке
        $examRu = Exam::firstOrCreate(
            [
                'exam_type_id' => $examType->id,
                'language' => 'ru',
            ],
            [
                'name' => 'Русский',
                'description' => 'Психотест на русском языке',
                'duration_minutes' => 50,
                'questions_count' => 30,
                'passing_score' => 23,
                'max_attempts' => 1,
                'is_active' => true,
                'created_by_user_id' => 1,
            ]
        );

        $this->command->info('Экзамен: ' . $examRu->name);

        // Импортируем русские вопросы (только если их еще нет)
        if ($examRu->questions()->count() === 0) {
            $this->importQuestions($mysqlConnection, $examRu, 'questions', 'answers', 'question_id');
        } else {
            $this->command->warn('Вопросы для экзамена "Русский" уже существуют, пропускаем импорт.');
        }

        // Создаем или находим экзамен на казахском языке
        $examKz = Exam::firstOrCreate(
            [
                'exam_type_id' => $examType->id,
                'language' => 'kz',
            ],
            [
                'name' => 'Қазақша',
                'description' => 'Психотест на казахском языке',
                'duration_minutes' => 50,
                'questions_count' => 30,
                'passing_score' => 23,
                'max_attempts' => 1,
                'is_active' => true,
                'created_by_user_id' => 1,
            ]
        );

        $this->command->info('Экзамен: ' . $examKz->name);

        // Импортируем казахские вопросы (только если их еще нет)
        if ($examKz->questions()->count() === 0) {
            $this->importQuestions($mysqlConnection, $examKz, 'kaz_questions', 'kaz_answers', 'kaz_question_id');
        } else {
            $this->command->warn('Вопросы для экзамена "Қазақша" уже существуют, пропускаем импорт.');
        }

        $this->command->info('Импорт завершен!');
    }

    /**
     * Импорт вопросов и ответов из старой базы
     */
    private function importQuestions($connection, $exam, $questionsTable, $answersTable, $foreignKeyColumn): void
    {
        $questions = $connection->table($questionsTable)->get();

        $this->command->info("Импорт {$questions->count()} вопросов из таблицы {$questionsTable}...");

        $progressBar = $this->command->getOutput()->createProgressBar($questions->count());
        $progressBar->start();

        foreach ($questions as $oldQuestion) {
            // Создаем вопрос (адаптируем старую структуру к новой)
            $question = Question::create([
                'exam_id' => $exam->id,
                'content' => $oldQuestion->title ?? '', // title -> content
                'image_path' => $oldQuestion->image ?? null, // image -> image_path
                'explanation' => null, // в старой базе нет explanation
                'is_active' => true,
                'created_by_user_id' => 1, // Импортировано из старой базы
            ]);

            // Получаем ответы для этого вопроса
            $answers = $connection->table($answersTable)
                ->where($foreignKeyColumn, $oldQuestion->id)
                ->get();

            // Создаем ответы (адаптируем старую структуру к новой)
            foreach ($answers as $oldAnswer) {
                Answer::create([
                    'question_id' => $question->id,
                    'content' => $oldAnswer->title ?? '', // title -> content
                    'image_path' => $oldAnswer->image ?? null, // image -> image_path
                    'is_correct' => (bool)($oldAnswer->correct ?? false), // correct -> is_correct
                    'created_by_user_id' => 1, // Импортировано из старой базы
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
    }
}
