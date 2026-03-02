<?php

namespace App\Http\Requests\Language;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class LanguageCreateRequest extends BaseFormRequest
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
            'name' => ['required', 'string', 'max:120'],
            'native_name' => ['nullable', 'string', 'max:120'],
            'code' => [
                'required',
                'string',
                'max:10',
                'regex:/^[a-z]{2,5}$/',
                Rule::unique('languages', 'code')->ignore($id),
            ],
            'direction' => ['nullable', Rule::in(['ltr', 'rtl'])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'in:0,1'],
        ];
    }
}

