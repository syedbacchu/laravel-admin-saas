<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class TenantOfficeCreateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'branch_name' => trim((string) $this->input('branch_name')),
            'address' => trim((string) $this->input('address')),
        ]);
    }

    public function rules(): array
    {
        return [
            'branch_name' => ['required', 'string', 'max:150'],
            'opening_balance' => ['required', 'numeric'],
            'address' => ['required', 'string', 'max:255'],
            'status' => ['nullable', Rule::in([0, 1])],
        ];
    }
}

