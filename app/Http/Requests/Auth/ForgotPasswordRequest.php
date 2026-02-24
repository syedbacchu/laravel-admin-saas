<?php

namespace App\Http\Requests\Auth;

use App\Enums\VerificationCodeTypeEnum;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class ForgotPasswordRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
//            'type' => [
//                'required',
//                Rule::in(array_column(VerificationCodeTypeEnum::cases(), 'value')),
//            ],
            'email' => ['required', 'string'],
            'resend' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => __('Type is required.'),
            'type.in' => __('Type is only email, username, or phone number.'),
            'email.required' => __('Email / Phone / Username is required.'),
        ];
    }

}
