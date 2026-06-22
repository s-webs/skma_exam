<?php

namespace App\Models\Concerns;

trait HasLocalizedNames
{
    public function localizedName(?string $locale = null): string
    {
        $locale = $this->normalizeNameLocale($locale ?? app()->getLocale());

        return match ($locale) {
            'kk' => $this->name_kk ?: $this->name_ru,
            'en' => $this->name_en ?: $this->name_ru,
            default => $this->name_ru,
        };
    }

    private function normalizeNameLocale(string $locale): string
    {
        return match ($locale) {
            'kz' => 'kk',
            default => $locale,
        };
    }
}
