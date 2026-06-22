<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\ExamType;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Answer;

class Psiho2Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Подключение к старой базе MySQL
        $mysqlConnection = DB::connection('mysql_psiho2');

        // Создаем тип экзамена
        $examType = ExamType::firstOrCreate(
            ['slug' => 'psixotest-100-minut'],
            [
                'name_ru' => 'Психотест (100 минут)',
                'description' => 'Психологическое тестирование для поступающих',
                'is_active' => true,
            ]
        );

        $this->command->info('Тип экзамена: ' . $examType->name_ru);

        // Создаем экзамен на русском языке
        $examRu = Exam::firstOrCreate(
            [
                'exam_type_id' => $examType->id,
                'language' => 'ru',
            ],
            [
                'name_ru' => 'Русский',
                'description' => 'Психотест на русском языке',
                'duration_minutes' => 100,
                'questions_count' => 60,
                'passing_score' => 45,
                'max_attempts' => 1,
                'is_active' => true,
                'created_by_user_id' => 1,
            ]
        );

        $this->command->info('Экзамен: ' . $examRu->name_ru);

        // Импортируем русские вопросы
        if ($examRu->questions()->count() === 0) {
            $this->importQuestions($mysqlConnection, $examRu, 'questions', 'answers', 'question_id');
        } else {
            $this->command->warn('Вопросы для экзамена "Русский" уже существуют, пропускаем импорт.');
        }

        // Создаем экзамен на казахском языке
        $examKz = Exam::firstOrCreate(
            [
                'exam_type_id' => $examType->id,
                'language' => 'kz',
            ],
            [
                'name_ru' => 'Қазақша',
                'description' => 'Психотест на казахском языке',
                'duration_minutes' => 100,
                'questions_count' => 60,
                'passing_score' => 45,
                'max_attempts' => 1,
                'is_active' => true,
                'created_by_user_id' => 1,
            ]
        );

        $this->command->info('Экзамен: ' . $examKz->name_ru);

        // Импортируем казахские вопросы
        if ($examKz->questions()->count() === 0) {
            $this->importQuestions($mysqlConnection, $examKz, 'kaz_questions', 'kaz_answers', 'kaz_question_id');
        } else {
            $this->command->warn('Вопросы для экзамена "Қазақша" уже существуют, пропускаем импорт.');
        }

        // Создаем экзамен на английском языке
        $examEn = Exam::firstOrCreate(
            [
                'exam_type_id' => $examType->id,
                'language' => 'en',
            ],
            [
                'name_ru' => 'English',
                'description' => 'Психотест на английском языке',
                'duration_minutes' => 100,
                'questions_count' => 60,
                'passing_score' => 45,
                'max_attempts' => 1,
                'is_active' => true,
                'created_by_user_id' => 1,
            ]
        );

        $this->command->info('Экзамен: ' . $examEn->name_ru);

        // Импортируем английские вопросы
        if ($examEn->questions()->count() === 0) {
            $this->importQuestions($mysqlConnection, $examEn, 'eng_questions', 'eng_answers', 'eng_question_id');
        } else {
            $this->command->warn('Вопросы для экзамена "English" уже существуют, пропускаем импорт.');
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
            // Создаем вопрос
            $question = Question::create([
                'exam_id' => $exam->id,
                'content' => $oldQuestion->title ?? '',
                'image_path' => $oldQuestion->image ?? null,
                'explanation' => null,
                'is_active' => true,
                'created_by_user_id' => 1,
            ]);

            // Получаем ответы для этого вопроса
            $answers = $connection->table($answersTable)
                ->where($foreignKeyColumn, $oldQuestion->id)
                ->get();

            // Создаем ответы
            foreach ($answers as $oldAnswer) {
                Answer::create([
                    'question_id' => $question->id,
                    'content' => $oldAnswer->title ?? '',
                    'image_path' => $oldAnswer->image ?? null,
                    'is_correct' => (bool)($oldAnswer->correct ?? false),
                    'created_by_user_id' => 1,
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
    }
}
