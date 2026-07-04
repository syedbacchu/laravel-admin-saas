<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class TenantPayrollLoanCreateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $loanAmount = $this->normalizeNullableNumericByAliases(['loan_amount', 'amount']);
        $afterAdjustment = $this->normalizeNullableNumericByAliases(['after_adjustment_amount', 'after_adjustment', 'adjustment_amount']);
        $status = $this->normalizeStatus($this->normalizeNullableStringByAliases(['status']));
        $loanDate = $this->normalizeDate($this->normalizeNullableStringByAliases(['loan_date', 'date'])) ?: now()->toDateString();

        $this->merge([
            'loan_date' => $loanDate,
            'employee_id' => $this->normalizeNullableIntegerByAliases(['employee_id', 'employee', 'employee_name']),
            'loan_amount' => $loanAmount,
            'monthly_deduction' => $this->normalizeNullableNumericByAliases(['monthly_deduction', 'deduction']),
            'after_adjustment_amount' => $afterAdjustment ?? $loanAmount,
            'status' => $status ?: 'pending',
        ]);
    }

    public function rules(): array
    {
        return [
            'loan_date' => ['required', 'date'],
            'employee_id' => ['required', 'integer', 'exists:tenant.employees,id'],
            'loan_amount' => ['required', 'numeric', 'min:0'],
            'monthly_deduction' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['pending', 'completed'])],
        ];
    }

    protected function normalizeNullableStringByAliases(array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = trim((string) $this->input($key, ''));
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    protected function normalizeNullableIntegerByAliases(array $keys): ?int
    {
        foreach ($keys as $key) {
            $value = $this->input($key, null);
            if ($value === null || $value === '') {
                continue;
            }

            return (int) $value;
        }

        return null;
    }

    protected function normalizeNullableNumericByAliases(array $keys): float|int|null
    {
        foreach ($keys as $key) {
            $value = $this->input($key, null);
            if ($value === null || $value === '') {
                continue;
            }

            return is_numeric($value) ? $value + 0 : null;
        }

        return null;
    }

    protected function normalizeDate(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1) {
            return $value;
        }

        foreach (['d-m-Y', 'd/m/Y', 'Y/m/d', 'm/d/Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('Y-m-d');
            } catch (\Throwable) {
                // Try next format.
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    protected function normalizeStatus(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = strtolower(trim($value));
        if ($normalized === '') {
            return null;
        }

        return match ($normalized) {
            'complete', 'completed', 'done' => 'completed',
            default => 'pending',
        };
    }
}
