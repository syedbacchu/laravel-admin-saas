<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class TenantVendorPaymentCreateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $officeId = $this->normalizeNullableInteger('office_id');
        if ($officeId === null) {
            $officeId = $this->normalizeNullableInteger('branch_id');
        }
        if ($officeId === null) {
            $officeId = $this->normalizeNullableInteger('branch_name');
        }

        $vendorId = $this->normalizeNullableInteger('vendor_id');
        if ($vendorId === null) {
            $vendorId = $this->normalizeNullableInteger('vendor_name');
        }

        $this->merge([
            'date' => $this->normalizeNullableString('date'),
            'vendor_id' => $vendorId,
            'office_id' => $officeId,
            'bill_ref' => trim((string) $this->input('bill_ref', '')),
            'amount' => $this->normalizeNullableNumeric('amount'),
            'payment_method' => trim((string) $this->input('payment_method', $this->input('cash_type', ''))),
            'note' => $this->normalizeNullableString('note'),
            'bill_document' => $this->normalizeNullableString('bill_document') ?: $this->normalizeNullableString('bill_documents'),
        ]);
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'vendor_id' => ['required', 'integer', 'exists:tenant.vendors,id'],
            'office_id' => ['required', 'integer', 'exists:tenant.offices,id'],
            'bill_ref' => ['required', 'string', 'max:120'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'string', 'max:80'],
            'note' => ['nullable', 'string', 'max:255'],
            'bill_document' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in([0, 1])],
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
