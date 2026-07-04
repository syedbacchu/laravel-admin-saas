<?php

namespace App\Http\Requests\Vehicle;

use App\Http\Requests\BaseFormRequest;
use App\Models\Language;
use Illuminate\Validation\Validator;

class RegistrationZoneCreateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'in:0,1'],
            'translations' => ['required', 'array'],
            'translations.*.name' => ['nullable', 'string', 'max:150'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $translations = (array) $this->input('translations', []);

            $allowedLanguageIds = Language::query()
                ->forInput()
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $defaultLanguage = Language::query()->where('is_default', 1)->first();
            if (!$defaultLanguage) {
                $validator->errors()->add('translations', __('Default language is missing'));
                return;
            }

            foreach (array_keys($translations) as $languageId) {
                if (!in_array((int) $languageId, $allowedLanguageIds, true)) {
                    $validator->errors()->add("translations.{$languageId}", __('Invalid language selected'));
                }
            }

            $defaultName = trim((string) data_get($translations, $defaultLanguage->id . '.name', ''));
            if ($defaultName === '') {
                $validator->errors()->add(
                    "translations.{$defaultLanguage->id}.name",
                    __('Name is required for default language')
                );
            }
        });
    }
}

