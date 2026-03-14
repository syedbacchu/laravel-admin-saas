<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ModelTranslationResolver
{
    public static function getTranslation(Model $model, string $relation, int $languageId): ?Model
    {
        if ($languageId <= 0 || !method_exists($model, $relation)) {
            return null;
        }

        if ($model->relationLoaded($relation)) {
            $loaded = $model->getRelation($relation);

            if ($loaded instanceof Collection) {
                $item = $loaded->firstWhere('language_id', $languageId);
                return $item instanceof Model ? $item : null;
            }

            if ($loaded instanceof Model && (int) ($loaded->language_id ?? 0) === $languageId) {
                return $loaded;
            }

            return null;
        }

        $query = $model->{$relation}();
        return $query->where('language_id', $languageId)->first();
    }

    public static function getValue(
        Model $model,
        string $relation,
        int $languageId,
        string $translatedField,
        string $defaultField,
        mixed $fallback = null
    ): mixed {
        $translation = self::getTranslation($model, $relation, $languageId);
        $translatedValue = data_get($translation, $translatedField);

        if ($translatedValue !== null && $translatedValue !== '') {
            return $translatedValue;
        }

        $defaultValue = data_get($model, $defaultField);
        if ($defaultValue !== null && $defaultValue !== '') {
            return $defaultValue;
        }

        return $fallback;
    }
}

