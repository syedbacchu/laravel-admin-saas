<?php

namespace App\Http\Requests\Contact;

use App\Http\Requests\BaseFormRequest;
use App\Rules\PhoneNumberBD;

class ContactStoreRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => ['nullable', new PhoneNumberBD()],
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required',
        ];
    }
}
