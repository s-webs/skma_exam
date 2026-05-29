<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class PublicStorageImage
{
    /** @var array<string, string|null> */
    private static array $absolutePathCache = [];

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

        if (! preg_match('#^[a-zA-Z0-9._/-]+$#', $normalized)) {
            return null;
        }

        $filename = basename($normalized);

        if (! preg_match('/^[a-zA-Z0-9._-]+$/', $filename)) {
            return null;
        }

        // Always via Laravel: nginx often blocks /storage/*.png (403) or /media/*.png (404).
        return route('public.exam-media.show', ['filename' => $filename]);
    }

    /**
     * @param  array<int, string>  $directories
     */
    public static function absolutePathForFilename(string $filename, array $directories = ['questions', 'answers']): ?string
    {
        if (! preg_match('/^[a-zA-Z0-9._-]+$/', $filename)) {
            return null;
        }

        if (array_key_exists($filename, self::$absolutePathCache)) {
            return self::$absolutePathCache[$filename];
        }

        foreach (self::candidateRelativePaths($filename, $directories) as $relative) {
            $absolute = self::absolutePathForRelative($relative);
            if ($absolute !== null) {
                return self::$absolutePathCache[$filename] = $absolute;
            }
        }

        return self::$absolutePathCache[$filename] = self::findByFilenameRecursive($filename);
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

        $absolute = self::findByFilenameRecursive($normalized);

        if ($absolute === null) {
            return null;
        }

        return self::relativePathFromAbsolute($absolute);
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
        $paths[] = "uploads/questions/{$filename}";
        $paths[] = "uploads/answers/{$filename}";

        return array_values(array_unique($paths));
    }

    private static function findByFilenameRecursive(string $filename): ?string
    {
        foreach (self::searchRoots() as $root) {
            if (! is_dir($root)) {
                continue;
            }

            try {
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS)
                );

                foreach ($iterator as $file) {
                    if ($file->isFile() && $file->getFilename() === $filename) {
                        return $file->getPathname();
                    }
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private static function searchRoots(): array
    {
        $roots = [
            public_path('storage'),
            storage_path('app/public'),
        ];

        $customRoot = env('EXAM_MEDIA_ROOT');
        if (is_string($customRoot) && $customRoot !== '') {
            array_unshift($roots, $customRoot);
        }

        return array_values(array_unique($roots));
    }

    private static function relativePathFromAbsolute(string $absolute): ?string
    {
        $absolute = str_replace('\\', '/', $absolute);

        foreach (self::searchRoots() as $root) {
            $root = rtrim(str_replace('\\', '/', $root), '/');
            $prefix = $root.'/';

            if (! str_starts_with($absolute, $prefix)) {
                continue;
            }

            $relative = ltrim(substr($absolute, strlen($prefix)), '/');

            if ($root === rtrim(str_replace('\\', '/', public_path('storage')), '/')) {
                return $relative;
            }

            return $relative;
        }

        return null;
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
