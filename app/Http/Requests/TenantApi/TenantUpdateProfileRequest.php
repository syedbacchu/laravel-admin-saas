<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;
use App\Rules\PhoneNumberBD;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TenantUpdateProfileRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = Auth::id();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => [
                'nullable',
                new PhoneNumberBD(),
                Rule::unique('users', 'phone')->ignore($userId),
            ],
            'image' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'language' => ['nullable', 'string', 'max:10'],
        ];
    }
}

