<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Exam;
use App\Models\ExamType;
use App\Models\Question;
use Illuminate\Database\Seeder;

class StaticPsiho2Seeder extends Seeder
{
    public function run(): void
    {
        $examType = ExamType::firstOrCreate(
            ['slug' => 'psixotest-100-minut'],
            [
                'name_ru' => 'Психотест (100 минут)',
                'description' => 'Психологическое тестирование для поступающих',
                'is_active' => true,
            ]
        );

        $this->command->info('Тип экзамена: ' . $examType->name_ru);

        $this->seedExam($examType, 'ru', 'Русский', 'Психотест на русском языке', $this->getQuestionsRu());
        $this->seedExam($examType, 'kz', 'Қазақша', 'Психотест на казахском языке', $this->getQuestionsKz());
        $this->seedExam($examType, 'en', 'English', 'Психотест на английском языке', $this->getQuestionsEn());

        $this->command->info('Статический сидер выполнен успешно!');
    }

    private function seedExam(ExamType $examType, string $language, string $name, string $description, array $questions): void
    {
        $exam = Exam::firstOrCreate(
            [
                'exam_type_id' => $examType->id,
                'language' => $language,
            ],
            [
                'name_ru' => $name,
                'description' => $description,
                'duration_minutes' => 100,
                'questions_count' => count($questions),
                'passing_score' => 45,
                'max_attempts' => 1,
                'is_active' => true,
                'created_by_user_id' => 1,
            ]
        );

        $this->command->info('Экзамен: ' . $exam->name_ru);

        if ($exam->questions()->count() === 0) {
            $this->importQuestions($exam, $questions);
        } else {
            $this->command->warn('Вопросы для экзамена "' . $exam->name_ru . '" уже существуют, пропускаем импорт.');
        }
    }

    private function importQuestions(Exam $exam, array $questions): void
    {
        $this->command->info("Импорт {$exam->name_ru}: " . count($questions) . ' вопросов...');

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
                    'image_path' => $answerData['image_path'] ?? null,
                    'is_correct' => $answerData['is_correct'],
                    'created_by_user_id' => 1,
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
    }

