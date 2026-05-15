<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class MigrateImagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Начинаем миграцию изображений...');

        // Пути к старым проектам
        $psiho1Path = 'C:/Projects/skma-test/psiho1/public/uploads/questions';
        $psiho2Path = 'C:/Projects/skma-test/psiho2/public/uploads/questions';

        // Путь к новому хранилищу
        $newStoragePath = storage_path('app/public/questions');

        // Создаем директорию если не существует
        if (!File::exists($newStoragePath)) {
            File::makeDirectory($newStoragePath, 0755, true);
            $this->command->info('Создана директория: ' . $newStoragePath);
        }

        // Копируем из psiho1
        if (File::exists($psiho1Path)) {
            $this->copyImages($psiho1Path, $newStoragePath, 'psiho1');
        } else {
            $this->command->warn('Директория psiho1 не найдена: ' . $psiho1Path);
        }

        // Копируем из psiho2
        if (File::exists($psiho2Path)) {
            $this->copyImages($psiho2Path, $newStoragePath, 'psiho2');
        } else {
            $this->command->warn('Директория psiho2 не найдена: ' . $psiho2Path);
        }

        $this->command->info('Миграция изображений завершена!');
    }

    /**
     * Копирование изображений из источника в назначение
     */
    private function copyImages(string $sourcePath, string $destinationPath, string $source): void
    {
        $files = File::allFiles($sourcePath);
        $copied = 0;
        $skipped = 0;

        $this->command->info("Копирование из {$source}: найдено " . count($files) . " файлов");

        $progressBar = $this->command->getOutput()->createProgressBar(count($files));
        $progressBar->start();

        foreach ($files as $file) {
            $filename = $file->getFilename();
            $destination = $destinationPath . '/' . $filename;

            // Если файл уже существует, пропускаем
            if (File::exists($destination)) {
                $skipped++;
            } else {
                File::copy($file->getPathname(), $destination);
                $copied++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
        $this->command->info("Скопировано: {$copied}, Пропущено: {$skipped}");
    }
}
