<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseFormRequest;
use App\Rules\PhoneNumberBD;
use App\Rules\UserName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserCreateRequest extends BaseFormRequest
{
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        $userId = $this->edit_id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9.\s]+$/',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],

            'phone' => [
                'required',
                new PhoneNumberBD,
                Rule::unique('users', 'phone')->ignore($userId),
            ],

            'role_id' => 'nullable|exists:roles,id',

            'username' => [
//                Rule::requiredIf(!$userId),
                'nullable',
                new UserName,
                Rule::unique('users', 'username')->ignore($userId),
            ],

            'password' => [
                Rule::requiredIf(!$userId),
                'nullable',
                'string',
                'min:8',
            ],
        ];
    }
}
