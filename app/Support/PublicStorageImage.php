<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class PublicStorageImage
{
    /**
     * @param  array<int, string>  $directories
     */
    public static function urlFor(?string $path, array $directories): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $normalized = ltrim($path, '/');
        $disk = Storage::disk('public');

        foreach ($directories as $directory) {
            $relative = str_contains($normalized, '/')
                ? $normalized
                : "{$directory}/{$normalized}";

            if ($disk->exists($relative)) {
                return $disk->url($relative);
            }
        }

        $fallbackDir = $directories[0] ?? 'questions';
        $fallback = str_contains($normalized, '/')
            ? $normalized
            : "{$fallbackDir}/{$normalized}";

        return $disk->url($fallback);
    }
}
