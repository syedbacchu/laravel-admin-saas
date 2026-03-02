<?php

namespace App\Http\Requests\Billing;

use App\Http\Requests\BaseFormRequest;
use App\Models\Language;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PlanCreateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $slug = $this->input('slug');
        if (!$slug) {
            $translations = (array) $this->input('translations', []);
            $defaultLanguage = Language::query()->where('is_default', 1)->first();
            $defaultName = $defaultLanguage ? data_get($translations, $defaultLanguage->id . '.name') : null;
            $slug = Str::slug((string) $defaultName);
        }

        $this->merge([
            'slug' => strtolower(trim((string) $slug)),
        ]);
    }

    public function rules(): array
    {
        $id = $this->edit_id;

        return [
            'translations' => ['required', 'array'],
            'translations.*.name' => ['nullable', 'string', 'max:150'],
            'translations.*.subtitle' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:180', Rule::unique('plans', 'slug')->ignore($id)],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'in:0,1'],

            'feature_assign' => ['nullable', 'array'],
            'feature_assign.*' => ['nullable', 'in:1'],
            'feature_values' => ['nullable', 'array'],
            'feature_values.*.value_bool' => ['nullable', 'in:0,1'],
            'feature_values.*.value_int' => ['nullable', 'integer'],
            'feature_values.*.value_decimal' => ['nullable', 'numeric'],
            'feature_values.*.value_text' => ['nullable', 'string'],
            'feature_values.*.value_json' => ['nullable', 'string'],

            'pricings' => ['nullable', 'array'],
            'pricings.*.term_months' => ['required_with:pricings.*.base_amount', 'integer', 'min:1', 'max:36'],
            'pricings.*.base_amount' => ['nullable', 'numeric', 'min:0'],
            'pricings.*.discount_type' => ['nullable', Rule::in(['percent', 'fixed'])],
            'pricings.*.discount_value' => ['nullable', 'numeric', 'min:0'],
            'pricings.*.currency' => ['nullable', 'string', 'max:10'],
            'pricings.*.is_active' => ['nullable', 'in:0,1'],
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
