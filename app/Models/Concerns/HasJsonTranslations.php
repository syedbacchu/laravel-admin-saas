<?php

namespace App\Models\Concerns;

trait HasJsonTranslations
{
    public function translationFor(string $field, ?string $locale = null, ?string $fallback = 'en'): ?string
    {
        $locale = $locale ?: app()->getLocale();
        $translationsField = $field . '_translations';
        $translations = $this->{$translationsField} ?? [];

        if (is_string($translations)) {
            $translations = json_decode($translations, true) ?: [];
        }

        if (!is_array($translations)) {
            $translations = [];
        }

        if (isset($translations[$locale]) && trim((string) $translations[$locale]) !== '') {
            return (string) $translations[$locale];
        }

        if ($fallback && isset($translations[$fallback]) && trim((string) $translations[$fallback]) !== '') {
            return (string) $translations[$fallback];
        }

        return $this->{$field} ?? null;
    }
}
