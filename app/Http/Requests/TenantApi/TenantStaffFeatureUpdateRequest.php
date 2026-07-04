<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;

class TenantStaffFeatureUpdateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'features' => $this->input('features', []),
        ]);
    }

    public function rules(): array
    {
        return [
            'features' => ['required', 'array'],
            'features.*' => ['string'], // array of feature keys
        ];
    }

    public function messages(): array
    {
        return [
            'features.required' => __('Features are required'),
            'features.array' => __('Features must be an array'),
            'features.*.string' => __('Each feature must be a string'),
        ];
    }
}