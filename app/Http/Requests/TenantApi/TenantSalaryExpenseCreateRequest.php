<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class TenantSalaryExpenseCreateRequest extends BaseFormRequest
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

        $paidToUserId = $this->normalizeNullableInteger('paid_to_user_id');
        if ($paidToUserId === null) {
            $paidToUserId = $this->normalizeNullableInteger('paid_to_id');
        }
        if ($paidToUserId === null) {
            $paidToUserId = $this->normalizeNullableInteger('employee_id');
        }

        $category = trim((string) $this->input('category', ''));
        if ($category === '') {
            $category = 'salary';
        }

        $this->merge([
            'date' => $this->normalizeNullableString('date'),
            'paid_to_user_id' => $paidToUserId,
            'category' => $category,
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
            'salary_month' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'paid_to_user_id' => [
                'required',
                'integer',
                'exists:tenant.employees,id',
                function ($attribute, $value, $fail) {
                    // Validate employee is active
                    $employee = \App\Models\TenantAllEmployee::query()->find($value);
                    if (!$employee || $employee->status !== 1) {
                        $fail('The selected employee is not active or does not exist.');
                    }
                },
            ],
            'category' => ['required', 'string', 'max:80'],
            'office_id' => ['required', 'integer', 'exists:tenant.offices,id'],
            'amount' => [
                'required',
                'numeric',
                'min:0',
            ],
            'remarks' => ['nullable', 'string', 'max:255'],
            'attachment' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in([0, 1])],
        ];
    }

    public function withValidator($validator)
    {
        // Validation moved to service layer
        // No form request validation needed for amount checking
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
