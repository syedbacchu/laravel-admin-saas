<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class TenantDailyOfficeExpenseCreateRequest extends BaseFormRequest
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

        $this->merge([
            'date' => $this->normalizeNullableString('date'),
            'paid_to' => trim((string) $this->input('paid_to', '')),
            'category' => trim((string) $this->input('category', '')),
            'office_id' => $officeId,
            'amount' => $this->normalizeNullableNumeric('amount'),
            'remarks' => trim((string) $this->input('remarks', '')),
            'attachment' => $this->normalizeNullableString('attachment') ?: $this->normalizeNullableString('attachments'),
        ]);
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'paid_to' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'max:80'],
            'office_id' => ['required', 'integer', 'exists:tenant.offices,id'],
            'amount' => ['required', 'numeric'],
            'remarks' => ['nullable', 'string', 'max:255'],
            'attachment' => ['nullable', 'string', 'max:255'],
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
