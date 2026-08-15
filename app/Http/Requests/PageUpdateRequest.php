<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PageUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:pages,slug,' . $this->route('id'),
            'heading' => 'nullable|string|max:255',
            'sub_heading' => 'nullable|string',
            'short_description' => 'nullable|string',
            'full_description' => 'nullable|string',
            'banner' => 'nullable|array',
            'banner.desktop' => 'nullable|string',
            'banner.mobile' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_keyword' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'meta_image' => 'nullable|string',
            'status' => 'boolean',
        ];
    }
}
