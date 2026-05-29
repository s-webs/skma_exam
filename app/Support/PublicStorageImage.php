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

        $normalized = ltrim(str_replace('\\', '/', $path), '/');
        $filename = str_contains($normalized, '/')
            ? basename($normalized)
            : $normalized;

        if (! preg_match('/^[a-zA-Z0-9._-]+$/', $filename)) {
            return null;
        }

        return route('public.media.show', ['filename' => $filename]);
    }

    /**
     * Absolute path on disk (checks storage/app/public and public/storage).
     *
     * @param  array<int, string>  $directories
     */
    public static function absolutePathForFilename(string $filename, array $directories = ['questions', 'answers']): ?string
    {
        if (! preg_match('/^[a-zA-Z0-9._-]+$/', $filename)) {
            return null;
        }

        foreach (self::candidateRelativePaths($filename, $directories) as $relative) {
            $absolute = self::absolutePathForRelative($relative);
            if ($absolute !== null) {
                return $absolute;
            }
        }

        return null;
    }

    /**
     * @param  array<int, string>  $directories
     */
    public static function resolveRelativePath(string $path, array $directories): ?string
    {
        $normalized = ltrim(str_replace('\\', '/', $path), '/');

        if (str_contains($normalized, '/')) {
            return self::absolutePathForRelative($normalized) !== null ? $normalized : null;
        }

        foreach (self::candidateRelativePaths($normalized, $directories) as $relative) {
            if (self::absolutePathForRelative($relative) !== null) {
                return $relative;
            }
        }

        return null;
    }

    public static function absolutePathForRelative(string $relative): ?string
    {
        $relative = ltrim(str_replace('\\', '/', $relative), '/');
        $disk = Storage::disk('public');

        if ($disk->exists($relative)) {
            return $disk->path($relative);
        }

        $publicStoragePath = public_path('storage/'.$relative);
        if (is_file($publicStoragePath)) {
            return $publicStoragePath;
        }

        return null;
    }

    /**
     * @param  array<int, string>  $directories
     * @return array<int, string>
     */
    public static function candidateRelativePaths(string $filename, array $directories): array
    {
        $paths = [];

        foreach (self::orderedDirectories($filename, $directories) as $directory) {
            $paths[] = "{$directory}/{$filename}";
        }

        $paths[] = $filename;

        return array_values(array_unique($paths));
    }

    /**
     * @param  array<int, string>  $directories
     * @return array<int, string>
     */
    private static function orderedDirectories(string $filename, array $directories): array
    {
        $lower = strtolower($filename);
        $preferred = $directories;

        if (str_contains($lower, 'answers')) {
            $preferred = ['questions', 'answers'];
        } elseif (str_contains($lower, 'questions')) {
            $preferred = ['questions', 'answers'];
        }

        return array_values(array_unique(array_merge($preferred, ['questions', 'answers'])));
    }
}
