<?php

namespace App\Http\Requests\Faq;

use Illuminate\Foundation\Http\FormRequest;

class FaqCategoryCreateRequest extends FormRequest
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
            'name'        => 'required|string|max:255',
            'image'       => 'nullable|string',
            'description' => 'nullable|string',
            'sort_order'  => 'nullable|integer',
            'status'      => 'nullable|boolean',
        ];
    }

}
