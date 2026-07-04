<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class TenantSupplierCreateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $name = trim((string) $this->input('name', ''));
        $supplierName = trim((string) $this->input('supplier_name', ''));

        $this->merge([
            'name' => $name !== '' ? $name : $supplierName,
            'business_category' => trim((string) $this->input('business_category', '')),
            'mobile' => trim((string) $this->input('mobile', '')),
            'image' => $this->normalizeNullableString('image'),
            'address' => trim((string) $this->input('address', '')),
            'opening_balance' => $this->normalizeNullableNumeric('opening_balance'),
            'contact_person' => trim((string) $this->input('contact_person', '')),
        ]);
    }

    public function rules(): array
    {
        $id = (int) ($this->route('supplier') ?? $this->route('id') ?? $this->input('edit_id') ?? 0);

        return [
            'name' => ['required', 'string', 'max:150'],
            'business_category' => ['required', 'string', 'max:120'],
            'mobile' => ['required', 'string', 'max:30', Rule::unique('tenant.suppliers', 'mobile')->ignore($id)],
            'image' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'opening_balance' => ['required', 'numeric'],
            'contact_person' => ['required', 'string', 'max:120'],
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
