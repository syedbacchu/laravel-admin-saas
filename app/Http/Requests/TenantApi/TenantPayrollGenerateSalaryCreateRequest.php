<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class TenantPayrollGenerateSalaryCreateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $month = $this->normalizeMonth($this->normalizeNullableStringByAliases(['month', 'salary_month']));
        $generateDate = $this->normalizeDate($this->normalizeNullableStringByAliases(['generate_date', 'date'])) ?: now()->toDateString();

        $this->merge([
            'month' => $month,
            'generate_date' => $generateDate,
            'status' => $this->normalizeNullableIntegerByAliases(['status']) ?? 1,
        ]);
    }

    public function rules(): array
    {
        $id = (int) ($this->route('id') ?? $this->input('edit_id') ?? 0);

        return [
            'month' => [
                'required',
                'regex:/^\d{4}-(0[1-9]|1[0-2])$/',
                Rule::unique('tenant.payroll_generated_salaries', 'month')->ignore($id),
            ],
            'generate_date' => ['required', 'date'],
            'status' => ['nullable', Rule::in([0, 1])],
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
}
