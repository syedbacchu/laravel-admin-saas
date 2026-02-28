<?php

namespace App\Http\Requests\Tenant;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class TenantCreateRequest extends BaseFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'company_username' => strtolower(trim((string) $this->input('company_username'))),
            'company_name' => trim((string) $this->input('company_name')),
            'owner_name' => trim((string) $this->input('owner_name')),
            'owner_email' => $this->input('owner_email') ? strtolower(trim((string) $this->input('owner_email'))) : null,
            'owner_phone' => $this->input('owner_phone') ? trim((string) $this->input('owner_phone')) : null,
        ]);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:150'],
            'company_username' => [
                'required',
                'string',
                'min:3',
                'max:60',
                'regex:/^[a-z0-9_]+$/',
                Rule::notIn(config('tenancy.reserved_paths', [])),
                Rule::unique('tenants', 'company_username'),
                Rule::unique('users', 'username'),
            ],
            'owner_name' => ['required', 'string', 'max:120'],
            'owner_email' => ['nullable', 'email', 'max:190', Rule::unique('users', 'email')],
            'owner_phone' => ['nullable', 'string', 'max:30', Rule::unique('users', 'phone')],
            'owner_password' => ['required', 'string', 'min:8', 'max:120'],
        ];
    }
}
