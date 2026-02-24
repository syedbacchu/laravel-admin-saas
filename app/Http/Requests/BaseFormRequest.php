<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class BaseFormRequest extends FormRequest
{
    protected function failedValidation(Validator $validator)
    {
        // If request expects JSON (API)
        if ($this->expectsJson() || $this->is('api/*')) {
            throw new HttpResponseException(response()->json([
                'status' => 422,
                'success' => false,
                'message' => $validator->errors()->first() ?? __("Something went wrong with validation"),
                'error_message' => $validator->errors()->first() ?? 'Validation Failed',
                'data' => []
            ], 422));
        }

        // If it's a web request (normal form submission)
        parent::failedValidation($validator);
    }
}
