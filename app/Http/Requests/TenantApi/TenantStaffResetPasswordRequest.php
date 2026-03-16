<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;

class TenantStaffResetPasswordRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'string', 'min:8'],
        ];
    }
}

