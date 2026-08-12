<?php

namespace App\Http\Requests\Subscriber;

use App\Http\Requests\BaseFormRequest;

class SubscriberSubscribeRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email|max:255',
        ];
    }
}
