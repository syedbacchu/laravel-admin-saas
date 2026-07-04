<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class TenantVendorCreateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $name = trim((string) $this->input('name', ''));
        $vendorName = trim((string) $this->input('vendor_name', ''));

        $this->merge([
            'name' => $name !== '' ? $name : $vendorName,
            'mobile' => trim((string) $this->input('mobile', '')),
            'date' => $this->normalizeNullableString('date'),
            'vehicle_category_id' => $this->normalizeNullableInteger('vehicle_category_id'),
            'work_area' => $this->normalizeNullableString('work_area'),
            'opening_balance' => $this->normalizeNullableNumeric('opening_balance'),
        ]);
    }

    public function rules(): array
    {
        $id = (int) ($this->route('vendor') ?? $this->route('id') ?? $this->input('edit_id') ?? 0);

        return [
            'name' => ['required', 'string', 'max:150'],
            'mobile' => ['required', 'string', 'max:30', Rule::unique('tenant.vendors', 'mobile')->ignore($id)],
            'date' => ['required', 'date'],
            'vehicle_category_id' => ['required', 'integer', 'exists:vehicle_categories,id'],
            'work_area' => ['nullable', 'string', 'max:255'],
            'opening_balance' => ['nullable', 'numeric'],
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
