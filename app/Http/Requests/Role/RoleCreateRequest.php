<?php

namespace App\Http\Requests\Role;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class RoleCreateRequest extends BaseFormRequest
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
            'name' => 'required|string|max:255',
            'slug' => ['required','string','max:255', Rule::unique('roles','slug')->ignore($this->edit_id)],
            'permissions'   => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,id',

        ];
    }
}
