<?php

namespace App\Http\Requests\DatabaseBackup;

use App\Http\Requests\BaseFormRequest;

class DatabaseBackupCreateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'description.max' => __('Description cannot exceed 500 characters'),
        ];
    }
}