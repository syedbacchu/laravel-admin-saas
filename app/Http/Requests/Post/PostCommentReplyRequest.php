<?php

namespace App\Http\Requests\Post;

use App\Http\Requests\BaseFormRequest;

class PostCommentReplyRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'comment' => ['required', 'string', 'max:5000'],
        ];
    }
}
