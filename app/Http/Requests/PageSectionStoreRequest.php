<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PageSectionStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page_id' => 'required|exists:pages,id',
            'component_id' => 'required|exists:components,id',
            'sort_order' => 'nullable|integer',
            'is_visible' => 'boolean',
        ];
    }
}
