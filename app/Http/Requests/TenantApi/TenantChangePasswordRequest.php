<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;
use App\Rules\StrongPassword;

class TenantChangePasswordRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'new_password' => ['required', new StrongPassword()],
            'confirm_password' => ['required', 'same:new_password'],
        ];
    }
}

