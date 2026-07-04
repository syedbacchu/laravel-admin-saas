<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class TenantPayrollAdvanceSalaryCreateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $salaryMonth = $this->normalizeMonth($this->normalizeNullableStringByAliases(['salary_month', 'month', 'salaryMonth']));
        $advanceAmount = $this->normalizeNullableNumericByAliases(['advance_amount', 'amount']);
        $status = $this->normalizeStatus($this->normalizeNullableStringByAliases(['status']));
        $date = $this->normalizeDate($this->normalizeNullableStringByAliases(['date', 'advance_date'])) ?: now()->toDateString();

        $this->merge([
            'date' => $date,
            'employee_id' => $this->normalizeNullableIntegerByAliases(['employee_id', 'employee', 'employee_name']),
            'advance_amount' => $advanceAmount,
            'salary_month' => $salaryMonth,
            'status' => $status ?: 'due',
        ]);
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'employee_id' => ['required', 'integer', 'exists:tenant.employees,id'],
            'advance_amount' => ['required', 'numeric', 'min:0'],
            'salary_month' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'status' => ['required', Rule::in(['due', 'paid'])],
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

    protected function normalizeMonth(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $value) === 1) {
            return $value;
        }

        if (preg_match('/^\d{4}\/(0[1-9]|1[0-2])$/', $value) === 1) {
            return str_replace('/', '-', $value);
        }

        foreach (['Y-F', 'Y-M', 'F-Y', 'M-Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('Y-m');
            } catch (\Throwable) {
                // Try next format.
            }
        }

        try {
            return Carbon::parse('01 ' . $value)->format('Y-m');
        } catch (\Throwable) {
            return null;
        }
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
            'paid'=> 'paid',
            default => 'due',
        };
    }
}
