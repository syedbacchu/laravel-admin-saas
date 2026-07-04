<?php

namespace App\Http\Requests\TenantApi;

use App\Http\Requests\BaseFormRequest;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class TenantPayrollAttendanceCreateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $employeeId = $this->normalizeNullableIntegerByAliases(['employee_id', 'employee', 'employee_name']);
        $month = $this->normalizeMonth($this->normalizeNullableStringByAliases(['month', 'attendance_month', 'salary_month']));
        $date = $this->normalizeDate($this->normalizeNullableStringByAliases(['date', 'attendance_date'])) ?: now()->toDateString();

        $this->merge([
            'date' => $date,
            'employee_id' => $employeeId,
            'working_day' => $this->normalizeNullableIntegerByAliases(['working_day', 'working_days']),
            'month' => $month,
            'status' => $this->normalizeNullableIntegerByAliases(['status']),
        ]);
    }

    public function rules(): array
    {
        $id = (int) ($this->route('id') ?? $this->input('edit_id') ?? 0);
        $employeeId = (int) ($this->input('employee_id') ?? 0);

        return [
            'date' => ['required', 'date'],
            'employee_id' => ['required', 'integer', 'exists:tenant.employees,id'],
            'working_day' => ['required', 'integer', 'min:0', 'max:31'],
            'month' => [
                'required',
                'regex:/^\d{4}-(0[1-9]|1[0-2])$/',
                Rule::unique('tenant.payroll_attendances', 'month')
                    ->where(function ($query) use ($employeeId) {
                        return $query->where('employee_id', $employeeId);
                    })
                    ->ignore($id),
            ],
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
