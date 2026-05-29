<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;

trait HasStorageImageUrl
{
    protected function storageImageUrl(?string $path, string $directory): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $relative = str_contains($path, '/') ? $path : "{$directory}/{$path}";

        if (! Storage::disk('public')->exists($relative) && ! str_contains($path, '/')) {
            $fallback = $directory === 'answers' ? "questions/{$path}" : "answers/{$path}";
            if (Storage::disk('public')->exists($fallback)) {
                $relative = $fallback;
            }
        }

        return Storage::disk('public')->url($relative);
    }
}
