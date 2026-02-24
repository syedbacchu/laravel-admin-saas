<?php

namespace App\Http\Requests\Settings;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Foundation\Http\FormRequest;

class FieldCreateRequest extends BaseFormRequest
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
        return [
            'group' => 'required|string|max:50',
            'label' => 'required|string|max:100',
            'slug'  => 'required|string|unique:settings_fields,slug',
            'type'  => 'required|in:text,password,select,file,number,checkbox,radio,textarea',
            'options' => 'nullable|string',
            'validation_rules' => 'nullable|string',
        ];
    }
}
