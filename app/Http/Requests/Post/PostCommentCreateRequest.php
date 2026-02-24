<?php

namespace App\Http\Requests\Post;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class PostCommentCreateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isGuest = !auth('api')->check() && !auth()->check();

        return [
            'name' => [Rule::requiredIf($isGuest), 'nullable', 'string', 'max:120'],
            'email' => [Rule::requiredIf($isGuest), 'nullable', 'email', 'max:120'],
            'website' => ['nullable', 'url', 'max:255'],
            'comment' => ['required', 'string', 'max:5000'],
            'parent_id' => ['nullable', 'integer', 'exists:post_comments,id'],
        ];
    }
}
