<?php

namespace App\Http\Requests\Tenant;

use App\Http\Requests\BaseFormRequest;
use App\Models\Tenant;
use Illuminate\Validation\Rule;

class TenantUpdateRequest extends BaseFormRequest
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
        $tenantId = $this->route('id');
        $tenant = Tenant::find($tenantId);
        $ownerUserId = $tenant?->owner_user_id;

        return [
            'company_name' => ['required', 'string', 'max:150'],
            'company_username' => [
                'required',
                'string',
                'min:3',
                'max:60',
                'regex:/^[a-z0-9_]+$/',
                Rule::notIn(config('tenancy.reserved_paths', [])),
                Rule::unique('tenants', 'company_username')->ignore($tenantId),
                Rule::unique('users', 'username')->ignore($ownerUserId),
            ],
            'owner_name' => ['required', 'string', 'max:120'],
            'owner_email' => ['nullable', 'email', 'max:190', Rule::unique('users', 'email')->ignore($ownerUserId)],
            'owner_phone' => ['nullable', 'string', 'max:30', Rule::unique('users', 'phone')->ignore($ownerUserId)],
            'owner_password' => ['nullable', 'string', 'min:8', 'max:120'],
        ];
    }
}
