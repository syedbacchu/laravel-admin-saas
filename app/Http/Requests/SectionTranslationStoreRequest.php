<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SectionTranslationStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page_section_id' => 'required|exists:page_sections,id',
            'language_id' => 'required|exists:languages,id',
            'data' => 'required|array',
        ];
    }
}
