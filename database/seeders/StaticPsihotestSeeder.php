<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExamType;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Answer;

class StaticPsihotestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем тип экзамена
        $examType = ExamType::create([
            'name' => 'Психотест (50 минут)',
            'slug' => 'psixotest-50-minut',
            'description' => 'Психологическое тестирование для поступающих',
            'is_active' => true,
        ]);

        $this->command->info('Тип экзамена: ' . $examType->name);

        // Создаем экзамен на русском языке
        $examRu = Exam::create([
            'exam_type_id' => $examType->id,
            'name' => 'Русский',
            'description' => 'Психотест на русском языке',
            'language' => 'ru',
            'duration_minutes' => 50,
            'questions_count' => 30,
            'passing_score' => 23,
            'max_attempts' => 1,
            'is_active' => true,
            'created_by_user_id' => 1,
        ]);

        $this->command->info('Экзамен: ' . $examRu->name);

        // Создаем экзамен на казахском языке
        $examKz = Exam::create([
            'exam_type_id' => $examType->id,
            'name' => 'Қазақша',
            'description' => 'Психотест на казахском языке',
            'language' => 'kz',
            'duration_minutes' => 50,
            'questions_count' => 30,
            'passing_score' => 23,
            'max_attempts' => 1,
            'is_active' => true,
            'created_by_user_id' => 1,
        ]);

        $this->command->info('Экзамен: ' . $examKz->name);

        // Импортируем вопросы для русского экзамена
        $this->importQuestions($examRu, $this->getQuestionsRu());

        // Импортируем вопросы для казахского экзамена
        $this->importQuestions($examKz, $this->getQuestionsKz());

        $this->command->info('Статический сидер выполнен успешно!');
    }

    /**
     * Импорт вопросов и ответов
     */
    private function importQuestions(Exam $exam, array $questions): void
    {
        $this->command->info("Импорт {$exam->name}: " . count($questions) . " вопросов...");

        $progressBar = $this->command->getOutput()->createProgressBar(count($questions));
        $progressBar->start();

        foreach ($questions as $questionData) {
            $question = Question::create([
                'exam_id' => $exam->id,
                'content' => $questionData['content'],
                'image_path' => $questionData['image_path'] ?? null,
                'explanation' => null,
                'is_active' => true,
                'created_by_user_id' => 1,
            ]);

            foreach ($questionData['answers'] as $answerData) {
                Answer::create([
                    'question_id' => $question->id,
                    'content' => $answerData['content'],
                    'is_correct' => $answerData['is_correct'],
                    'created_by_user_id' => 1,
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
    }

    /**
     * Вопросы для русского экзамена
     */
    private function getQuestionsRu(): array
    {
        return [
    [
        'content' => 'Одиннадцатый месяц года – это:',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'ноябрь',
                'is_correct' => true,
            ],
            [
                'content' => 'октябрь',
                'is_correct' => false,
            ],
            [
                'content' => 'декабрь',
                'is_correct' => false,
            ],
            [
                'content' => 'май',
                'is_correct' => false,
            ],
            [
                'content' => 'февраль',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => '«Суровый» является противоположным по значению слову:',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'мягкий',
                'is_correct' => true,
            ],
            [
                'content' => 'резкий',
                'is_correct' => false,
            ],
            [
                'content' => 'строгий',
                'is_correct' => false,
            ],
            [
                'content' => 'жесткий',
                'is_correct' => false,
            ],
            [
                'content' => 'неподатливый',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Какое из приведенных ниже слов отлично от других:',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'сомнительный',
                'is_correct' => true,
            ],
            [
                'content' => 'определенный',
                'is_correct' => false,
            ],
            [
                'content' => 'уверенный',
                'is_correct' => false,
            ],
            [
                'content' => 'доверие',
                'is_correct' => false,
            ],
            [
                'content' => 'верный',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Ответьте «Да» или «Нет».
Сокращение «н.э.» означает «нашей эры» (новой эры)?',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'Да',
                'is_correct' => true,
            ],
            [
                'content' => 'Нет',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Какое из следующих слов отлично от других:',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'слушать',
                'is_correct' => true,
            ],
            [
                'content' => 'петь',
                'is_correct' => false,
            ],
            [
                'content' => 'звонить',
                'is_correct' => false,
            ],
            [
                'content' => 'болтать',
                'is_correct' => false,
            ],
            [
                'content' => 'говорить',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Слово «безукоризненный» является противоположным по своему значению слову:',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'непристойный',
                'is_correct' => true,
            ],
            [
                'content' => 'незапятнанный',
                'is_correct' => false,
            ],
            [
                'content' => 'неподкупный',
                'is_correct' => false,
            ],
            [
                'content' => 'невинный',
                'is_correct' => false,
            ],
            [
                'content' => 'классический',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Какое из приведенных ниже слов относится к слову «жевать» как обоняние и нос:',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'зубы',
                'is_correct' => true,
            ],
            [
                'content' => 'сладкий',
                'is_correct' => false,
            ],
            [
                'content' => 'язык',
                'is_correct' => false,
            ],
            [
                'content' => 'запах',
                'is_correct' => false,
            ],
            [
                'content' => 'чистый',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Сколько из приведенных ниже пар слов являются полностью идентичными? 
Sharp M.C. и Sharp M.C.
Filder E.H. и Filder E.N.
Connor M.G. и Conner M.G.  
Woesner O.W. и Woerner O.W.
Soderquist P.E. и Soderquist B.E.',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '1',
                'is_correct' => true,
            ],
            [
                'content' => '2',
                'is_correct' => false,
            ],
            [
                'content' => '3',
                'is_correct' => false,
            ],
            [
                'content' => '4',
                'is_correct' => false,
            ],
            [
                'content' => '5',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => '«Ясный» является противоположным по смыслу слову:',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'тусклый',
                'is_correct' => true,
            ],
            [
                'content' => 'очевидный',
                'is_correct' => false,
            ],
            [
                'content' => 'явный',
                'is_correct' => false,
            ],
            [
                'content' => 'отчетливый',
                'is_correct' => false,
            ],
            [
                'content' => 'недвусмысленный',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Предприниматель купил несколько подержанных автомобилей за 3500 долларов, а продал их за 5500 долларов, заработав при этом 50 долларов за автомобиль. Сколько автомобилей он продал?',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '40',
                'is_correct' => true,
            ],
            [
                'content' => '30',
                'is_correct' => false,
            ],
            [
                'content' => '20',
                'is_correct' => false,
            ],
            [
                'content' => '10',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Слова «Стук» и «Сток» имеют:',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'ни сходное, ни противоположное значение',
                'is_correct' => true,
            ],
            [
                'content' => 'ни сходное, ни противоположное значение',
                'is_correct' => false,
            ],
            [
                'content' => 'сходное значение',
                'is_correct' => false,
            ],
            [
                'content' => 'противоположное значение',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Сколько из этих 6 пар чисел являются полностью одинаковыми?
5296 5296
66986 69686
834426 834426
7354256 7354256
61197172 61197172
83238224 83238234',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '4',
                'is_correct' => true,
            ],
            [
                'content' => '3',
                'is_correct' => false,
            ],
            [
                'content' => '2',
                'is_correct' => false,
            ],
            [
                'content' => '1',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => '«Близкий» является противоположным слову:',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'чужой',
                'is_correct' => true,
            ],
            [
                'content' => 'дружеский',
                'is_correct' => false,
            ],
            [
                'content' => 'приятельский',
                'is_correct' => false,
            ],
            [
                'content' => 'родной',
                'is_correct' => false,
            ],
            [
                'content' => 'иной',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Какое число является наименьшим: 
6  0,7  36  0,31  5?',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '0,31',
                'is_correct' => true,
            ],
            [
                'content' => '6',
                'is_correct' => false,
            ],
            [
                'content' => '5',
                'is_correct' => false,
            ],
            [
                'content' => '36',
                'is_correct' => false,
            ],
            [
                'content' => '0,7',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Расставьте предлагаемые ниже слова в таком порядке, чтобы получилось правильное предложение. В качестве ответа запишите две последние буквы последнего слова.
одни  ухода  они  гостей  после  наконец  остались',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'ни',
                'is_correct' => true,
            ],
            [
                'content' => 'да',
                'is_correct' => false,
            ],
            [
                'content' => 'ле',
                'is_correct' => false,
            ],
            [
                'content' => 'ей',
                'is_correct' => false,
            ],
            [
                'content' => 'сь',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Какой из приведенных ниже пяти рисунков наиболее отличен от других?',
        'image_path' => 'DNgBJKxhaPquestions.jpeg',
        'answers' => [
            [
                'content' => '4',
                'is_correct' => true,
            ],
            [
                'content' => '1',
                'is_correct' => false,
            ],
            [
                'content' => '2',
                'is_correct' => false,
            ],
            [
                'content' => '3',
                'is_correct' => false,
            ],
            [
                'content' => '5',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Два рыбака поймали 36 рыб. Первый поймал в 8 раз больше, чем второй. Сколько поймал второй?',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '4',
                'is_correct' => true,
            ],
            [
                'content' => '2',
                'is_correct' => false,
            ],
            [
                'content' => '6',
                'is_correct' => false,
            ],
            [
                'content' => '8',
                'is_correct' => false,
            ],
            [
                'content' => '10',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => '«Восходить» и «возродить» имеют:',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'ни сходное, ни противоположное значение',
                'is_correct' => true,
            ],
            [
                'content' => 'противоположное значение',
                'is_correct' => false,
            ],
            [
                'content' => 'сходное значение',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Расставьте предлагаемые ниже слова в таком порядке, чтобы получилось утверждение. Если оно правильно, то ответ будет П, если не правильно – Н.
Мхом обороты камень набирает заросший.',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'Н',
                'is_correct' => true,
            ],
            [
                'content' => 'П',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Две из приведенных ниже фраз имеют одинаковый смысл, найдите их:
1.	Держать нос по ветру.
2.	Пустой мешок не стоит.
3.	Трое докторов не лучше одного.
4.	Не все то золото, что блестит.
5.	У семи нянек дитя без глаза.',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '3 и 5',
                'is_correct' => true,
            ],
            [
                'content' => '2 и 4',
                'is_correct' => false,
            ],
            [
                'content' => '1 и 5',
                'is_correct' => false,
            ],
            [
                'content' => '1 и 4',
                'is_correct' => false,
            ],
            [
                'content' => '2 и 3',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Какое число должно стоять вместо знака «?»:
73  66  59  52  45  38  «?»',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '31',
                'is_correct' => true,
            ],
            [
                'content' => '30',
                'is_correct' => false,
            ],
            [
                'content' => '33',
                'is_correct' => false,
            ],
            [
                'content' => '32',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Длительность дня и ночи в сентябре почти такая же, как и в:',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'марте',
                'is_correct' => true,
            ],
            [
                'content' => 'июне',
                'is_correct' => false,
            ],
            [
                'content' => 'мае',
                'is_correct' => false,
            ],
            [
                'content' => 'ноябре',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Предположим, что первые два утверждения верны. Тогда заключительное будет:
Все передовые люди – члены партии.
Все передовые люди занимают крупные посты.
Некоторые члены партии занимают крупные посты.',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'верно',
                'is_correct' => true,
            ],
            [
                'content' => 'не верно',
                'is_correct' => false,
            ],
            [
                'content' => 'не определенно',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Поезд проходит 75 см за 1/4 с. Если он будет ехать с той же скоростью, то какое расстояние он пройдет за 5 с?',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '1500 см или 15 м',
                'is_correct' => true,
            ],
            [
                'content' => '1700 см или 17 м',
                'is_correct' => false,
            ],
            [
                'content' => '1200 см или 12 м',
                'is_correct' => false,
            ],
            [
                'content' => '1350 см или 13,5 м',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Если предположить, что два первых утверждения верны, то последнее:
Боре столько же лет, что Маше.
Маша моложе Жени.
Боря моложе Жени.',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'верно',
                'is_correct' => true,
            ],
            [
                'content' => 'неверно',
                'is_correct' => false,
            ],
            [
                'content' => 'неопределенно',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Пять полукилограммовых пачек мясного фарша стоят 2 доллара. Сколько килограмм фарша можно купить за 80 центов:',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '1',
                'is_correct' => true,
            ],
            [
                'content' => '2',
                'is_correct' => false,
            ],
            [
                'content' => '3',
                'is_correct' => false,
            ],
            [
                'content' => '0,5',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Расстилать и растянуть. Эти слова:',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'схожи по смыслу',
                'is_correct' => true,
            ],
            [
                'content' => 'противоположны',
                'is_correct' => false,
            ],
            [
                'content' => 'ни схожи, ни противоположны',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Предположим, что первые два утверждения верны. Тогда последнее будет:
Саша поздоровался с Машей.
Маша поздоровалась с Дашей.
Саша не поздоровался с Дашей',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'не определенно',
                'is_correct' => true,
            ],
            [
                'content' => 'не определенно',
                'is_correct' => false,
            ],
            [
                'content' => 'не верно',
                'is_correct' => false,
            ],
            [
                'content' => 'верно',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Автомобиль стоимостью 2400 долларов был уценен во время сезонной распродажи на 33 1/3%. Сколько стоил автомобиль во время распродажи?',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '1600',
                'is_correct' => true,
            ],
            [
                'content' => '1500',
                'is_correct' => false,
            ],
            [
                'content' => '1400',
                'is_correct' => false,
            ],
            [
                'content' => '1800',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Три из пяти фигур можно соединить таким образом, чтобы получилась разнобедренная трапеция:',
        'image_path' => 'Lii18g1oKgquestions.jpeg',
        'answers' => [
            [
                'content' => '1-2-4',
                'is_correct' => true,
            ],
            [
                'content' => '3-4-5',
                'is_correct' => false,
            ],
            [
                'content' => '1-2-3',
                'is_correct' => false,
            ],
            [
                'content' => '1-2-5',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'На платье требуется 2 1/3 метра ткани. Сколько платьев можно сшить из 42 метров?',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '18',
                'is_correct' => true,
            ],
            [
                'content' => '21',
                'is_correct' => false,
            ],
            [
                'content' => '16',
                'is_correct' => false,
            ],
            [
                'content' => '15',
                'is_correct' => false,
            ],
            [
                'content' => '17',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Значения следующих двух предложений:
Трое докторов не лучше одного.
Чем больше докторов, тем больше болезней.',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'ни сходны, ни противоположны',
                'is_correct' => true,
            ],
            [
                'content' => 'противоположны',
                'is_correct' => false,
            ],
            [
                'content' => 'сходны',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => '«Увеличивать» и «Расширять». Эти слова:',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'сходны',
                'is_correct' => true,
            ],
            [
                'content' => 'противоположны',
                'is_correct' => false,
            ],
            [
                'content' => 'ни сходны, ни противоположны',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Смысл двух пословиц: Швартоваться лучше двумя якорями.
Не клади все яйца.',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'схож',
                'is_correct' => true,
            ],
            [
                'content' => 'противоположны',
                'is_correct' => false,
            ],
            [
                'content' => 'ни сходны, ни противоположны',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Если бы полкило картошки стоило 0,0125 доллара, то сколько килограмм можно было бы купить за 50 центов',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '20',
                'is_correct' => true,
            ],
            [
                'content' => '10',
                'is_correct' => false,
            ],
            [
                'content' => '30',
                'is_correct' => false,
            ],
            [
                'content' => '40',
                'is_correct' => false,
            ],
            [
                'content' => '25',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Один из членов ряда не подходит к другим. Каким числом Вы бы его заменили:
1/4 1/8 1/8 1/4 1/8 1/8 1/4 1/8 1/6',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '1/8',
                'is_correct' => true,
            ],
            [
                'content' => '1/4',
                'is_correct' => false,
            ],
            [
                'content' => '1/6',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => '«Отражаемый» и «воображаемый». Эти слова являются:',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'ни сходными, ни противоположными',
                'is_correct' => true,
            ],
            [
                'content' => 'сходными',
                'is_correct' => false,
            ],
            [
                'content' => 'противоположными',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Сколько соток составляет участок длиною 70 м и шириной 20 м?',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '14',
                'is_correct' => true,
            ],
            [
                'content' => '0,14',
                'is_correct' => false,
            ],
            [
                'content' => '1,4',
                'is_correct' => false,
            ],
            [
                'content' => '140',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Следующие две фразы по значению:
Хорошие вещи дешевы, плохие дороги.
Хорошее качество обеспечивается простотой, плохое – сложностью.',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'сходны',
                'is_correct' => true,
            ],
            [
                'content' => 'противоположны',
                'is_correct' => false,
            ],
            [
                'content' => 'ни сходны, ни противоположны',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Солдат, стреляя в цель, поразил ее в 12,5% случаев. Сколько раз солдат должен выстрелить, чтобы поразить ее сто раз?',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '800',
                'is_correct' => true,
            ],
            [
                'content' => '600',
                'is_correct' => false,
            ],
            [
                'content' => '400',
                'is_correct' => false,
            ],
            [
                'content' => '200',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Один из членов ряда не подходит к другим. Каким числом Вы бы его заменили:
1/4 1/6 1/8 1/9 1/12 1/14',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '1/10',
                'is_correct' => true,
            ],
            [
                'content' => '1/1',
                'is_correct' => false,
            ],
            [
                'content' => '1/13',
                'is_correct' => false,
            ],
            [
                'content' => '1/4',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Три партнера по акционерному обществу решили поделить прибыль поровну. Т. вложил в дело 4500 долларов, К. – 3500 долларов, П. – 2000 долларов. Если прибыль составит 2400 долларов, то насколько меньше прибыль получит Т. по сравнению с тем, как если бы прибыль была разделена пропорционально вкладам?',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '280',
                'is_correct' => true,
            ],
            [
                'content' => '140',
                'is_correct' => false,
            ],
            [
                'content' => '200',
                'is_correct' => false,
            ],
            [
                'content' => '300',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Какие из две приведенных ниже пословиц имеют сходный смысл: 
1.Куй железо, пока горячо.
2.Один в поле не воин.
3.Лес рубят, щепки летят.
4.Не все то золото, что блестит.
5.Не по виду суди, а по делам гляди.',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '4 и 5',
                'is_correct' => true,
            ],
            [
                'content' => '2 и 4',
                'is_correct' => false,
            ],
            [
                'content' => '1 и 3',
                'is_correct' => false,
            ],
            [
                'content' => '1 и 2',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Значение следующих фраз: Лес рубят, щепки летят.
Большое дело не бывает без потерь.',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'сходно',
                'is_correct' => true,
            ],
            [
                'content' => 'противоположно',
                'is_correct' => false,
            ],
            [
                'content' => 'ни сходны, ни противоположны',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Какая из этих фигур наиболее отлична от других:',
        'image_path' => 'oCrJ00jE1cquestions.jpeg',
        'answers' => [
            [
                'content' => '3',
                'is_correct' => true,
            ],
            [
                'content' => '5',
                'is_correct' => false,
            ],
            [
                'content' => '1',
                'is_correct' => false,
            ],
            [
                'content' => '2',
                'is_correct' => false,
            ],
            [
                'content' => '4',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'В печатающейся статье 24 000 слов. Редактор решил использовать шрифт двух размеров. При использовании шрифта большего размера на странице умещается 900 слов, меньшего – 1200. Статья должна занять 21 полную страницу в журнале. Сколько страниц должно быть напечатано меньшим шрифтом?',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '17',
                'is_correct' => true,
            ],
            [
                'content' => '18',
                'is_correct' => false,
            ],
            [
                'content' => '19',
                'is_correct' => false,
            ],
            [
                'content' => '20',
                'is_correct' => false,
            ],
            [
                'content' => '22',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'У Бори было 10 цветов, 5 синих и 5 желтых, а у Сары не было цветов вообще. Боря дал Саре 4 своих цветка. Какое из следующих утверждений теперь непременно является неверным?',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'У Сары есть меньше синих цветов, чем у Бори',
                'is_correct' => true,
            ],
            [
                'content' => 'У Сары есть больше желтых цветов, чем у Бори',
                'is_correct' => false,
            ],
            [
                'content' => 'У Бори есть одинаковое количество желтых и синих цветов',
                'is_correct' => false,
            ],
            [
                'content' => 'У Сары и у Бори есть одинаковое количество желтых цветов',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Какая из нижеследующих наименее похожа на другие?',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'Цветы',
                'is_correct' => true,
            ],
            [
                'content' => 'Поэма',
                'is_correct' => false,
            ],
            [
                'content' => 'Новелла',
                'is_correct' => false,
            ],
            [
                'content' => 'Рисование',
                'is_correct' => false,
            ],
            [
                'content' => 'Проза',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Самый большой орган в организме человека?',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'Кожа',
                'is_correct' => true,
            ],
            [
                'content' => 'Мозг',
                'is_correct' => false,
            ],
            [
                'content' => 'Сердце',
                'is_correct' => false,
            ],
            [
                'content' => 'Печень',
                'is_correct' => false,
            ],
            [
                'content' => 'Почки',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => '«Претензия» и «Претенциозный». Эти слова по своему значению:',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'схожи',
                'is_correct' => true,
            ],
            [
                'content' => 'противоположны',
                'is_correct' => false,
            ],
            [
                'content' => 'ни сходны, ни противоположны',
                'is_correct' => false,
            ],
        ],
    ],
];
    }

    /**
     * Вопросы для казахского экзамена
     */
    private function getQuestionsKz(): array
    {
        return [
    [
        'content' => 'Жылдың он бірінші айы:',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'қараша',
                'is_correct' => true,
            ],
            [
                'content' => 'қазан',
                'is_correct' => false,
            ],
            [
                'content' => 'желтоқсан',
                'is_correct' => false,
            ],
            [
                'content' => 'мамыр',
                'is_correct' => false,
            ],
            [
                'content' => 'ақпан',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => '«Қатал» сөзіне қарама-қарсы мағынаны білдіретін сөз:',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'мейірімді',
                'is_correct' => true,
            ],
            [
                'content' => 'мейірімсіз',
                'is_correct' => false,
            ],
            [
                'content' => 'сұсты',
                'is_correct' => false,
            ],
            [
                'content' => 'қатыгез',
                'is_correct' => false,
            ],
            [
                'content' => 'суық мінезді',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => '«Иә» немесе «Жоқ» деп жауап беріңіз:
 «б.д.» қысқартуы «біздің дәуіріміз» (жаңа дәуір) дегенді білдіреді.',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'Иә',
                'is_correct' => true,
            ],
            [
                'content' => 'Жоқ',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Төмендегі сөздердің қайсысы басқасынан өзгеше:',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'тыңдау',
                'is_correct' => true,
            ],
            [
                'content' => 'ән айту',
                'is_correct' => false,
            ],
            [
                'content' => 'қоңырау шалу',
                'is_correct' => false,
            ],
            [
                'content' => 'әңгіме айту',
                'is_correct' => false,
            ],
            [
                'content' => 'сөйлеу',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => '«Мінсіз» сөзіне қарама-қарсы мағынаны білдіретін сөз:',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'арсыз',
                'is_correct' => true,
            ],
            [
                'content' => 'әділетті',
                'is_correct' => false,
            ],
            [
                'content' => 'адал',
                'is_correct' => false,
            ],
            [
                'content' => 'шыншыл',
                'is_correct' => false,
            ],
            [
                'content' => 'таза',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Төменде көрсетілген сөздердің қайсысы иіс сезімі мен мұрын сияқты «шайнау» сөзімен байланысты:',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'тістер',
                'is_correct' => true,
            ],
            [
                'content' => 'тәтті',
                'is_correct' => false,
            ],
            [
                'content' => 'тіл',
                'is_correct' => false,
            ],
            [
                'content' => 'иіс',
                'is_correct' => false,
            ],
            [
                'content' => 'таза',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Төменде көрсетілген жұп сөздердің қаншасы толығымен бірдей? 
Sharp M.C. и Sharp M.C.
Filder E.H. и Filder E.N.
Connor M.G. и Conner M.G.  
Woesner O.W. и Woerner O.W.
Soderquist P.E. и Soderquist B.E.',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '1',
                'is_correct' => true,
            ],
            [
                'content' => '2',
                'is_correct' => false,
            ],
            [
                'content' => '3',
                'is_correct' => false,
            ],
            [
                'content' => '4',
                'is_correct' => false,
            ],
            [
                'content' => '5',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => '«Анық» сөзіне қарама-қарсы мағынаны білдіретін сөз:',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'белгісіз',
                'is_correct' => true,
            ],
            [
                'content' => 'айқын',
                'is_correct' => false,
            ],
            [
                'content' => 'белгілі',
                'is_correct' => false,
            ],
            [
                'content' => 'нақты',
                'is_correct' => false,
            ],
            [
                'content' => 'екі мағыналы емес',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Кәсіпкер 3500 долларға бірнеше ескі автокөлік сатып алып, оны 5500 долларға сатып, әр автокөліктен 50 доллардан пайда тапқан. Ол қанша автокөлік сатты?',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '40',
                'is_correct' => true,
            ],
            [
                'content' => '30',
                'is_correct' => false,
            ],
            [
                'content' => '20',
                'is_correct' => false,
            ],
            [
                'content' => '10',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => '«Сыңар» сөзінің синонимін табыңыз:',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '1',
                'is_correct' => true,
            ],
            [
                'content' => '2',
                'is_correct' => false,
            ],
            [
                'content' => '3',
                'is_correct' => false,
            ],
            [
                'content' => '4',
                'is_correct' => false,
            ],
            [
                'content' => '5',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Төмендегі 6 жұп сандардың қаншасы толығымен бірдей?
5296 5296
66986 69686
834426 834426
7354256 7354256
61197172 61197172
83238224 83238234',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '4',
                'is_correct' => true,
            ],
            [
                'content' => '3',
                'is_correct' => false,
            ],
            [
                'content' => '2',
                'is_correct' => false,
            ],
            [
                'content' => '1',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => '«Жақын» сөзіне қарама-қарсы мағынаны білдіретін сөз:',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'бөтен',
                'is_correct' => true,
            ],
            [
                'content' => 'дос',
                'is_correct' => false,
            ],
            [
                'content' => 'жолдас',
                'is_correct' => false,
            ],
            [
                'content' => 'бауыр',
                'is_correct' => false,
            ],
            [
                'content' => 'өзгеше',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Сандар қатарындағы мәні төмен сан:
6  0,7  36  0,31  5?',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '0,31',
                'is_correct' => true,
            ],
            [
                'content' => '6',
                'is_correct' => false,
            ],
            [
                'content' => '5',
                'is_correct' => false,
            ],
            [
                'content' => '36',
                'is_correct' => false,
            ],
            [
                'content' => '0,7',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Төменде көрсетілген сөздерді дұрыс сөйлем құрастырылатындай ретпен орналастырыңыз. Жауап - соңғы сөздің соңғы екі дыбысы.
соң дастархан қонақтар мерекелік келген жайылды',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'ды',
                'is_correct' => true,
            ],
            [
                'content' => 'ар',
                'is_correct' => false,
            ],
            [
                'content' => 'ен',
                'is_correct' => false,
            ],
            [
                'content' => 'ік',
                'is_correct' => false,
            ],
            [
                'content' => 'ан',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Төмендегі бес фигураның қайсысы басқалардан өзгеше?',
        'image_path' => '0gazKu1mkJquestions.jpeg',
        'answers' => [
            [
                'content' => '4',
                'is_correct' => true,
            ],
            [
                'content' => '1',
                'is_correct' => false,
            ],
            [
                'content' => '2',
                'is_correct' => false,
            ],
            [
                'content' => '3',
                'is_correct' => false,
            ],
            [
                'content' => '5',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Екі балықшы 36 балық аулады. Біріншісі екіншісінен 8 есе көп аулады. Екіншісі қанша балық аулады?',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '4',
                'is_correct' => true,
            ],
            [
                'content' => '2',
                'is_correct' => false,
            ],
            [
                'content' => '6',
                'is_correct' => false,
            ],
            [
                'content' => '8',
                'is_correct' => false,
            ],
            [
                'content' => '10',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Қай сөз басқаларынан өзгеше:',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'Жылан',
                'is_correct' => true,
            ],
            [
                'content' => 'Ит',
                'is_correct' => false,
            ],
            [
                'content' => 'Тышқан',
                'is_correct' => false,
            ],
            [
                'content' => 'Арыстан',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Төмендегі сөздерді дұрыс сөйлем құрастырылатындай ретпен орналастырыңыз. Сөйлем шындыққа сәйкес келсе – «Д» жауабын таңдаңыз; сәйкес келмесе – «Ж» жауабын таңдаңыз.
жанар тапса қалауын қар',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'Ж',
                'is_correct' => true,
            ],
            [
                'content' => 'Д',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Төменде көрсетілген фразалардың екеуі бірдей мағынаға ие, соларды табыңыз:
1.	Ат төбеліндей.
2.	Ит өлген жерде.
3.	Қиғаш қас.
4.	Мұртын балта шаппайды.
5.	Бұраң бел.',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '3 және 5',
                'is_correct' => true,
            ],
            [
                'content' => '2 және 4',
                'is_correct' => false,
            ],
            [
                'content' => '1 және 5',
                'is_correct' => false,
            ],
            [
                'content' => '1 және 4',
                'is_correct' => false,
            ],
            [
                'content' => '2 және 3',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => '«?» белгісінің орнында қай сан орналасуы тиіс: 73  66  59  52  45  38  «?»',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '31',
                'is_correct' => true,
            ],
            [
                'content' => '30',
                'is_correct' => false,
            ],
            [
                'content' => '33',
                'is_correct' => false,
            ],
            [
                'content' => '32',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Қыркүйектегі күн мен түннің ұзақтығы қай аймен сәйкес:',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'наурызбен',
                'is_correct' => true,
            ],
            [
                'content' => 'маусыммен',
                'is_correct' => false,
            ],
            [
                'content' => 'мамырмен',
                'is_correct' => false,
            ],
            [
                'content' => 'қарашамен',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Алдыңғы екі сөйлемді дұрыс деп есептесек, онда соңғы сөйлем:
Барлық прогрессивті адамдар - партия мүшелері.
Барлық прогрессивті адамдар ірі қызметтерді атқарады.
Кейбір партия мүшелері ірі қызметтерді атқарады.',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'дұрыс',
                'is_correct' => true,
            ],
            [
                'content' => 'дұрыс емес',
                'is_correct' => false,
            ],
            [
                'content' => 'белгісіз',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Егер алдыңғы екі сөйлемді дұрыс деп есептесек, онда соңғысы: Асан мен Үсен жасты.
Асан Әсемнен кіші.
Үсен Әсемнен кіші.',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'дұрыс',
                'is_correct' => true,
            ],
            [
                'content' => 'дұрыс емес',
                'is_correct' => false,
            ],
            [
                'content' => 'белгісіз',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Бес жартыкилограммдық қант 2 доллар тұрады. 80 центке қанша килограмм қант ала аламыз:',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '1',
                'is_correct' => true,
            ],
            [
                'content' => '2',
                'is_correct' => false,
            ],
            [
                'content' => '3',
                'is_correct' => false,
            ],
            [
                'content' => '0,5',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => '«Төсеу» және «Жаю» сөздері:',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'мағыналас сөздер',
                'is_correct' => true,
            ],
            [
                'content' => 'мағынасы қарама-қарсы сөздер',
                'is_correct' => false,
            ],
            [
                'content' => 'мағыналас емес, қарама-қарсы емес сөздер',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Алдыңғы екі сөйлемді дұрыс деп есептесек, онда соңғы сөйлем:
Асан Үсенмен сәлемдесті.
Үсен Әсеммен сәлемдесті.
Асан Әсеммен сәлемдеспеді.',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'белгісіз',
                'is_correct' => true,
            ],
            [
                'content' => 'дұрыс емес',
                'is_correct' => false,
            ],
            [
                'content' => 'дұрыс',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => '2400 доллар тұратын автокөлік жаппай сатылым кезінде 33 1/3% ке түсіріліп бағаланды. Жаппай сатылым кезінде автокөліктің бағасы қанша болды?',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '1600',
                'is_correct' => true,
            ],
            [
                'content' => '1500',
                'is_correct' => false,
            ],
            [
                'content' => '1400',
                'is_correct' => false,
            ],
            [
                'content' => '1800',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Бес фигураның үшеуін көпбұрышты трапеция шығу үшін біріктіруге болады. Ол қай фигуралар:',
        'image_path' => 'LFhBvu66e3questions.jpeg',
        'answers' => [
            [
                'content' => '1-2-4',
                'is_correct' => true,
            ],
            [
                'content' => '3-4-5',
                'is_correct' => false,
            ],
            [
                'content' => '1-2-3',
                'is_correct' => false,
            ],
            [
                'content' => '1-2-5',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Көйлекке 7/3 метр мата қажет. 42 метр матадан қанша көйлек тігуге болады?',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '18',
                'is_correct' => true,
            ],
            [
                'content' => '21',
                'is_correct' => false,
            ],
            [
                'content' => '16',
                'is_correct' => false,
            ],
            [
                'content' => '15',
                'is_correct' => false,
            ],
            [
                'content' => '17',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Төмендегі екі сөйлем мағынасы:
Үш дәрігер бір дәрігерден артық емес.
Дәрігер көп болған сайын, ауру да көп.',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'мағыналас емес, қарама-қарсы емес сөздер',
                'is_correct' => true,
            ],
            [
                'content' => 'мағыналас сөздер',
                'is_correct' => false,
            ],
            [
                'content' => 'мағынасы қарама-қарсы сөздер',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => '«Ұлғайту» және «Кеңейту». Бұл сөздер:',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'мағыналас',
                'is_correct' => true,
            ],
            [
                'content' => 'қарама-қарсы',
                'is_correct' => false,
            ],
            [
                'content' => 'мағыналас емес, қарама-қарсы емес',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Екі мақал-мәтелдің мағынасы:
Қалауын тапса қар жанар.
Ебін тапқан екі асар.',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'мағыналас',
                'is_correct' => true,
            ],
            [
                'content' => 'қарама-қарсы',
                'is_correct' => false,
            ],
            [
                'content' => 'мағыналас емес, қарама-қарсы емес',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => '«Жігерлі» және «Рухты». Бұл сөздер мағынасы жағынан:',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'мағыналас',
                'is_correct' => true,
            ],
            [
                'content' => 'қарама-қарсы',
                'is_correct' => false,
            ],
            [
                'content' => 'мағыналас емес, қарама-қарсы емес',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Егер жарты кг картоп 0,0125 доллар тұрса, 50 центке қанша кг картоп алуға болар еді:',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '20',
                'is_correct' => true,
            ],
            [
                'content' => '10',
                'is_correct' => false,
            ],
            [
                'content' => '30',
                'is_correct' => false,
            ],
            [
                'content' => '40',
                'is_correct' => false,
            ],
            [
                'content' => '25',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Қатардағы сандардың біреуі басқаларына сәйкес емес. Сәйкес санды табыңыз:
1/4 1/8 1/8 1/4 1/8 1/8 1/4 1/8 1/6',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '1/8',
                'is_correct' => true,
            ],
            [
                'content' => '1/4',
                'is_correct' => false,
            ],
            [
                'content' => '1/6',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Жалпы заңдылыққа қайшы келіп отырған әріп?',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'F',
                'is_correct' => true,
            ],
            [
                'content' => 'A',
                'is_correct' => false,
            ],
            [
                'content' => 'O',
                'is_correct' => false,
            ],
            [
                'content' => 'E',
                'is_correct' => false,
            ],
            [
                'content' => 'I',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Ұзындығы 70 м және ені 20 м жер учаскесі қанша соттыққа тең болады?',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '14',
                'is_correct' => true,
            ],
            [
                'content' => '0.14',
                'is_correct' => false,
            ],
            [
                'content' => '1.4',
                'is_correct' => false,
            ],
            [
                'content' => '140',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Солдат нысанаға ата отырып 12,5% жағдайда дәл тигізді. Нысанаға 100 рет тигізу үшін солдат неше рет атуы керек?',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '800',
                'is_correct' => true,
            ],
            [
                'content' => '600',
                'is_correct' => false,
            ],
            [
                'content' => '400',
                'is_correct' => false,
            ],
            [
                'content' => '200',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Қатардағы сандардың біреуі басқаларына сәйкес емес. Сәйкес санды табыңыз:
1/4 1/6 1/8 1/9 1/12 1/14',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '1/10',
                'is_correct' => true,
            ],
            [
                'content' => '1/1',
                'is_correct' => false,
            ],
            [
                'content' => '1/13',
                'is_correct' => false,
            ],
            [
                'content' => '1/4',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Акционерлік қоғамдағы үш серіктес түскен пайданы тең бөліп алғысы келді. Т. – 4500 доллар, К. – 3500 доллар, П. – 2000 доллар айналымға салған. Табыс – 2400 доллар болған жағдайда, салған салымына байланысты пропорционалды бөлгенмен салыстырғанда Т. табысты қаншалықты кем алады?',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '280',
                'is_correct' => true,
            ],
            [
                'content' => '140',
                'is_correct' => false,
            ],
            [
                'content' => '200',
                'is_correct' => false,
            ],
            [
                'content' => '300',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Төмендегі қайсы екі мақал-мәтелдердің мағынасы ұқсас: 
1.Қыз өссе – елдің көркі.
2.Білім – инемен құдық қазғандай.
3.Ананың сүті – бал.
4.Ер ел үшін туады, ел үшін өледі.
5.Отан үшін отқа түс – күймейсің.',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '4 және 5',
                'is_correct' => true,
            ],
            [
                'content' => '2 және 4',
                'is_correct' => false,
            ],
            [
                'content' => '1 және 3',
                'is_correct' => false,
            ],
            [
                'content' => '1 және 2',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Төмендегі бес фигураның қайсысы басқалардан өзгеше?',
        'image_path' => 'e3k2aDRSBlquestions.jpeg',
        'answers' => [
            [
                'content' => '3',
                'is_correct' => true,
            ],
            [
                'content' => '5',
                'is_correct' => false,
            ],
            [
                'content' => '1',
                'is_correct' => false,
            ],
            [
                'content' => '2',
                'is_correct' => false,
            ],
            [
                'content' => '4',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Мақала 24 000 сөзден тұрады. Редактор екі түрлі шрифт қолдануды ұйғарды. Үлкен шрифт қолданған кезде бір бетке – 900, кіші шрифт қолданғанда – 1200 сөз сияды. Мақаланы журналдың 21 бетіне орналастыру қажет. Журналдың қанша бетін кіші шрифтпен басу керек?',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '17',
                'is_correct' => true,
            ],
            [
                'content' => '18',
                'is_correct' => false,
            ],
            [
                'content' => '19',
                'is_correct' => false,
            ],
            [
                'content' => '20',
                'is_correct' => false,
            ],
            [
                'content' => '22',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Әсемде 10 гүл болды, 5 көк және 5 сары, ал Сарада гүл болған жоқ. Әсем Сараға өзінің 4 гүлін берді. Төмендегі тұжырымдардың қайсысы дұрыс емес болып табылады?',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'Әсемге қарағанда Сарада көк гүл аз.',
                'is_correct' => true,
            ],
            [
                'content' => 'Әсемге қарағанда Сарада сары гүл көп.',
                'is_correct' => false,
            ],
            [
                'content' => 'Әсемде көк және сары гүлдер саны тең.',
                'is_correct' => false,
            ],
            [
                'content' => 'Сара мен Әсемде сары гүлдердің саны тең',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Төмендегі сөздердің қайсысы басқаларынан өзгеше?',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'Гүл',
                'is_correct' => true,
            ],
            [
                'content' => 'Поэма',
                'is_correct' => false,
            ],
            [
                'content' => 'Новелла',
                'is_correct' => false,
            ],
            [
                'content' => 'Сурет',
                'is_correct' => false,
            ],
            [
                'content' => 'Проза',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Адам ағзасындағы ең үлкен орган?',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'Тері',
                'is_correct' => true,
            ],
            [
                'content' => 'Ми',
                'is_correct' => false,
            ],
            [
                'content' => 'Жүрек',
                'is_correct' => false,
            ],
            [
                'content' => 'Бауыр',
                'is_correct' => false,
            ],
            [
                'content' => 'Бүйрек',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Әсем он алты жаста – інісінен 4 есе үлкен жаста. Інісінен екі есе үлкен жаста болған кезде Әсемнің жасы қаншада болады?',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => '24',
                'is_correct' => true,
            ],
            [
                'content' => '20',
                'is_correct' => false,
            ],
            [
                'content' => '25',
                'is_correct' => false,
            ],
            [
                'content' => '26',
                'is_correct' => false,
            ],
            [
                'content' => '28',
                'is_correct' => false,
            ],
        ],
    ],
    [
        'content' => 'Егер «ҚЫНТЫ» дыбыстарын ретімен орналастырсаңыз… болады:',
        'image_path' => NULL,
        'answers' => [
            [
                'content' => 'Мұхит',
                'is_correct' => true,
            ],
            [
                'content' => 'Қала',
                'is_correct' => false,
            ],
            [
                'content' => 'Жануар',
                'is_correct' => false,
            ],
            [
                'content' => 'Өзен',
                'is_correct' => false,
            ],
            [
                'content' => 'Ел',
                'is_correct' => false,
            ],
        ],
    ],
];
    }
}
