<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class TenantEmployeeCreateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $name = trim((string) $this->input('name', ''));
        $fullName = trim((string) $this->input('full_name', ''));

        $this->merge([
            'name' => $name !== '' ? $name : $fullName,
            'email' => $this->input('email') ? strtolower(trim((string) $this->input('email'))) : null,
            'mobile' => trim((string) $this->input('mobile', '')),
            'gender' => trim((string) $this->input('gender', '')),
            'blood_group' => trim((string) $this->input('blood_group', '')),
            'birth_date' => $this->normalizeNullableString('birth_date'),
            'join_date' => $this->normalizeNullableString('join_date'),
            'nid' => trim((string) ($this->input('nid', $this->input('nid_no', '')))),
            'designation' => trim((string) $this->input('designation', '')),
            'address' => trim((string) $this->input('address', '')),
            'basic_salary' => $this->normalizeNullableNumeric('basic_salary'),
            'house_rent' => $this->normalizeNullableNumeric('house_rent'),
            'medical' => $this->normalizeNullableNumeric('medical'),
            'allowance' => $this->normalizeNullableNumeric('allowance'),
            'extra_allowance' => $this->normalizeNullableNumeric('extra_allowance'),
            'conveyance' => $this->normalizeNullableNumeric('conveyance'),
            'gross_salary' => $this->normalizeNullableNumeric('gross_salary'),
            'image' => $this->normalizeNullableString('image'),
        ]);
    }

    public function rules(): array
    {
        $id = (int) ($this->route('employee') ?? $this->route('id') ?? $this->input('edit_id') ?? 0);

        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:180'],
            'mobile' => ['required', 'string', 'max:30', Rule::unique('tenant.employees', 'mobile')->where(function ($query) {
                return $query->where('employee_type', 'employee');
            })->ignore($id)],
            'gender' => ['nullable', 'string', 'max:20'],
            'blood_group' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date'],
            'join_date' => ['nullable', 'date'],
            'nid' => ['nullable', 'string', 'max:60'],
            'designation' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'basic_salary' => ['nullable', 'numeric'],
            'house_rent' => ['nullable', 'numeric'],
            'medical' => ['nullable', 'numeric'],
            'allowance' => ['nullable', 'numeric'],
            'extra_allowance' => ['nullable', 'numeric'],
            'conveyance' => ['nullable', 'numeric'],
            'gross_salary' => ['nullable', 'numeric'],
            'image' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in([0, 1])],
        ];
    }

    protected function normalizeNullableString(string $key): ?string
    {
        $value = trim((string) $this->input($key, ''));
        return $value !== '' ? $value : null;
    }

    protected function normalizeNullableNumeric(string $key): float|int|null
    {
        $value = $this->input($key, null);
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? $value + 0 : null;
    }
}
