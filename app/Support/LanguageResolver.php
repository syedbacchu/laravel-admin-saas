<?php

namespace App\Support;

use App\Models\Language;
use Illuminate\Http\Request;

class LanguageResolver
{
    public static function resolveFromRequest(Request $request, string $header = 'lang', string $fallbackCode = 'en'): array
    {
        return self::resolve((string) $request->header($header, $fallbackCode), $fallbackCode);
    }

    public static function resolve(?string $requestedCode, string $fallbackCode = 'en'): array
    {
        $normalizedCode = strtolower(trim((string) $requestedCode));
        $fallbackCode = strtolower(trim($fallbackCode)) ?: 'en';

        if ($normalizedCode === '') {
            $normalizedCode = $fallbackCode;
        }

        $language = Language::query()
            ->forInput()
            ->where('code', $normalizedCode)
            ->first(['id', 'code']);

        if ($language) {
            return [
                'id' => (int) $language->id,
                'code' => (string) $language->code,
            ];
        }

        $fallbackLanguage = Language::query()
            ->forInput()
            ->where('code', $fallbackCode)
            ->first(['id', 'code']);

        if ($fallbackLanguage) {
            return [
                'id' => (int) $fallbackLanguage->id,
                'code' => (string) $fallbackLanguage->code,
            ];
        }

        $firstLanguage = Language::query()
            ->forInput()
            ->first(['id', 'code']);

        if ($firstLanguage) {
            return [
                'id' => (int) $firstLanguage->id,
                'code' => (string) $firstLanguage->code,
            ];
        }

        return [
            'id' => 0,
            'code' => $fallbackCode,
        ];
    }
}

