<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class TenantFundTransferCreateRequest extends BaseFormRequest
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
            $branchName = $this->input('branch_name', null);
            if ($branchName !== null && $branchName !== '' && is_numeric($branchName)) {
                $officeId = (int) $branchName;
            }
        }

        $this->merge([
            'date' => $this->normalizeNullableString('date'),
            'office_id' => $officeId,
            'person_name' => trim((string) $this->input('person_name', $this->input('person', ''))),
            'cash_type' => trim((string) $this->input('cash_type', $this->input('payment_type', ''))),
            'amount' => $this->normalizeNullableNumeric('amount'),
            'bank_name' => trim((string) $this->input('bank_name', $this->input('bank', ''))),
            'purpose' => trim((string) $this->input('purpose', $this->input('remarks', $this->input('note', '')))),
        ]);
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'office_id' => ['required', 'integer', 'exists:tenant.offices,id'],
            'person_name' => ['required', 'string', 'max:150'],
            'cash_type' => ['required', 'string', 'max:80'],
            'amount' => ['required', 'numeric'],
            'bank_name' => ['required', 'string', 'max:150'],
            'purpose' => ['required', 'string', 'max:255'],
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
