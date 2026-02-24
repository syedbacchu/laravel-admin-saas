<?php

namespace App\Http\Requests\Slider;

use App\Http\Requests\BaseFormRequest;

class SliderCreateRequest extends BaseFormRequest
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
        $rule = [
            'type' => 'required|in:1,2',
            'title' => 'nullable|max:255',
            'subtitle' => 'nullable|max:255',
            'offer' => 'nullable|max:255',
            'link' => 'nullable|max:255',
            'serial' => 'nullable|integer',
        ];
        if (empty($this->edit_id)) {
            $rule['photo'] =  'required';
        }

        return $rule;
    }
}