    private function getQuestionsRu(): array
    {
        return array (
  0 => 
  array (
    'content' => 'Звонкие, не имеющие парных глухих:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '[и], [л].',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '[ж], [в].',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '[б], [в].',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '[з], [д].',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '[г], [д].',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  1 => 
  array (
    'content' => 'Укажите звонкий согласный.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '[з\'].',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '[ц].',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '[ф\'].',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '[c\']',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '[п].',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  2 => 
  array (
    'content' => 'Укажите, в каком слове нужно вставить о.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Гр...мадный.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Т...бурет.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Б...тальон.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'К...блук.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Б...тон к завтраку.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  3 => 
  array (
    'content' => 'Укажите морфологический способ образования слова: худший.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Суффиксальный.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Приставочный.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Приставочно-суффиксальный.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Бессуффиксный.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Сложение.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  4 => 
  array (
    'content' => 'Пропущена буква с в',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Бе..шумный.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Бездействие.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Ра..давать.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Издательская.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Бе..вкусный.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  5 => 
  array (
    'content' => 'Укажите слово с буквой И после Ц.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Лекц...я.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '(Нет)кожиц... .',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Шприц... .',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Куриц...н.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Бледнолиц...и.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  6 => 
  array (
    'content' => 'Укажите ряд антонимов:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Праздничность, будничность.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Невежливость, невежество.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Резкость, грубость.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Осторожность, заботливость.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Праздность, пустота.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  7 => 
  array (
    'content' => 'Служебные части речи - это:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Частицы.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Глагольные формы.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Числительные и местоимения.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Междометия.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Наречия.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  8 => 
  array (
    'content' => 'Выберите строку с существительными второго склонения:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Полесье, звено.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Юноша, дядя.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Судьба, родина.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Высота, слепота.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Валя, Ваня.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  9 => 
  array (
    'content' => 'Укажите прилагательное с двумя Н:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Обществе...ый.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Кожа...ый.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Ветре...ый.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Серебря...ый.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Полотая...ый.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  10 => 
  array (
    'content' => 'Словосочетание числительное+существительное в предложении является: Вдруг они увидели, что плывут к ним тридцать кораблей.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Подлежащим.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Сказуемым.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Определением.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Дополнением.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Обстоятельством.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  11 => 
  array (
    'content' => 'Разряд местоимений который включает только одно слово:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Возвратный.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Притяжательное.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Отрицательные.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Определительные.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Вопросительные.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  12 => 
  array (
    'content' => 'Укажите от какого глагола нельзя образовать форму 1-го лица ед.ч.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Победить.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Стесняться.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Бороться.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Ржаветь.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Видеть.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  13 => 
  array (
    'content' => 'Выберите ряд только страдательных причастий.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Прослушанный, выполнен, редактируемый.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Окованный, подбитый, прощавшийся.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Решаемый, хранящий, собиравший.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Разбитый, распиливший, выстроенный.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Замеченный, подстреленный, замешавший.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  14 => 
  array (
    'content' => 'Укажите предложение, в котором нет деепричастного оборота.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Вскоре мы заметили пыльный вихрь несшийся по степи нам навстречу.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Он еще мог бы сделав усилие вырваться из елани обратно.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Лось обирая осинку с высоты своей спокойно глядит на ползущую девочку.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Затаив дыхание сидели дети на холодном камне.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'А девочка тоже ползла по болоту не поднимая вверх высоко головы.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  15 => 
  array (
    'content' => 'Укажите наречие с буквой а на конце.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Изредк... смотреть.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Высок... взлететь.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Насух... вытереть.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Влев... свернуть.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Запрост... справиться.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  16 => 
  array (
    'content' => 'Укажите словосочетание, в котором нет производного предлога.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Ушел, благодаря за помощь.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Двигаться вдоль дороги.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Выдержал испытания, благодаря мужеству.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Обежать вокруг дома.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Растет около дома.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  17 => 
  array (
    'content' => 'Укажите предложение, в котором подчеркнутое слово пишется слитно.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Она сама поскорей утерла свои слезы, что(бы\') не закапать шаль.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Вдруг слева от меня раздались какие(то) крики.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Ее ум был пытлив и равнодушен в одно и то(же) время.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Она тотчас(же) поняла, что он чувствовал смущение.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Она удивилась и долго потом размышляла о том, что(бы) это значило.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  18 => 
  array (
    'content' => 'Укажите вариант, в котором слово с частицей не пишется раздельно.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Далеко ...радостное событие.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '...навидел давно.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '...угомонный малыш.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '...сравненный успех.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '...солоно хлебавши.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  19 => 
  array (
    'content' => 'Междометия - характерная принадлежность:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Разговорного стиля.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Художественного стиля.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Научного стиля.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Публицистического стиля.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Официально - делового стиля.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  20 => 
  array (
    'content' => 'Создают перегной:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Бактерии.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Хордовые.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Вирусы.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Растения.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Грибы.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  21 => 
  array (
    'content' => 'Вещества от листа в корень передвигаются по:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Лубу.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Камбию.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Сердцевине.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Пробке.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Древесине.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  22 => 
  array (
    'content' => 'Раздельнополые цветки характерны для:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Тополя.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Лилии.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Гороха.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Картофеля',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Тюльпана.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  23 => 
  array (
    'content' => 'Стебель соломина характерен для:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Злаковых.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Бобовых.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Лилейных.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Пасленовых.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Сложноцветных.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  24 => 
  array (
    'content' => 'Цирроз – это заболевание:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Печени.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Кожи.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Нервной системы.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Сердца.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Легких.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  25 => 
  array (
    'content' => 'К теплолюбивым культурным растениям относится:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Томат.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Горох.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Лук.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Ячмень.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Рожь.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  26 => 
  array (
    'content' => 'На поверхность кожи выходят протоки железы',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Потовой.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Щитовидной.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Поджелудочной.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Слюнной.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Печени.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  27 => 
  array (
    'content' => 'К периферическому отделу нервной системы относятся:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Нервы.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Мозжечок.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Стволовая часть головного мозга.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Спинной мозг.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Передний мозг.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  28 => 
  array (
    'content' => 'Источником энергии, необходимым для работы мышц, являются:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Органические вещества.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Витамины.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Ферменты.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Минеральные  вещества.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Вода.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  29 => 
  array (
    'content' => 'Возбудитель туберкулеза:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Бактерия.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Вирус.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Инфузория.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Плазмодий.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Амеба.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  30 => 
  array (
    'content' => 'Вредные привычки приводят к:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Снижению болезней.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Нормальному течению физиологических процессов.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Лучшим условиям жизнедеятельности.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Нормальному течения психических процессов.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Повышению болезней.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  31 => 
  array (
    'content' => 'Нуклеотид гуанин комплементарен:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Цитозину.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Аденину.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Тимину.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Гуанину.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Урацилу.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  32 => 
  array (
    'content' => 'Способность к терморегуляции возникла у:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Млекопитающих.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Насекомых.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Пресмыкающихся.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Земноводных.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Червей.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  33 => 
  array (
    'content' => 'Совокупностью всех биоценозов Земли является:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Биосфера.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Литосфера.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Гидросфера.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Ноосфера.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Атмосфера.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  34 => 
  array (
    'content' => 'Мох, имеющий на стебле только одну коробочку со спорами:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Кукушкин лен.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Багрянки.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Ламинария.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Плаун.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Сфагнум.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  35 => 
  array (
    'content' => 'Споры развиваются на кисточках у гриба:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Пеницилла.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Трутовика.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Головни.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Спорыньи.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Мукора.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  36 => 
  array (
    'content' => 'Способ размножения амебы:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Путем деления.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Цистами.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Откладыванием икринок.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Почкованием.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Половым путем.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  37 => 
  array (
    'content' => 'Особенно большое число стрекательных клеток у гидры находится на:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Щупальцах.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Энтодерме.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Подошве.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Внутренней поверхности.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Стебельке.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  38 => 
  array (
    'content' => 'Способ размножения речного рака:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Половым путем.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Почкованием.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Бесполым путем.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Делением.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Партеногенезом.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  39 => 
  array (
    'content' => 'Яйценоская порода кур:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Леггорн.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Загорская.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Виандот.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Плимутрок.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Банкивская.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  40 => 
  array (
    'content' => 'Металлическая связь в веществе',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Ba',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'S',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'HCl',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'P',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'KCl',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  41 => 
  array (
    'content' => 'Частицы с неспаренными электронами, образующиеся при разрыве ковалентной связи',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Радикалы',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Атомы',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Ионы',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Катионы',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Анионы',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  42 => 
  array (
    'content' => 'К химической реакции относится:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'горение полиэтилена',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'измельчение мела',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'испарение уксусной кислоты',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'кристаллизация сахара',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'плавление серебра',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  43 => 
  array (
    'content' => 'Главное квантовое число характеризует',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Общую энергию электрона',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Число электронов в атоме',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Ориентацию орбитали в пространстве',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Направление собственного вращения электрона',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Энергию электрона данного подуровня',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  44 => 
  array (
    'content' => 'К эмульсиям относят смесь:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Бензина и воды',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Уксусной кислоты и песка',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Воды и спирта',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Воды и уксусной кислоты',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Воды и мела',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  45 => 
  array (
    'content' => 'Наиболее легкая фракция перегонки нефти',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Газолиновая',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Керосиновая',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Газойль',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Лигроиновая',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Мазут',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  46 => 
  array (
    'content' => 'Взаимодействие сложных эфиров с водой в присутствии щелочи, называется',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'гидролизом',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'этерификацией',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'гидратацией',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'гидрированием',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'дегидрированием',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  47 => 
  array (
    'content' => 'Средние соли состоят из',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'каитонов металлов и кислотных остатков.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'кислотных остатков и гидроксогрупп, связанных с катионами.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'кислотных остатков с незамещенными атомами водорода.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'двух химически разных катионов и кислотного остатка.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'внешней и внутренней сферы, которая включает комплексообразователь и лиганды',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  48 => 
  array (
    'content' => 'Виноградным сахаром иногда называют',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Глюкозу',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Целлюлозу',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Сахарозу',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Рибозу',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Крахмал',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  49 => 
  array (
    'content' => 'Необратимой будет реакция между',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'магнием и кислородом',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'азотом и кислородом',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'водородом и азотом',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'оксидом серы (IV) и кислородом',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'оксидом углерода (IV) и водой',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  50 => 
  array (
    'content' => 'Денатурация белка это -',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'изменение вторичной, третичной структур белка под влиянием внешних воздействий',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'растворение белка в воде',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'восстановление белка',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'окисление на воздухе',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'образование коллоидного раствора',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  51 => 
  array (
    'content' => 'Первичные спирты могут использоваться для:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Получения сложных эфиров.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Процесса крекинга.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Синтеза углеводов.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Реакций нейтрализации.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Реакций полимеризации.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  52 => 
  array (
    'content' => 'При восстановлении пропаналя образуется',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Пропанол',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Пропилацетат',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Пропан',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Пропановая кислота',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Пропен',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  53 => 
  array (
    'content' => 'В результате полного хлорирования ацетилена образуется',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Тетрахлорэтан',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Дихлорэтан',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Хлорэтан',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Трихлорэтан',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Дихлорэтен',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  54 => 
  array (
    'content' => 'В схеме превращений
Al --+X-->  Al(OH)3  --+Y--> AlOHSO4 --+Z-->  Al(OH)3
Веществами X, Y, Z являются',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'H2O, H2SO4, NaOH',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'H2O, SO3, H2SO4',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'H2O, K2SO4, H2O',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'NaOH, H2SO4, H2SO3',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Al(OH)3, H2SO4, H2O',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  55 => 
  array (
    'content' => 'Для нейтрализации 20,4 г валериановой кислоты потребуется 2%-ный гидроксид натрия массой',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '400 г',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '700 г',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '500 г',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '300 г',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '600 г',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  56 => 
  array (
    'content' => 'Взорвали смесь, содержащую 80 л (н.у.) хлора и 5 г водорода. Объем образовавшегося хлороводорода равен',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '112 л',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '56 л',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '160 л',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '80 л',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '224 л',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  57 => 
  array (
    'content' => 'Вещества Х и У в схеме превращений',
    'image_path' => 'o8JuluhNwcquestions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'ацетилен, поливинилхлорид',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'изопрен, каучук',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'бутадиен, каучук',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'пропилен, полипропилен',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'этилен, полиэтилен',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  58 => 
  array (
    'content' => 'Кислотные свойства оксидов в ряду N2O5 → P2O5 → As2O5 → Sb2O5:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'убывают',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'вначале убывают, затем возрастают',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'отсутствуют',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'не изменяются',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'возрастают',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  59 => 
  array (
    'content' => 'Число частиц Al(NO3)3 в 21,3 г',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '0,6  молекул',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '1,48  молекул',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '6  молекул',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '1,68  молекул',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '0,12  молекул',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  60 => 
  array (
    'content' => 'Какое число должно стоять вместо знака «?»:
73  66  59  52  45  38  «?»',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '31',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '30',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '33',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '32',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '34',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  61 => 
  array (
    'content' => 'Длительность дня и ночи в сентябре почти такая же, как и в:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'марте',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'июне',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'мае',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'ноябре',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'декабре',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  62 => 
  array (
    'content' => 'Предположим, что первые два утверждения верны. Тогда заключительное будет:
Все передовые люди – члены партии.
Все передовые люди занимают крупные посты.
Некоторые члены партии занимают крупные посты.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'верно',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'не верно',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'не определенно',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  63 => 
  array (
    'content' => 'Поезд проходит 75 см за 1/4 с. Если он будет ехать с той же скоростью, то какое расстояние он пройдет за 5 с?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '1500 см или 15 м',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '1700 см или 17 м',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '1200 см или 12 м',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '1350 см или 13,5 м',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '1300 см и 13 м',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  64 => 
  array (
    'content' => 'Если предположить, что два первых утверждения верны, то последнее:
Боре столько же лет, что Маше.
Маша моложе Жени.
Боря моложе Жени.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'верно',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'неверно',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'неопределенно',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  65 => 
  array (
    'content' => 'Пять полукилограммовых пачек мясного фарша стоят 2 доллара. Сколько килограмм фарша можно купить за 80 центов:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '1',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '2',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '3',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '0.5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '4',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  66 => 
  array (
    'content' => 'Расстилать и растянуть. Эти слова:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'схожи по смыслу',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'противоположны',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'ни схожи, ни противоположны',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  67 => 
  array (
    'content' => 'Предположим, что первые два утверждения верны. Тогда последнее будет:
Саша поздоровался с Машей.
Маша поздоровалась с Дашей.
Саша не поздоровался с Дашей',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'не определенно',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'не верно',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'верно',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  68 => 
  array (
    'content' => 'Автомобиль стоимостью 2400 долларов был уценен во время сезонной распродажи на 33 1/3%. Сколько стоил автомобиль во время распродажи?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '1600',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '1500',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '1400',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '1800',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '1700',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  69 => 
  array (
    'content' => 'Три из пяти фигур можно соединить таким образом, чтобы получилась разнобедренная трапеция:',
    'image_path' => 'RAtWREKFWhquestions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => '1-2-4',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '3-4-5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '1-2-3',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '1-2-5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '1-3-5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  70 => 
  array (
    'content' => 'На платье требуется 2 1/3 метра ткани. Сколько платьев можно сшить из 42 метров?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '18',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '21',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '16',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '15',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '17',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  71 => 
  array (
    'content' => 'Значения следующих двух предложений:
Трое докторов не лучше одного.
Чем больше докторов, тем больше болезней.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'ни сходны, ни противоположны',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'противоположны',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'сходны',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  72 => 
  array (
    'content' => '«Увеличивать» и «Расширять». Эти слова:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'сходны',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'противоположны',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'ни сходны, ни противоположны',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  73 => 
  array (
    'content' => 'Смысл двух пословиц:
Швартоваться лучше двумя якорями.
Не клади все яйца.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'схож',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'противоположен',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'ни схож, ни противоположен',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  74 => 
  array (
    'content' => '«Претензия» и «Претенциозный». Эти слова по своему значению:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'схожи',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'противоположны',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'ни сходны, ни противоположны',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  75 => 
  array (
    'content' => 'Следующие две фразы по значению:
Хорошие вещи дешевы, плохие дороги.
Хорошее качество обеспечивается простотой, плохое – сложностью.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'сходны',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'противоположны',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'ни сходны, ни противоположны',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  76 => 
  array (
    'content' => 'Один из членов ряда не подходит к другим. Каким числом Вы бы его заменили:
1/4 1/8 1/8 1/4 1/8 1/8 1/4 1/8 1/6',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '1/8',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '1/4',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '1/6',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '1/7',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '1/5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  77 => 
  array (
    'content' => 'Сколько соток составляет участок длиною 70 м и шириной 20 м?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '14',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '0,14',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '1,4',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '140',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '1400',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  78 => 
  array (
    'content' => '«Отражаемый» и «воображаемый». Эти слова являются:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'ни сходными, ни противоположными',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'сходными',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'противоположными',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  79 => 
  array (
    'content' => 'Если бы полкило картошки стоило 0,0125 доллара, то сколько килограмм можно было бы купить за 50 центов',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '20',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '10',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '30',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '40',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '25',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  80 => 
  array (
    'content' => '',
    'image_path' => 'VeJvGCNvRbquestions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => '',
        'is_correct' => true,
        'image_path' => '08JdAFwdUBanswers.png',
      ),
      1 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'yETlHu3iiJanswers.png',
      ),
      2 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => '8hQTKY0ysFanswers.png',
      ),
      3 => 
      array (
        'content' => '1',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'u0wxMa8ltNanswers.png',
      ),
    ),
  ),
  81 => 
  array (
    'content' => '12,5% от числа составляет 10. Чему равно это число',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '80',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '100',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '70',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '85',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '75',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  82 => 
  array (
    'content' => 'Записать в виде алгебраического выражения: разность произведения чисел m и n и квадрата числа k',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '',
        'is_correct' => true,
        'image_path' => 'KeCZshrCHSanswers.png',
      ),
      1 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'ZyKcvmJFHEanswers.png',
      ),
      2 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'syppJZVNRbanswers.png',
      ),
      3 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'CoCghNVckyanswers.png',
      ),
      4 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'ytzOZ608HSanswers.png',
      ),
    ),
  ),
  83 => 
  array (
    'content' => 'Найдите числовое значение выражения:',
    'image_path' => '10.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => '3',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '4',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'YmiqOYLlDhanswers.png',
      ),
      3 => 
      array (
        'content' => '1.5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'PXr5iupvsEanswers.png',
      ),
    ),
  ),
  84 => 
  array (
    'content' => 'Найдите углы параллелограмма, если один из них больше другого на 50 градусов',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '65 и 115 градусов',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '125 и 55 градусов',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '75 и 105 градусов',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '50 и 130 градусов',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '60 и 120 градусов',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  85 => 
  array (
    'content' => 'За 2,5 кг баранины заплатили 475 тенге, тогда по той же цене на 665 тенге баранины можно купить',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '3.5 кг',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '3.25 кг',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '4 кг',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '5 кг',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '3 кг',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  86 => 
  array (
    'content' => 'Решите уравнение:',
    'image_path' => '6v8D5UYwGIquestions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => '',
        'is_correct' => true,
        'image_path' => 'tPVEdHp4dLanswers.png',
      ),
      1 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'oYPkdumzqWanswers.png',
      ),
      2 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'qZArpxjt1canswers.png',
      ),
      3 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'hzHRWd3NRXanswers.png',
      ),
      4 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'SFrf3KG0EGanswers.png',
      ),
    ),
  ),
  87 => 
  array (
    'content' => 'Выразите у через х из уравнения 10х – 5у – 7 = 0',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'у = 2х – 1,4',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'у = 2х + 1,4',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'у = 2х – 7',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'у = 2х + 7',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'у = –2х – 1,4',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  88 => 
  array (
    'content' => 'Найдите производную функции',
    'image_path' => 'GS9xLCmYfvquestions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => '',
        'is_correct' => true,
        'image_path' => 'J9QppCEgpyanswers.png',
      ),
      1 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'skcHTiMioManswers.png',
      ),
      2 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'uankGBeL3aanswers.png',
      ),
      3 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'eG6DbnhhGKanswers.png',
      ),
      4 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => '62vfu7yyxianswers.png',
      ),
    ),
  ),
  89 => 
  array (
    'content' => 'Найдите производную функции',
    'image_path' => 'N6w60qq94yquestions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => '',
        'is_correct' => true,
        'image_path' => 'KEcnW9QMtYanswers.png',
      ),
      1 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'ScJATMrIefanswers.png',
      ),
      2 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => '8LfJCWKf82answers.png',
      ),
      3 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'BEG7CuVgQ9answers.png',
      ),
      4 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'rw157RfKZ6answers.png',
      ),
    ),
  ),
  90 => 
  array (
    'content' => 'Чему равен угол треугольника со сторонами 5 см, 12 см и 13 см, противолежащий стороне 13 см?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '90 градусов',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '30 градусов',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '45 градусов',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '25 градусов',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '60 градусов',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  91 => 
  array (
    'content' => 'В треугольнике АВС АВ = ВС. Высота АК делит сторону ВС на отрезки ВК = 24 см и КС = 1 см. Найдите площадь треугольника АВС',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '87,5 см2',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '25 см2',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '875 см2',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '175 см2',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '276 см2',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  92 => 
  array (
    'content' => '',
    'image_path' => 'p8z9fdTBr8questions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => '',
        'is_correct' => true,
        'image_path' => '3aE6YZnfMPanswers.png',
      ),
      1 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'PUAhMX0h9Aanswers.png',
      ),
      2 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'Y1Tr82oJbyanswers.png',
      ),
      3 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'N6M8qwXkf1answers.png',
      ),
      4 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'E2h1XZbAeIanswers.png',
      ),
    ),
  ),
  93 => 
  array (
    'content' => 'Радиусы оснований усеченного конуса 10 см, 4 см, высота 8 см. Найдите образующую',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '10 см',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '5 см',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '100 см',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '20 см',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '6 см',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  94 => 
  array (
    'content' => '',
    'image_path' => 'WL1S7hiZOPquestions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => '47',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '65',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '49',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '25',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '51',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  95 => 
  array (
    'content' => 'Моторная лодка прошла 12 км против течения реки и 12 км по течению раки, затратив на весь путь против течения на 1 час больше, чем на путь по течению. Найти скорость течения реки, если скорость лодки в стоячей воде 9 км/ч',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '3 км/ч',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '2,5 км/ч',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '2 км/ч',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '1 км/ч',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '3,5 км/ч',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  96 => 
  array (
    'content' => 'Решите неравенство:',
    'image_path' => 'fg2i1Tio3uquestions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => '(-2; 3)',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '(1; -5)',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '(6; 1)',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '(-1; -3)',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '(5; 4)',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  97 => 
  array (
    'content' => '',
    'image_path' => 'u219jmG5dyquestions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => '',
        'is_correct' => true,
        'image_path' => 'Qwa8Kz8d7vanswers.png',
      ),
      1 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'fOQSC8DWPianswers.png',
      ),
      2 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'ZhOsCc16z4answers.png',
      ),
      3 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'G9ZK14Wtptanswers.png',
      ),
      4 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => '9Ulm56nz3xanswers.png',
      ),
    ),
  ),
  98 => 
  array (
    'content' => '',
    'image_path' => 'mwETgPFyUQquestions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => '(x – 2)(5ax – b – 1)',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '(x – 2)(ax – 5b – 1)',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '(x – 2)(5ax + b + 1)',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '(x + 2)(5ax – b – 1)',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '(x + 2)(5ax – 1)',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  99 => 
  array (
    'content' => 'Упростите выражение:',
    'image_path' => '6YUb1TFhdmquestions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => '',
        'is_correct' => true,
        'image_path' => 'Vx7vm0QkuVanswers.png',
      ),
      1 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => '8ExYE1A3BBanswers.png',
      ),
      2 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'AylzKUdb4Uanswers.png',
      ),
      3 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => '0xqgfgBLmNanswers.png',
      ),
      4 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'bIisJKmswaanswers.png',
      ),
    ),
  ),
);
    }
    private function getQuestionsKz(): array
    {
        return array (
  0 => 
  array (
    'content' => 'Қазақ тiлiнде неше дыбыс бар?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '38',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '36',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '42',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '12',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '26',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  1 => 
  array (
    'content' => 'Үндi дауыссыздар қатарын табыңыз.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'л,м,н,й,у',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'з,қ,к,л,м',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'а,о,ы,i,у',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'п,б,т,с,ш',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'д,г,ғ,з,в',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  2 => 
  array (
    'content' => 'Дыбыстық өзгерiске ұшырап бiрiккен сөздi көрсетiңiз.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Бүгiн',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Алатау',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Жаңатас',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Сезiмтал',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Екiбастұз',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  3 => 
  array (
    'content' => 'Синоним сөздердiң тiзбегiн табыңыз.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Халық, ел',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Нәрсе, киiм',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Теңдiк, құлдық',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Тарих, қоғам',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  4 => 
  array (
    'content' => 'Омоним болатын сөз қайсы?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Қыс',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Бүкiл',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Анда-санда',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Жеңеше',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'әрине',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  5 => 
  array (
    'content' => 'Сiлтеу есiмдiгiн табыңыз.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Анау, мынада, осы',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Он, алтау, бесеу',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Тақ, жүз, оқы',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Үйде, кеште, ерте',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Бүгiн, ертең, кеше',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  6 => 
  array (
    'content' => 'Септеулiк шылаумен байланысып тұрған тiркестi көрсетiңiз.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Жиналыстан кейiн',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Қазбектiң жоспары',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Қамысты көл',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Туыс адам',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Орта мектеп',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  7 => 
  array (
    'content' => 'Негiзгi сын есiмдi көрсетiңiз.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Ақ, қоңыр',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Ақылды, арлы',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Жазған, оқыр',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Ана, мына',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Белсендi, еңбекқор',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  8 => 
  array (
    'content' => 'Бiтеу буыннан тұратын сөздер қатарын табыңыз.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Заң, тау',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Ақ, ар',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Тазала, оқы',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Ас, қал',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Дос, тарақ',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  9 => 
  array (
    'content' => 'Антоним кездесетiн сөйлемдi көрсетiңiз.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Жақсы мен жаманның арасы жер мен көктей',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Ақ қағазды ермек қылайын',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Адамды заман билейдi.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Ырыс алды ынтымақ',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Дос жылатып айтады.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  10 => 
  array (
    'content' => 'Көсемшенің жұрнағы жалғанған сөзді көрсетіңіз.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'баршы',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'барғалы',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'барды',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'барса',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'бару',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  11 => 
  array (
    'content' => 'Еркiн сөз тiркесiн көрсетiңiз.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Түйме қадау',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Су жүрек',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Қас пен көздiң арасы',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Тас бауыр',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Төбе шашы тiк тұрды',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  12 => 
  array (
    'content' => 'Үндестiк заңына бағынбай тұрған сөздi көрсетiңiз.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'өнерпаз',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Қонақжай',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Ақылды',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Ақылды',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Балалар',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  13 => 
  array (
    'content' => 'Тәуелдiк тұлғадағы есiмдiктер қатарын көрсетiңiз.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Менiкi, сенiкi, сiздiкi',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Ауылым, жаным, жасым',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'әкесi, ағасы, үйi',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'әркiм, бiреу, ешкiм',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'гүлдің, топырақтың',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  14 => 
  array (
    'content' => 'Қай қатардағы сөздер үндестiк заңына бағынбайды?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Еңбекқор, кiтапты',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Қазақтар, татарлар',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Апарады, танысу',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Есiмiм, әсем',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Дүйсенбiде, жұманың',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  15 => 
  array (
    'content' => 'Қай дауыссыз дыбыс сөздiң cоңында кездеспейдi?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Ғ',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'П',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Қ',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'С',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Т',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  16 => 
  array (
    'content' => 'Түбiр сөздер тобын табыңыз.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'сары, жүрек, бала',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'доcтық, ғылыми, бөлiмде',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'аударған, оған, бiлiктi',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'тiлегiм, сезiм, көңiлi',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'бала, балалық,балақай',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  17 => 
  array (
    'content' => 'Жiктеу есiмдiктерi қатарын белгiлеңiз.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'бiздiкi, менде, сенiң, оларға',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'ешкiм, мынау, соған, мынаған',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'барлық, осы, ешқашан, мынау',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'түгел, бұны, соның, барша',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'күллісі, ешкім, бәрі',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  18 => 
  array (
    'content' => 'Тәуелдiк жалғаулы сөздер қатарын белгiлеңiз.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'қалам, ойың, інісі',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'бiлiм, ғалым, ғылым',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'әлем, сөйлем, сәлем',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'жүзiм, әсем, киiм',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'дүниені, қалаларға',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  19 => 
  array (
    'content' => 'Септеуліктерді көрсетіңіз.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'дейін',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'немесе',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'керек',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'бірақ',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'ғана',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  20 => 
  array (
    'content' => 'Терек тұқымының таралу жолы:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Желмен.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Құстармен.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Өздігінен.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Бунақденелілермен.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Сумен.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  21 => 
  array (
    'content' => 'Бүршіктер бөлінеді:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Жапырақты және гүлді',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Өркенді',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Жапырақты, сабақты',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Гүлді, тамырлы',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Тамырлы, сабақты',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  22 => 
  array (
    'content' => 'Бунақденелілер қоректенеді:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Гүл шірнесімен.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Аналық жыныс жасушасымен.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Аналық түйінімен.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Бүршікпен.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Гүл күлтесімен.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  23 => 
  array (
    'content' => 'Дара жарнақты өсімдік:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Қарабидай.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Қияр.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Күнбағыс.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Үрмебұршақ.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Асқабақ.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  24 => 
  array (
    'content' => 'Қызанның тұқымдасы:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Алқа тұқымдас.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Күрделігүлділер.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Лалагүл тұқымдасы.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Бұршақ тұқымдас.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Астық тұқымдас.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  25 => 
  array (
    'content' => 'Шымтезек мүгінің құрылысы:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Сабақ, жапырақ.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Ризоид, сабақ, жапырақ.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Ризоид, сабақ.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Тамыр, сабақ, жапырақ.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Гүл, жеміс, тұқым.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  26 => 
  array (
    'content' => 'Тек қана тірі ағзалардың денесіндегі органикалық заттармен қоректенетін бактериялар -',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Паразиттер.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Сүтқышқылы бактериялар.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Түйнек бактериялары.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Сапрофиттер.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Топырақ бактериялары.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  27 => 
  array (
    'content' => 'Паразитті қарапайымдыларға жататын-',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'дизентерия (қантышқақ) амебасы.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'сәулелілер.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'кірпікшелі кебісше.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'қабыршақты тамыраяқ.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'кәдімгі амеба.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  28 => 
  array (
    'content' => 'Гидра тыныс алады:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Бүкіл денесімен.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Өкпе қапшығымен.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Желбезекпен.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Өкпемен.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Демтүтікпен.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  29 => 
  array (
    'content' => 'Қылқұрттың қауіптілігі:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Дернәсілі бунақденелілердің паразиті',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Адамға ауру туғызады',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Суда жұмыртқаламайды',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Адамның бұлшықетін зақымдайды',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Миды зақымдайды',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  30 => 
  array (
    'content' => 'Киімдерге дернәсілдері зиян келтіретін бунақдене:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Қаракүйе.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Өлібас.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Қантамшы.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Маса.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Тұткөбелек.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  31 => 
  array (
    'content' => 'Қабыршақты жорғалаушыларға жататын:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'жыландар',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'крокодилдер',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'саламандралар',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'тасбақалар',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'тритондар',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  32 => 
  array (
    'content' => 'Адамның құрсақ қуысында орналасатын мүше:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Бүйрек.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Жүрек.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Жұтқыншақ.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Өкпе.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Өңеш.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  33 => 
  array (
    'content' => 'Бас ми бағанының құрамына кірмейтін:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'үлкен ми сыңарлары',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'аралық ми',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'ми көпіршесі',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'сопақша ми',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'ортаңғы ми',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  34 => 
  array (
    'content' => 'Кез келген бұлшық еттердің жиырылуы байланысты:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Энергияны пайдалануға.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Қозуға.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Көмірқышқыл газын пайдалануға.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Оттегін пайдалануға.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Тежелуге.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  35 => 
  array (
    'content' => 'Оң жақ қарыншадан шығатын қантамыр:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Өкпе салатамыры.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Көктамыр.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Қолқа.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Өкпе көктамыры.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Қылтамыр.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  36 => 
  array (
    'content' => 'Адам тыныс алуына қажетті газ:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'оттегі',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'азот (IV) оксиді',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'азот',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'азот (II) оксиді',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'көмірқышқыл газы',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  37 => 
  array (
    'content' => 'Ас қорыту жүйесінің ішкі қабырғасы ... тұрады:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'эпителий ұлпасынан',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'көлденең жолақты бұлшық ет ұлпасынан',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'бірыңғай салалы бұлшық ет ұлпасынан',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'дәнекер ұлпадан',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'жүйке ұлпасынан',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  38 => 
  array (
    'content' => 'Дәрумендер (витаминдер) ... түзілуіне қатысады.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'ферменттердің',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'көмірсулардың',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'майлардың',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'гормондардың',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'тұздардың',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  39 => 
  array (
    'content' => 'Физикалық жұмыс кезінде жылудың теріден сыртқа шығарылу себебі:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Тердің булануынан.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Бауырдың күшті жұмысынан.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Қанда қанттың көбеюінен.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Лүпілдің баяулауынан.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Лүпілдің артуынан.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  40 => 
  array (
    'content' => 'Заттардың қайсысы жай затқа жатады?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Р4',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'РН3',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Р2 О3',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'P2 O5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'РСl3',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  41 => 
  array (
    'content' => 'Теріс иондарды қалай атайды?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Аниондар',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Анодтар',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Тотықтырғыштар',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Каниондар',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Акцепторлар',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  42 => 
  array (
    'content' => 'Мышьяктың жоғары оксидінің формуласындағы индекстр қосындысы',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '7',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '4',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '6',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '8',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  43 => 
  array (
    'content' => 'Aзонның өшшекке айналатын теңдеуіндегі барлық коэффициенттер қосындыы.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '5',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '4',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '2',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '3',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '6',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  44 => 
  array (
    'content' => 'Төменде көрсетілген құбылыстардың қайсысы химиялық реакцияларға жатады?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'В, D',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Мұздың еруі',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Күкірттің жануы',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Қанттың еруі',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Сүттің ашуы',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  45 => 
  array (
    'content' => 'Алюминийдің валенттігі нешеге тең?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '3',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '1',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '2',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '4',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  46 => 
  array (
    'content' => '1 моль көміртекте атом саны қанша?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '6,02 ∙ 10²³',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '6,02²³',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '6 ∙ 23¹º',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '1• 10²³',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '12',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  47 => 
  array (
    'content' => '10 моль күкірттің массасы (г) –',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '320 г.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '32 г.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '3,2 г.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '10 г.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '16 г.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  48 => 
  array (
    'content' => 'Өнеркәсіпте оттекті қай заттан алады?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Ауадан',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Судан',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'КМnO4',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'KClO3',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'HgO',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  49 => 
  array (
    'content' => 'Төмендегі реакция теңдеуіндегі оттектің алдына қандай коэффициент қойылады CH4 + O2 → CO2 + H2O?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '2',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '1',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '3',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '4',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  50 => 
  array (
    'content' => 'Су әрекеттеседі:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Активті металдармен',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Қышқылдармен',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Негіздермен',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Барлық металдармен',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Сутекпен',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  51 => 
  array (
    'content' => 'Қосылыстар арасынан қышқылды көрсет:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'HNO3',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Н2О',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Са(ОН)2',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'NH3',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'NaCl',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  52 => 
  array (
    'content' => 'Периодтық жүйенің 7-ге тең горизонтальді жолдары аталады:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Периодтар',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Топтар',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Қатарлар',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Жолдар',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Топшалар',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  53 => 
  array (
    'content' => 'Кремний атомындағы электрон қабаттарының саны:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '3',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '1',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '2',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '4',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  54 => 
  array (
    'content' => 'Темір атомының ядросындағы протондар саны:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '26',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '20',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '55',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '30',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '8',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  55 => 
  array (
    'content' => 'Бром Br2 молекуласындағы химиялық байланыс түрі:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Ковалентт полюссіз',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Иондық',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Металдық',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Коваленттік',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Ковалентті полюсті',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  56 => 
  array (
    'content' => 'Қай металл сілтілікке жатады:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Na',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Ca',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Fe',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Ni',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Дұрыс жауап жоқ',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  57 => 
  array (
    'content' => 'Қай затпен хлор әрекеттеспейді?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'CuO',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'H2',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Na',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'K',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Ca',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  58 => 
  array (
    'content' => 'Период нөмірі сәйкес келеді:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Электрон қабаттарының санына',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Протондар санына',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Сыртқы қабаттағы электрондар санына',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Нейтрондар санына',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Электрондардың жалпы санына',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  59 => 
  array (
    'content' => 'Диссоциация кезінде анион ретінде тек гидроксид-ион, түзетін заттар аталады:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Сілтілер',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Қышқылдар',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Орта тұздар',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Қышқыл тұздар',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Қос тұздар',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  60 => 
  array (
    'content' => '«?» белгісінің орнында қай сан орналасуы тиіс:
73	66  59  52  45  38  «?»',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '31',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '30',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '33',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '32',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '34',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  61 => 
  array (
    'content' => 'Қыркүйектегі күн мен түннің ұзақтығы қай аймен сәйкес:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'наурызбен',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'маусыммен',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'мамырмен',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'қарашамен',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'желтоқсанмен',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  62 => 
  array (
    'content' => 'Алдыңғы екі сөйлемді дұрыс деп есептесек, онда соңғы сөйлем:
Барлық прогрессивті адамдар - партия мүшелері.
Барлық прогрессивті адамдар ірі қызметтерді атқарады.
Кейбір партия мүшелері ірі қызметтерді атқарады.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'дұрыс',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'дұрыс емес',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'белгісіз',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  63 => 
  array (
    'content' => 'Поезд ¼ секундта 75 см қашықтықты жүріп өтті. Егер ол осы жылдамдықты сақтаса 5 секундта қанша қашықтықты жүріп өтеді?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '1500 см немесе 15 м',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '1700 см немесе 7 м',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '1200 см немесе 12 м',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '1350 см немесе 13,5 м',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '1300 см немесе 13 м',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  64 => 
  array (
    'content' => 'Егер алдыңғы екі сөйлемді дұрыс деп есептесек, онда соңғысы:
Асан мен Үсен жасты.
Асан Әсемнен кіші.
Үсен Әсемнен кіші.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'дұрыс',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'дұрыс емес',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'белгісіз',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  65 => 
  array (
    'content' => 'Бес жарты килограммдық қант 2 доллар тұрады. 80 центке қанша килограмм қант ала аламыз:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '1',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '2',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '3',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '0.5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '4',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  66 => 
  array (
    'content' => '«Төсеу» және «Жаю» сөздері:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'мағыналас сөздер',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'мағынасы қарама-қарсы сөздер',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'мағыналас емес',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'қарама-қарсы емес сөздер',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'қарама-қарсы сөздер',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  67 => 
  array (
    'content' => 'Алдыңғы екі сөйлемді дұрыс деп есептесек, онда соңғы сөйлем:
Асан Үсенмен сәлемдесті.
Үсен Әсеммен сәлемдесті.
Асан Әсеммен сәлемдеспеді.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'белгісіз',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'дұрыс емес',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'дұрыс',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  68 => 
  array (
    'content' => '2400 доллар тұратын автокөлік жаппай сатылым кезінде 33 1/3% ке түсіріліп бағаланды. Жаппай сатылым кезінде автокөліктің бағасы қанша болды?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '1600',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '1500',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '1400',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '1800',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '1700',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  69 => 
  array (
    'content' => 'Бес фигураның үшеуін көпбұрышты трапеция шығу үшін біріктіруге болады. Ол қай фигуралар:',
    'image_path' => 'fX58n2boTTquestions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => '1-2-4',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '3-4-5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '1-2-3',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '1-2-5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '1-5-2',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  70 => 
  array (
    'content' => 'Көйлекке 7/3 метр мата қажет. 42 метр матадан қанша көйлек тігуге болады?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '18',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '21',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '16',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '15',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '17',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  71 => 
  array (
    'content' => 'Төмендегі екі сөйлем мағынасы:
Үш дәрігер бір дәрігерден артық емес.
Дәрігер көп болған сайын, ауру да көп.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'мағыналас емес, қарама-қарсы емес сөздер',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'мағыналас сөздер',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'мағынасы қарама-қарсы сөздер',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  72 => 
  array (
    'content' => '«Ұлғайту» және «Кеңейту». Бұл сөздер:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'мағыналас',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'қарама-қарсы',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'мағыналас емес, қарама-қарсы емес',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  73 => 
  array (
    'content' => 'Екі мақал-мәтелдің мағынасы: 
Қалауын тапса қар жанар.
Ебін тапқан екі асар.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'мағыналас',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'қарама-қарсы',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'мағыналас емес, қарама-қарсы емес',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  74 => 
  array (
    'content' => '«Жігерлі» және «Рухты». Бұл сөздер мағынасы жағынан:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'мағыналас',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'қарама-қарсы',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'мағыналас емес, қарама-қарсы емес',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  75 => 
  array (
    'content' => 'Егер жарты кг картоп 0,0125 доллар тұрса, 50 центке қанша кг картоп алуға болар еді:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '20',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '10',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '30',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '40',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '25',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  76 => 
  array (
    'content' => 'Қатардағы сандардың біреуі басқаларына сәйкес емес. Сәйкес санды табыңыз:
1/4 1/8 1/8 1/4 1/8 1/8 1/4 1/8 1/6',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '1/8',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '1/4',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '1/6',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '1/7',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '1/5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  77 => 
  array (
    'content' => 'Жалпы заңдылыққа қайшы келіп отырған әріп?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'F',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'A',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'O',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'E',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'I',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  78 => 
  array (
    'content' => 'Ұзындығы 70 м және ені 20 м жер учаскесі қанша соттыққа тең болады?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '14',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '0.14',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '1.4',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '140',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  79 => 
  array (
    'content' => 'Солдат нысанаға ата отырып 12,5% жағдайда дәл тигізді. Нысанаға 100 рет тигізу үшін солдат неше рет атуы керек?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '800',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '600',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '400',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '200',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '100',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  80 => 
  array (
    'content' => 'Есептеңіз: (5,75-0,75) 0,01',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '0.05',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '0.02',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '500',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '0.5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  81 => 
  array (
    'content' => '',
    'image_path' => 'WTdS8ANOe4questions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => '0',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '9',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '18',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '1.8',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '1',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  82 => 
  array (
    'content' => 'Көбейткішке жікте: а(3+b)+b+3',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '(b+3)(a+1)',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '(a+b)3',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '(3+b)(a–1)',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '(b+3)a',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '(3+b)(1-a)',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  83 => 
  array (
    'content' => 'Екі айлақтың арасы 55,4 км. Кеме екі айлақтың арасын ағыспен 2 сағат жүрді. Ағыс жылдамдығы 2,8 км/сағ, кеменің өз жылдамдығын табыңыз.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '24,9 км/сағ',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '25 км/сағ',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '29,1 км/сағ',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '26,3 км/сағ',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '30,5 км/сағ',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  84 => 
  array (
    'content' => 'Квадраттың бір қабырғасы 12 см. Периметрін табыңыз.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '48см',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '24см',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '96см',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '36см',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '16см',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  85 => 
  array (
    'content' => '',
    'image_path' => 'AKMspYBTjMquestions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => '3',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'ErGUnoGdS2answers.png',
      ),
      2 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'ZtxSEomw2Tanswers.png',
      ),
      3 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'DZxrsuG3Yvanswers.png',
      ),
      4 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'BGmUWiIvBfanswers.png',
      ),
    ),
  ),
  86 => 
  array (
    'content' => 'Функцияның  анықталу облысын тап',
    'image_path' => 'V8lKvSCuvIquestions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => '',
        'is_correct' => true,
        'image_path' => 'jgboOETlHuanswers.png',
      ),
      1 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => '36dQuJA8qHanswers.png',
      ),
      2 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => '16V8IW5958answers.png',
      ),
      3 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'ysg8sn3NqZanswers.png',
      ),
      4 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'QJqRghbesGanswers.png',
      ),
    ),
  ),
  87 => 
  array (
    'content' => 'Теңсіздіктің дұрыс аралық шешімін анықтаңыз:',
    'image_path' => 'HnUhD4eOnIquestions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => '',
        'is_correct' => true,
        'image_path' => '13.png',
      ),
      1 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'quSmvs8acyanswers.png',
      ),
      2 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => '5lx4UgTiqtanswers.png',
      ),
      3 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'MMljkQlaPdanswers.png',
      ),
      4 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'MMx0vps2vQanswers.png',
      ),
    ),
  ),
  88 => 
  array (
    'content' => 'Аудандары 80 га және 120 га тең болатын екі участоктен барлығы 7200ц астық жиналды. Бірінші участокта әрбір 3 га-да жиналған астықтың мөлшері екінші участокта 2 га-да жиналған астық мөлшерінен 10 ц-ге артық деп алып, әр участоктің 1 га-нан қаншадан астық жиналғанын табыңыз.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '30 ц; 40 ц.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '50 ц; 60 ц.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '40 ц; 50 ц.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '35 ц; 45 ц.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '20 ц; 30 ц.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  89 => 
  array (
    'content' => 'Теңдеуді шешіңіз:',
    'image_path' => 'HXJY9FCyf0questions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => '{2}.',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '{2; 3}.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '{-3}.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '{-2; -3}.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '{-2}.',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  90 => 
  array (
    'content' => 'Конустың жасаушысы табан жазықтығына 30 градус бұрыш жасай көлбеген және 8 см-ге тең. Конустың осьтік қимасының ауданын табыңыз.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '',
        'is_correct' => true,
        'image_path' => 'qDzlnKxyiDanswers.png',
      ),
      1 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'gL6aCJcqr7answers.png',
      ),
      2 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'xLjpDtBW22answers.png',
      ),
      3 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => '52zWMIKI8Xanswers.png',
      ),
      4 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'Dq2E5p5ewBanswers.png',
      ),
    ),
  ),
  91 => 
  array (
    'content' => 'Функцияның туындысын тап:',
    'image_path' => '5htmivgRMIquestions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => '',
        'is_correct' => true,
        'image_path' => '7HXNEaS4Fianswers.png',
      ),
      1 => 
      array (
        'content' => '1',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => '8XZaPm5c6Panswers.png',
      ),
      3 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => '7PYurHHnTSanswers.png',
      ),
      4 => 
      array (
        'content' => '0',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  92 => 
  array (
    'content' => 'M(7; -11) нүктесінен y=-2x+5 түзуге параллель өтетін түзудің теңдеуін табыңыз',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'y=-2x+3',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'y=-2x+4',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'y=-2x-1',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'y=-2x+2',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'y=-2x+1',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  93 => 
  array (
    'content' => '',
    'image_path' => 'pjmRKgpl3Yquestions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'x-sin5x+C',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'x+cos5x+C',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'x+5cos5x+C',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'x+5sin5x+C',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'x-5cos5x+C',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  94 => 
  array (
    'content' => '',
    'image_path' => 'gGQU4fa2Igquestions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => '2',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '-1',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '4',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '0',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '-2',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  95 => 
  array (
    'content' => '',
    'image_path' => '2SVrXp6RaJquestions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => '',
        'is_correct' => true,
        'image_path' => '14tl7J7D9Zanswers.png',
      ),
      1 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'fdpHcpCVq4answers.png',
      ),
      2 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => '1bM27lyFyNanswers.png',
      ),
      3 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'EI0eGQUM59answers.png',
      ),
      4 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'rcwWH0i6QKanswers.png',
      ),
    ),
  ),
  96 => 
  array (
    'content' => '',
    'image_path' => 'Pvp6kIQ0WZquestions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Тақ',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Жұп та емес, тақ та емес',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Жалпы түрде',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Жұп',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Периодты',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  97 => 
  array (
    'content' => 'Арифметикалық прогрессияның он үшінші мүшесін үшінші мүшесіне бөлсек, бөліндісі 3-ке тең, ал он сегізінші мүшесін жетінші мүшесіне бөлсек, толымсыз бөлінді 2, қалдық 8-ге тең. Прогрессияның бірінші мүшесін, айырмасын табыңыз.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '',
        'is_correct' => true,
        'image_path' => 'vGQ4h2eIhOanswers.png',
      ),
      1 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => '5jpMrUZxkkanswers.png',
      ),
      2 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'LOGWPrUmgtanswers.png',
      ),
      3 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'xGz7aDnd7Aanswers.png',
      ),
      4 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'PUXJQslyRbanswers.png',
      ),
    ),
  ),
  98 => 
  array (
    'content' => '',
    'image_path' => 'DqP7yjGVUuquestions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => '',
        'is_correct' => true,
        'image_path' => 'tpbNmPwb2Sanswers.png',
      ),
      1 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'VmXx6wZrOHanswers.png',
      ),
      2 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'KkGqSjMOfVanswers.png',
      ),
      3 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => '0RPpjupcbBanswers.png',
      ),
      4 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'q5GDPtI1cBanswers.png',
      ),
    ),
  ),
  99 => 
  array (
    'content' => 'Шеңбердің радиусы 5 см-ге тең. Центрлік бұрышы 45 градус болып келетін сегментің ауданын табу керек.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '',
        'is_correct' => true,
        'image_path' => 'fmQQflqnFTanswers.png',
      ),
      1 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'Gj6Btq21kqanswers.png',
      ),
      2 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'hBFTBQ0W1Panswers.png',
      ),
      3 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'xgpElpGxEoanswers.png',
      ),
      4 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'EtyOM54waTanswers.png',
      ),
    ),
  ),
);
    }
    private function getQuestionsEn(): array
    {
        return array (
  0 => 
  array (
    'content' => 'She _____ a new pair of gloves, as she _____ her old one.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'bought / had lost',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'bought / lost',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Had bought / lost',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'was buying / lose',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'bought / has lost',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  1 => 
  array (
    'content' => 'Choose the right answer
I _____ no news from my family since I _____ to work here.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Have had / began',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'have / had begun',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'have / will begin',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'have / have begun',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'has / has begun',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  2 => 
  array (
    'content' => 'The floor _____ by 6 o’clock yesterday.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'had been painted',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'was painted',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'painted',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'was painting',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'was being painted',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  3 => 
  array (
    'content' => 'Choose the right answer
We heard him _____ this story yesterday.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Tell',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'To tell',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Told',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Had told',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Has told',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  4 => 
  array (
    'content' => 'We saw _____ the paper.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Them signing',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Them to sign',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'They sign',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'They to sign',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'They signing',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  5 => 
  array (
    'content' => 'Choose the right answer
He wants the document _____ by the end of the working day.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'To have been translated',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'To translate',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'To have translated',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Has been translated',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Is translated',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  6 => 
  array (
    'content' => '_____ very ill, she couldn’t attend classes.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Being',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Be',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'To be',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Was',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Has been',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  7 => 
  array (
    'content' => 'Choose the right answer
He is still trying to make me _____ my mind.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'change',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'to change',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'changed',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'changing',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'to have changed',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  8 => 
  array (
    'content' => 'Choose the right answer
The music could _____ from far away.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'be heard',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'hear',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'to be heard',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'hearing',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'heard',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  9 => 
  array (
    'content' => 'Choose the right answer
I’d like _____.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'you to join us',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'you join us',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'you joining us',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'your join us',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'your joining us',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  10 => 
  array (
    'content' => 'She keeps _____ us what to do.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'telling',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'to tell',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'tell',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'of telling',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'be telling',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  11 => 
  array (
    'content' => 'Choose the right answer
Mr. Smith is said _____ a good lecturer.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'to be',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'being',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'of being',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'to being',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'been',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  12 => 
  array (
    'content' => 'The meeting is reported _____ next June.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'to open',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'opened',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'opening',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'opens',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'to have opened',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  13 => 
  array (
    'content' => 'Choose the right answer
She enjoyed the film _____ on TV yesterday.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'shown',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'showed',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'showing',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'was shown',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'being showed',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  14 => 
  array (
    'content' => 'While _____ the article he had to look up some words in the dictionary.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'reading',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'read',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'reads',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'being reading',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'being read',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  15 => 
  array (
    'content' => 'Choose the right answer
If you _____ some news let me know.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'get',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'will get',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'would get',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'got',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'to get',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  16 => 
  array (
    'content' => 'What _____ you do if you didn’t know the examination material?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'would',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'will',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'did',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'had',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'shall',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  17 => 
  array (
    'content' => 'I wish she _____ more friends here.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'had',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'have',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'hadn’t',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'didn’t have',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'has',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  18 => 
  array (
    'content' => 'Choose the right answer
If I were you I _____ to convince your friend.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'would try',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'tried',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'didn’t try',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'will try',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'try',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  19 => 
  array (
    'content' => 'I _____ there on condition that you accompany me.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'will go',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'went',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'would have gone',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'would go',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'have gone',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  20 => 
  array (
    'content' => 'The main types of tissues:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'all answers are correct',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'connective',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'nerve',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'epithelial',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'muscle',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  21 => 
  array (
    'content' => 'The function of muscle cells:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'contractile',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'support',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'coupling',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'nutritional',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'collagen synthesis',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  22 => 
  array (
    'content' => 'The functions of the nervous tissue cells:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'the ability to conduct electrical impulses',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'secretory',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'contractile',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'nutritional',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'collagen synthesis',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  23 => 
  array (
    'content' => 'The function of epithelial cells:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'ability to secrete liquid',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'support',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'coupling',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'nutritional',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'collagen synthesis',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  24 => 
  array (
    'content' => 'Functions of connective tissue cells all except:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'the ability to secrete liquid',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'support',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'coupling',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'nutritional',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'collagen synthesis',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  25 => 
  array (
    'content' => 'The main intermediate filament proteins (one of the main part of cytoskeleton):',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'vimentin, keratin, lamin',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'actin',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Tubulin',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'keratin, tubulin',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'lamin, actin',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  26 => 
  array (
    'content' => 'Ability to adjust to living with nature is',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Adaptation',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Attempt',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Assimilation',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Fighting',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Defending',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  27 => 
  array (
    'content' => 'The cytoskeleton is composed of:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'microtubules, actin and intermediate filaments',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'microtubules',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'end of filaments',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'actin filaments, macrotubules',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'melanin',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  28 => 
  array (
    'content' => 'The main protein of microtubules:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Tubulin',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'actin',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'vimentin',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'keratin',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'microtubulin',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  29 => 
  array (
    'content' => 'The main protein actin filaments:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'actin',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Tubulin',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'vimentin',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'keratin',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'microtubulin',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  30 => 
  array (
    'content' => 'The components of the cell nucleus are:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'nucleoplasm',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'surface receptors',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'secretory vesicles',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'mitochondria',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'endoplasmic reticullum',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  31 => 
  array (
    'content' => 'Organisms in the Kingdom Animalia are:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'mutlicellular and heterotrophic',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'multicellular and autotrophic',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'unicellular and autotrophic',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'unicellular and heterotrophic',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'multicellular and detritivore',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  32 => 
  array (
    'content' => 'Which of the following groups would contain the largest number of organisms?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'phylum',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'order',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'class',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'family',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'kingdom',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  33 => 
  array (
    'content' => 'The nucleotid has the following components:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'nitrogen-containing nucleobases, a sugar called deoxyribose and a phosphate group',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'alongside proteins',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'nuclear matrix',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'chromatin',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'complex carbohydrates (polysaccharides)',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  34 => 
  array (
    'content' => 'The function of ribosomes within the cell is',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'to synthesize proteins',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'to ferment carbohydrates',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'to produce ATP',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'all of these',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'to defend',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  35 => 
  array (
    'content' => 'What do plants and animals have in common?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'both are eukaryotic',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'both are autotrophic',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'both are prokaryotic',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'both are heterotrophic',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'both do photosynthesis',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  36 => 
  array (
    'content' => 'Which of the following is an example of autoimmune disease?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'None above',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Systemic lupus erythematosus',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Polyarteritis nodosa',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'All of the above',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  37 => 
  array (
    'content' => 'One of two identical arms that make up a chromosome:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'chromatid',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'spindle',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'peptidase',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'DNA',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'protein',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  38 => 
  array (
    'content' => 'Negative regulation of protein synthesis is accomplished by',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'the binding of a repressor to the DNA',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'the binding of RNA polymerase to the promoter',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'allosteric inhibition',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'the binding of a repressor to the RNA polymerase',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'the binding ligands to the receptor',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  39 => 
  array (
    'content' => 'The worms breathe through',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Skin',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Lungs',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Gill',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Mouth',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Tale',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  40 => 
  array (
    'content' => 'Amount of all coefficients in equation reaction of calcium with water is equal to …',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '5',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '3',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '7',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '9',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '6',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  41 => 
  array (
    'content' => 'Formula of substance with covalent polar bond is …',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'HCl',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'CaCl2',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Na2O',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Cl2',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Сu',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  42 => 
  array (
    'content' => 'Which oxide is an amphoteric?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'ZnО',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'SiО2',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'SiО',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Nа2О',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'СаО',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  43 => 
  array (
    'content' => 'Which salt is acid salt?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Fe (НСО3)3',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '[Fe(ОН)2]2СО3',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Fe ОН СО3',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Fe2 (CО3)3',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'NaCl',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  44 => 
  array (
    'content' => 'Which of the next given substances is soluble in water?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Zn(NO3)2',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Cu(OH)2',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'AgBr',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'HgS',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'АgCl',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  45 => 
  array (
    'content' => 'Which acid corresponds to the title «sulfurous acid»?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Н2SО3',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Н2S2О3',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Н2SО4',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'H2SO2',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'H3 SO3',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  46 => 
  array (
    'content' => 'What substances form Mn2+ ions on dissociation?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'MnCI2',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'KMnO4',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'Na2MnO4',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'MnO2',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Mn2O3',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  47 => 
  array (
    'content' => 'How many ions formed on (NH4)2SO4 molecule dissociation?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '3',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '9',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '2',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '4',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '1',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  48 => 
  array (
    'content' => 'Covalent bond is effected on account of …',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'two general electrons, or electronic pairs',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'valence electrons',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'electronic clouds',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'the electrostatic force gravity',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'atoms radius',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  49 => 
  array (
    'content' => 'A pair of elements having similar structure of external energy levels:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'S and Se',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'В and Si',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'К and Са',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'Мn and Fe',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Na and Mg',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  50 => 
  array (
    'content' => 'What type of bond in a molecule of  NaCl:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'ion',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'polar covalent',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'non-polar covalent',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'hydrogen',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'metallic',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  51 => 
  array (
    'content' => 'The molecule ... oxidation state is zero, and the valence is one.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Br2',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'HCl',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'N2',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'NH3',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'О2',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  52 => 
  array (
    'content' => 'The maximum number of electrons on the d-sublevel is:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '10',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '2',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '8',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '6',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '3',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  53 => 
  array (
    'content' => 'The chemical bond between two different atoms with electron pair is called:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'a polar covalent',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'nonpolar',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'hydrogen',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'ion',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'metallic',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  54 => 
  array (
    'content' => 'How many electrons contain ion Cr+3',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '21',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '25',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '18',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '23',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '20',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  55 => 
  array (
    'content' => 'Electronic formula of arsenic:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '',
        'is_correct' => true,
        'image_path' => 'r2M39eYDc3answers.png',
      ),
      1 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'gzwjHJT3vbanswers.png',
      ),
      2 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'gLyFgwTMDPanswers.png',
      ),
      3 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'fgrQRJ79CTanswers.png',
      ),
      4 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'KJ3PDqGEvFanswers.png',
      ),
    ),
  ),
  56 => 
  array (
    'content' => 'The organic compound which has carbon atoms with no ring chain',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'acyclic',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'carbocyclic',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'alicyclic',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'heterocyclic',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'aromatic',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  57 => 
  array (
    'content' => 'Ester group:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '–COOR',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '–CHOS',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '–COOH',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '–SO3H',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '-COOP',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  58 => 
  array (
    'content' => 'The general formula for monohydroxy alcohols is…',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'CnH2n+2O',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'CnH2nO2',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'CnH2n+2',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'CnH2nO',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'CnH2n-2O',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  59 => 
  array (
    'content' => 'Strong base is …',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'dimethylamine',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'methanol',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'methylamine',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'anyline',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'phenol',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  60 => 
  array (
    'content' => 'The eleventh month of the year is:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'November',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'October',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'December',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'May',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'February',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  61 => 
  array (
    'content' => '"Severe" is the opposite in meaning of the word:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'soft',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'strict',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'hard',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'sharp',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Intractable',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  62 => 
  array (
    'content' => 'Which of the words below is different from the others:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'doubtful',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'confident',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'trust',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'faithful',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'certain',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  63 => 
  array (
    'content' => 'How many hundredths is a section 70 m long and 20 m wide?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '14',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '1.4',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '140',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '1400',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '0.14',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  64 => 
  array (
    'content' => 'Which of the following words is different from the others:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'listen',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'sing',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'call',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'chatting',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'speak',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  65 => 
  array (
    'content' => 'The word "impeccable" is the opposite in meaning of the word:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'obscene',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'spotless',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'incorruptible',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'innocent',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'classic',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  66 => 
  array (
    'content' => 'Which of the following words refers to the word “chew” as a sense of smell and nose:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'teeth',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'language',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'smell',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'clean',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'sweet',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  67 => 
  array (
    'content' => 'How many of the following word pairs are completely identical?
Sharp M.C. и Sharp M.C.
Filder E.H. и Filder E.N.
Connor M.G. и Conner M.G.  
Woesner O.W. и Woerner O.W.
Soderquist P.E. и Soderquist B.E.',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '1',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '2',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '3',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '4',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  68 => 
  array (
    'content' => '"Clear" is the opposite in meaning of the word:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'dull',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'Explicit',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'obvious',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'distinct',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'Unambiguous',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  69 => 
  array (
    'content' => 'The entrepreneur bought several used cars for $ 3,500, and sold them for $ 5,500, while earning $50 per car. How many cars did he sell?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '40',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '20',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '10',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '30',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '50',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  70 => 
  array (
    'content' => 'The words “Knock” and “Stoke” have:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'neither the same nor the opposite',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'similar meaning',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'opposite meaning',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  71 => 
  array (
    'content' => 'How many of these 6 pairs of numbers are exactly the same?
5296 5296
66986 69686
834426 834426
7354256 7354256
61197172 61197172
83238224 83238234',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '4',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '3',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '2',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '1',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  72 => 
  array (
    'content' => '“Close” is the opposite of the word:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'alien',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'friendly',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'native',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'other',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'friendly',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  73 => 
  array (
    'content' => 'Which is the smallest number:
6  0,7  36  0,31  5?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '0.31',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '6',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '36',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '0.7',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  74 => 
  array (
    'content' => 'Three of the five figures can be connected in such a way as to create a isosceles trapezoid:',
    'image_path' => 'jdbLaISPn9questions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => '1-2-4',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '3-4-5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '1-2-3',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '1-2-5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '1-3-5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  75 => 
  array (
    'content' => 'Which of the following five figures is the most different from the others?',
    'image_path' => 'h0b4gJuxHequestions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => '4',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '1',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '2',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '3',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  76 => 
  array (
    'content' => 'Two fishermen caught 36 fish. The first caught 8 times more than the second. How many caught the second?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '4',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '2',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '6',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '8',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '10',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  77 => 
  array (
    'content' => '“Rise” and “revive” have:',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'Neither the same nor the opposite',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'opposite meaning',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'similar meaning',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  78 => 
  array (
    'content' => 'A dress requires 2 1/3 meters of fabric. How many dresses can be sewn from 42 meters?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '18',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '16',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '17',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '15',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '21',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  79 => 
  array (
    'content' => 'What number should stand instead of the “?” Sign:
73 66 59 52 45 38 "?"',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '31',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '33',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '32',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '30',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '34',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  80 => 
  array (
    'content' => '',
    'image_path' => 'E0MDKMFwsUquestions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => '2.15',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '2.35',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '2.3',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '1.95',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '0.75',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  81 => 
  array (
    'content' => '',
    'image_path' => 'A22KJYNe7equestions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => '4.6',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '-4.6',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '3',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '2.3',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '-1',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  82 => 
  array (
    'content' => '',
    'image_path' => '2hdwVJRQpvquestions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'no solutions',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '1',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '2',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '-2',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '-1',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  83 => 
  array (
    'content' => 'To solve the inequality: (2x+1)(5x-4)>0',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '(-∞; -0,5) U (0,8; ∞)',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '(-∞; -5) U (4; ∞)',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '(-0,5; 0,8)',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '[-0,5; 0,8]',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '(-0,5; 4)',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  84 => 
  array (
    'content' => '',
    'image_path' => 'AtllY0N6OHquestions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => '1',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'KkwMrldq2Eanswers.png',
      ),
      2 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'mfs91Bn6Npanswers.png',
      ),
      3 => 
      array (
        'content' => '-1',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '0.5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  85 => 
  array (
    'content' => '',
    'image_path' => 'FmTqHp3Xcxquestions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => '1',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'RDrGYmLu8Canswers.png',
      ),
      2 => 
      array (
        'content' => '0.5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '-0.5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '-1',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  86 => 
  array (
    'content' => '',
    'image_path' => 'LgsXpOc1wrquestions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => '',
        'is_correct' => true,
        'image_path' => 'aFqtcWS4Wtanswers.png',
      ),
      1 => 
      array (
        'content' => '-1',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'AczB1YttnIanswers.png',
      ),
      3 => 
      array (
        'content' => '-0.5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '0.5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  87 => 
  array (
    'content' => '',
    'image_path' => 'QAUnCvGWSYquestions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => '',
        'is_correct' => true,
        'image_path' => 'TWLeiod5jnanswers.png',
      ),
      1 => 
      array (
        'content' => '-1',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => '1wCXvUpHV3answers.png',
      ),
      3 => 
      array (
        'content' => '-0.5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '0.5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  88 => 
  array (
    'content' => '',
    'image_path' => 'x4HkK4F6wxquestions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => '0.5',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => '9FnXPrP48Qanswers.png',
      ),
      2 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'EDjIN3FGA1answers.png',
      ),
      3 => 
      array (
        'content' => '-0.5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '-1',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  89 => 
  array (
    'content' => '',
    'image_path' => 'qPe0vQLkfoquestions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => '',
        'is_correct' => true,
        'image_path' => 'aPKpuu8Yedanswers.png',
      ),
      1 => 
      array (
        'content' => '0.5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '-1',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'xzbScr8nt8answers.png',
      ),
      4 => 
      array (
        'content' => '-0.5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  90 => 
  array (
    'content' => '',
    'image_path' => 'FToB591ATuquestions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => '0.5',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '-1',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'PHsQaJwqmnanswers.png',
      ),
      3 => 
      array (
        'content' => '-0.5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'xYLEf82Hbganswers.png',
      ),
    ),
  ),
  91 => 
  array (
    'content' => '',
    'image_path' => 'hoG6Zyrwilquestions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => '',
        'is_correct' => true,
        'image_path' => '0O641p2oyOanswers.png',
      ),
      1 => 
      array (
        'content' => '0.5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '',
        'is_correct' => false,
        'image_path' => 'qdbLO40n72answers.png',
      ),
      3 => 
      array (
        'content' => '-1',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '-0.5',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  92 => 
  array (
    'content' => 'To calculate the perimeter of the triangle, if its sides are 5,7, 12',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '24',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '27',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '57',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '42',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '47',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  93 => 
  array (
    'content' => 'To calculate the perimeter of the triangle, if its sides are 4, 6, 12',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '22',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '57',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '47',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '42',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '25',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  94 => 
  array (
    'content' => 'To calculate the perimeter of the triangle, if its sides are 19, 21, 12',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => '52',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => '57',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => '27',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => '42',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => '47',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  95 => 
  array (
    'content' => 'What is the graph of the function y=3x-4?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'straight',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'parabola',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'cubic parabola',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'hyperbola',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'circle',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  96 => 
  array (
    'content' => 'What is the graph of the function y=5/x?',
    'image_path' => NULL,
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'hyperbola',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'parabola',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'circle',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'cubic parabola',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'straight',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  97 => 
  array (
    'content' => 'What is the graph of the function',
    'image_path' => 'uFofTIHwgCquestions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'circle',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'hyperbola',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'parabola',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'straight',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'cubic parabola',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  98 => 
  array (
    'content' => 'What is the graph of the function',
    'image_path' => 'vd2nW1izGnquestions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'parabola',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'circle',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'straight',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'hyperbola',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'cubic parabola',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
  99 => 
  array (
    'content' => 'What is the graph of the function',
    'image_path' => 'DvvWXFWuBrquestions.png',
    'answers' => 
    array (
      0 => 
      array (
        'content' => 'cubic parabola',
        'is_correct' => true,
        'image_path' => NULL,
      ),
      1 => 
      array (
        'content' => 'straight',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      2 => 
      array (
        'content' => 'hyperbola',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      3 => 
      array (
        'content' => 'circle',
        'is_correct' => false,
        'image_path' => NULL,
      ),
      4 => 
      array (
        'content' => 'parabola',
        'is_correct' => false,
        'image_path' => NULL,
      ),
    ),
  ),
);
    }
}
