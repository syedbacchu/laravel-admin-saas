<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;

class TenantForgotPasswordRequest extends BaseFormRequest
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
            'resend' => ['nullable', 'boolean'],
        ];
    }
}

