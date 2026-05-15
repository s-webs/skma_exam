<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageOptimizationService
{
    /**
     * Оптимизирует и конвертирует изображение в WebP
     *
     * @param UploadedFile $file
     * @param string $directory Директория для сохранения (например, 'questions' или 'answers')
     * @param int $maxWidth Максимальная ширина изображения
     * @param int $quality Качество WebP (0-100)
     * @return string Имя файла
     */
    public function optimizeAndStore(
        UploadedFile $file,
        string $directory,
        int $maxWidth = 1200,
        int $quality = 85
    ): string {
        // Создаем менеджер изображений с GD драйвером
        $manager = new ImageManager(new Driver());

        // Читаем изображение
        $image = $manager->read($file->getPathname());

        // Изменяем размер если изображение больше максимальной ширины
        if ($image->width() > $maxWidth) {
            $image->scale(width: $maxWidth);
        }

        // Генерируем уникальное имя файла
        $filename = uniqid() . '.webp';

        // Конвертируем в WebP и сохраняем
        $encoded = $image->toWebp($quality);

        // Сохраняем в storage
        Storage::disk('public')->put(
            $directory . '/' . $filename,
            (string) $encoded
        );

        return $filename;
    }

    /**
     * Удаляет старое изображение если оно существует
     *
     * @param string|null $path
     * @param string $directory
     * @return void
     */
    public function deleteOldImage(?string $path, string $directory): void
    {
        if ($path && Storage::disk('public')->exists($directory . '/' . $path)) {
            Storage::disk('public')->delete($directory . '/' . $path);
        }
    }
}
