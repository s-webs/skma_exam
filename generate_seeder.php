<?php

$exported = file_get_contents('exported_questions.txt');

// Извлекаем массивы из экспортированного файла
preg_match('/\/\/ Русские вопросы.*?\$questionsRu = (\[.*?\]);/s', $exported, $ruMatch);
preg_match('/\/\/ Казахские вопросы.*?\$questionsKz = (\[.*?\]);/s', $exported, $kzMatch);

$seederTemplate = file_get_contents('database/seeders/StaticPsihotestSeeder.php');

// Заменяем метод getQuestionsRu
$seederTemplate = preg_replace(
    '/private function getQuestionsRu\(\): array\s*\{.*?return \[.*?\];\s*\}/s',
    'private function getQuestionsRu(): array
    {
        return ' . $ruMatch[1] . ';
    }',
    $seederTemplate
);

// Заменяем метод getQuestionsKz
$seederTemplate = preg_replace(
    '/private function getQuestionsKz\(\): array\s*\{.*?return \[.*?\];\s*\}/s',
    'private function getQuestionsKz(): array
    {
        return ' . $kzMatch[1] . ';
    }',
    $seederTemplate
);

file_put_contents('database/seeders/StaticPsihotestSeeder.php', $seederTemplate);

echo "Сидер успешно сгенерирован!\n";
