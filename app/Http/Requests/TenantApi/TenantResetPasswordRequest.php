<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;
use App\Rules\StrongPassword;

class TenantResetPasswordRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'login' => strtolower(trim((string) $this->input('login'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'login' => ['required', 'string', 'max:180'],
            'otp' => ['required', 'string', 'max:12'],
            'password' => ['required', new StrongPassword()],
            'confirm_password' => ['required', 'same:password'],
        ];
    }
}

