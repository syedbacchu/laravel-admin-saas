<?php

namespace App\Http\Requests\CustomField;

use Illuminate\Foundation\Http\FormRequest;

class CustomFieldCreateRequest extends FormRequest
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
        $rule = [
            'module' => 'required|string',
            'label' => 'required|string|max:255',
            'name' => 'nullable|string|max:255',
            'type' => 'required|string',
            'options' => 'nullable|string',
            'show_in' => 'required|array|min:1',
            'show_in.*' => 'in:create,update,api',
            'is_required' => 'nullable|boolean',
            'default_value' => 'nullable|string',
            'validation_rules' => 'nullable|string',
            'status' => 'required|boolean',
            'sort_order' => 'nullable|integer',
            'edit_id' => 'nullable|integer',
        ];
        if (in_array($this->type, ['select', 'radio', 'checkbox'])) {
            $rule['options'] = 'required|string';
        }

        return $rule;
    }
}
