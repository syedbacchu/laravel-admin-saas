<?php

namespace App\Http\Requests\Billing;

use App\Http\Requests\BaseFormRequest;
use App\Models\Language;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class FeatureCreateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'key' => strtolower(trim((string) $this->input('key'))),
        ]);
    }

    public function rules(): array
    {
        $id = $this->edit_id;

        return [
            'key' => [
                'required',
                'string',
                'max:120',
                'regex:/^[a-z0-9._-]+$/',
                Rule::unique('features', 'key')->ignore($id),
            ],
            'value_type' => ['required', Rule::in(['boolean', 'integer', 'decimal', 'string', 'json'])],
            'is_active' => ['nullable', 'in:0,1'],
            'translations' => ['required', 'array'],
            'translations.*.name' => ['nullable', 'string', 'max:150'],
            'translations.*.description' => ['nullable', 'string'],
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

            $defaultLanguage = Language::query()
                ->where('is_default', 1)
                ->first();

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
                $validator->errors()->add("translations.{$defaultLanguage->id}.name", __('Name is required for default language'));
            }
        });
    }
}
