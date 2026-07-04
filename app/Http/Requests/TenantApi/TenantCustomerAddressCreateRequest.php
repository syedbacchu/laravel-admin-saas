<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;

class TenantCustomerAddressCreateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->input('name') ? trim((string) $this->input('name')) : null,
            'address' => trim((string) $this->input('address')),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:150'],
            'address' => ['required', 'string', 'max:500'],
            'status' => ['nullable', 'in:0,1'],
        ];
    }
}
