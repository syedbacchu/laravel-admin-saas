<?php

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostCategoryCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('post_category') ?? $this->route('id') ?? $this->input('edit_id');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('post_categories', 'slug')->ignore($id)],
            'parent_id' => ['nullable', 'exists:post_categories,id'],
            'image' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'serial' => ['nullable', 'integer'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
