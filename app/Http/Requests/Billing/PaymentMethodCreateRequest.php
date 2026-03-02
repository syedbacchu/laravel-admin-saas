<?php

namespace App\Http\Requests\Billing;

use App\Http\Requests\BaseFormRequest;
use App\Models\Language;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PaymentMethodCreateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtolower(trim((string) $this->input('code'))),
        ]);
    }

    public function rules(): array
    {
        $id = $this->edit_id;

        return [
            'code' => [
                'required',
                'string',
                'max:80',
                'regex:/^[a-z0-9._-]+$/',
                Rule::unique('payment_methods', 'code')->ignore($id),
            ],
            'is_active' => ['nullable', 'in:0,1'],
            'sort_order' => ['nullable', 'integer', 'min:0'],

            'details.mobile_number' => ['nullable', 'string', 'max:60'],
            'details.account_number' => ['nullable', 'string', 'max:120'],
            'details.bank_name' => ['nullable', 'string', 'max:160'],
            'details.branch_name' => ['nullable', 'string', 'max:160'],

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
                $validator->errors()->add("translations.{$defaultLanguage->id}.name", __('Name is required for default language'));
            }

            $code = strtolower((string) $this->input('code'));
            if ($code === 'bank_payment') {
                $bankName = trim((string) $this->input('details.bank_name', ''));
                if ($bankName === '') {
                    $validator->errors()->add('details.bank_name', __('Bank name is required for bank payment method'));
                }
            }
        });
    }
}

