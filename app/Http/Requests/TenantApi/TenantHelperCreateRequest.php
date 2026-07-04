<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class TenantHelperCreateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'mobile' => trim((string) $this->input('mobile')),
            'image' => $this->normalizeNullableString('image'),
            'address' => trim((string) $this->input('address')),
            'vehicle_category_id' => $this->normalizeNullableInteger('vehicle_category_id'),
            'basic_salary' => $this->normalizeNullableNumeric('basic_salary'),
            'house_rent' => $this->normalizeNullableNumeric('house_rent'),
            'medical' => $this->normalizeNullableNumeric('medical'),
            'allowance' => $this->normalizeNullableNumeric('allowance'),
            'extra_allowance' => $this->normalizeNullableNumeric('extra_allowance'),
            'conveyance' => $this->normalizeNullableNumeric('conveyance'),
            'gross_salary' => $this->normalizeNullableNumeric('gross_salary'),
        ]);
    }

    public function rules(): array
    {
        $id = (int) ($this->route('helpers') ?? $this->route('id') ?? $this->input('edit_id') ?? 0);
        return [
            'name' => ['required', 'string', 'max:120'],
            'mobile' => ['required', 'string', 'max:30', Rule::unique('tenant.employees', 'mobile')->where(function ($query) {
                return $query->where('employee_type', 'helper');
            })->ignore($id)],
            'image' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'vehicle_category_id' => ['required', 'integer', 'exists:vehicle_categories,id'],
            'basic_salary' => ['nullable', 'numeric'],
            'house_rent' => ['nullable', 'numeric'],
            'medical' => ['nullable', 'numeric'],
            'allowance' => ['nullable', 'numeric'],
            'extra_allowance' => ['nullable', 'numeric'],
            'conveyance' => ['nullable', 'numeric'],
            'gross_salary' => ['nullable', 'numeric'],
            'status' => ['nullable', Rule::in([0, 1])],
        ];
    }

    protected function normalizeNullableString(string $key): ?string
    {
        $value = trim((string) $this->input($key, ''));
        return $value !== '' ? $value : null;
    }

    protected function normalizeNullableInteger(string $key): ?int
    {
        $value = $this->input($key, null);
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
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
