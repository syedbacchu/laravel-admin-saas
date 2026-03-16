<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;
use App\Rules\PhoneNumberBD;
use App\Rules\UserName;

class TenantStaffCreateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => $this->input('email') ? strtolower(trim((string) $this->input('email'))) : null,
            'username' => $this->input('username') ? strtolower(trim((string) $this->input('username'))) : null,
            'status' => $this->input('status', 1),
            'enable_login' => $this->input('enable_login', 1),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', new PhoneNumberBD()],
            'username' => ['nullable', new UserName()],
            'password' => ['required', 'string', 'min:8'],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'status' => ['nullable', 'in:0,1'],
            'enable_login' => ['nullable', 'in:0,1'],
        ];
    }
}
