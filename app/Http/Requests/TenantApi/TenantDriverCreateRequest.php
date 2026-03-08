<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class TenantDriverCreateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $phone = trim((string) $this->input('phone', ''));
        $licenseNo = strtoupper(trim((string) $this->input('license_no', '')));
        $nidNo = trim((string) $this->input('nid_no', ''));

        $this->merge([
            'phone' => $phone !== '' ? $phone : null,
            'license_no' => $licenseNo !== '' ? $licenseNo : null,
            'nid_no' => $nidNo !== '' ? $nidNo : null,
        ]);
    }

    public function rules(): array
    {
        $id = (int) ($this->edit_id ?: 0);

        return [
            'vehicle_id' => ['nullable', 'integer', 'exists:tenant.vehicles,id'],
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'license_no' => [
                'nullable',
                'string',
                'max:80',
                Rule::unique('tenant.drivers', 'license_no')->ignore($id),
            ],
            'nid_no' => ['nullable', 'string', 'max:40'],
            'joining_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in([0, 1])],
        ];
    }
}
