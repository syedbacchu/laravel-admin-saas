<?php

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('post') ?? $this->route('id') ?? $this->input('edit_id');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('posts', 'slug')->ignore($id)],
            'excerpt' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'post_type' => ['nullable', 'string', 'max:50'],
            'thumbnail_img' => ['nullable', 'string'],
            'featured_img' => ['nullable', 'string'],
            'visibility' => ['nullable', 'boolean'],
            'is_comment_allow' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'featured_order' => ['nullable', 'integer'],
            'status' => ['required', Rule::in(['draft', 'published', 'scheduled'])],
            'published_at' => ['nullable', 'date'],
            'serial' => ['nullable', 'integer'],
            'event_date' => ['nullable', 'date'],
            'event_end_date' => ['nullable', 'date', 'after_or_equal:event_date'],
            'venue' => ['nullable', 'string'],
            'video_url' => ['nullable', 'string'],
            'photos' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string'],
            'meta_keywords' => ['nullable', 'string'],
            'meta_description' => ['nullable', 'string'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:post_categories,id'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
        ];
    }
}
