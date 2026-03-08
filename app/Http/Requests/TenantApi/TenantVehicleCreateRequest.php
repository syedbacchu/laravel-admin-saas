<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class TenantVehicleCreateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'registration_no' => strtoupper(trim((string) $this->input('registration_no'))),
        ]);
    }

    public function rules(): array
    {
        $id = (int) ($this->edit_id ?: 0);

        return [
            'registration_no' => [
                'required',
                'string',
                'max:80',
                Rule::unique('tenant.vehicles', 'registration_no')->ignore($id),
            ],
            'vehicle_type' => ['nullable', 'string', 'max:80'],
            'brand' => ['nullable', 'string', 'max:80'],
            'model' => ['nullable', 'string', 'max:80'],
            'manufacturing_year' => ['nullable', 'integer', 'between:1900,2100'],
            'color' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in([0, 1])],
        ];
    }
}
