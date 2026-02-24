<?php

namespace App\Http\Requests\Auth;

use App\Enums\VerificationCodeTypeEnum;
use App\Http\Requests\BaseFormRequest;
use App\Rules\StrongPassword;
use Illuminate\Validation\Rule;

class ResetPasswordRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password_token' => ['required'],
            'otp' => ['required'],
            'password' => ['required', new StrongPassword()],
            'confirm_password' => 'required|same:password',
        ];
    }

    public function messages(): array
    {
        return [
            'password_token.required' => __('Password token is required.'),
            'otp.required' => __('OTP is required.'),
            'password.required' => __('Password is required.'),
            'confirm_password.required' => __('Confirm Password is required.'),
        ];
    }

}
