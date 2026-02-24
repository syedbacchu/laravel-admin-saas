<?php

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TagCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('tag') ?? $this->route('id') ?? $this->input('edit_id');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('tags', 'name')->ignore($id)],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('tags', 'slug')->ignore($id)],
        ];
    }
}
