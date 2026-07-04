<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class TenantAreaCreateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
        ]);
    }

    public function rules(): array
    {
        $id = (int) ($this->route('area') ?? $this->route('id') ?? $this->input('edit_id') ?? 0);

        return [
            'name' => ['required', 'string', 'max:150', Rule::unique('areas', 'name')->ignore($id)],
            'status' => ['nullable', 'in:0,1'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Area name is required'),
            'name.string' => __('Area name must be a string'),
            'name.max' => __('Area name must not exceed 150 characters'),
            'name.unique' => __('An area with this name already exists'),
            'status.in' => __('Status must be either active or inactive'),
        ];
    }
}
