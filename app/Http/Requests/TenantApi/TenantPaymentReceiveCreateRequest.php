<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class TenantPaymentReceiveCreateRequest extends BaseFormRequest
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

        $customerId = $this->normalizeNullableInteger('customer_id');
        if ($customerId === null) {
            $customerId = $this->normalizeNullableInteger('customer_name');
        }

        $this->merge([
            'date' => $this->normalizeNullableString('date'),
            'customer_id' => $customerId,
            'office_id' => $officeId,
            'bill_ref' => trim((string) $this->input('bill_ref', '')),
            'amount' => $this->normalizeNullableNumeric('amount'),
            'cash_type' => trim((string) $this->input('cash_type', '')),
            'note' => trim((string) $this->input('note', $this->input('remarks', ''))),
            'created_by' => trim((string) $this->input('created_by', $this->input('created_by_name', ''))),
            'bill_document' => $this->normalizeNullableString('bill_document') ?: $this->normalizeNullableString('bill_documents'),
        ]);
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'customer_id' => ['required', 'integer', 'exists:tenant.customers,id'],
            'office_id' => ['required', 'integer', 'exists:tenant.offices,id'],
            'bill_ref' => ['required', 'string', 'max:120'],
            'amount' => ['required', 'numeric', 'min:0'],
            'cash_type' => ['required', 'string', 'max:80'],
            'note' => ['required', 'string', 'max:255'],
            'created_by' => ['required', 'string', 'max:150'],
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
