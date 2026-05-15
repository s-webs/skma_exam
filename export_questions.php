<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Exam;

// Получаем все экзамены
$exams = Exam::with('examType')->get();

echo "Найдено экзаменов: " . $exams->count() . "\n\n";

foreach ($exams as $exam) {
    echo "ID: {$exam->id}, Язык: {$exam->language}, Тип: {$exam->exam_type_id}, Название: {$exam->name}\n";
}

echo "\n";

// Получаем экзамены по языку
$examRu = Exam::where('language', 'ru')->with('questions.answers')->first();
$examKz = Exam::where('language', 'kz')->with('questions.answers')->first();

if (!$examRu) {
    echo "Русский экзамен не найден\n";
    exit(1);
}

if (!$examKz) {
    echo "Казахский экзамен не найден\n";
    exit(1);
}

// Экспортируем русские вопросы
echo "// Русские вопросы (Экзамен ID: {$examRu->id})\n";
echo "\$questionsRu = [\n";
foreach ($examRu->questions as $question) {
    echo "    [\n";
    echo "        'content' => " . var_export($question->content, true) . ",\n";
    echo "        'image_path' => " . var_export($question->image_path, true) . ",\n";
    echo "        'answers' => [\n";
    foreach ($question->answers as $answer) {
        echo "            [\n";
        echo "                'content' => " . var_export($answer->content, true) . ",\n";
        echo "                'is_correct' => " . var_export($answer->is_correct, true) . ",\n";
        echo "            ],\n";
    }
    echo "        ],\n";
    echo "    ],\n";
}
echo "];\n\n";

// Экспортируем казахские вопросы
echo "// Казахские вопросы (Экзамен ID: {$examKz->id})\n";
echo "\$questionsKz = [\n";
foreach ($examKz->questions as $question) {
    echo "    [\n";
    echo "        'content' => " . var_export($question->content, true) . ",\n";
    echo "        'image_path' => " . var_export($question->image_path, true) . ",\n";
    echo "        'answers' => [\n";
    foreach ($question->answers as $answer) {
        echo "            [\n";
        echo "                'content' => " . var_export($answer->content, true) . ",\n";
        echo "                'is_correct' => " . var_export($answer->is_correct, true) . ",\n";
        echo "            ],\n";
    }
    echo "        ],\n";
    echo "    ],\n";
}
echo "];\n";

