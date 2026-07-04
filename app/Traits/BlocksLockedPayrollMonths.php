<?php

namespace App\Traits;

use App\Models\TenantPayrollGeneratedSalary;

trait BlocksLockedPayrollMonths
{
    protected function ensurePayrollMonthsAreEditable(array $months, string $resourceLabel): ?array
    {
        $months = array_values(array_unique(array_filter(array_map(
            fn ($month) => $this->normalizePayrollMonth($month),
            $months
        ))));

        foreach ($months as $month) {
            if ($this->isPayrollMonthLocked($month)) {
                return $this->sendResponse(
                    false,
                    __('Salary has already been generated for :month. :resource cannot be modified for this month.', [
                        'month' => $month,
                        'resource' => $resourceLabel,
                    ]),
                    [],
                    422
                );
            }
        }

        return null;
    }

    protected function isPayrollMonthLocked(string $month): bool
    {
        $month = $this->normalizePayrollMonth($month);

        if ($month === null) {
            return false;
        }

        // Check if next month's salary has been generated
        // If next month exists, current month is locked (dues carried forward)
        $nextMonth = date('Y-m', strtotime($month . '-01 +1 month'));

        return TenantPayrollGeneratedSalary::query()
            ->where('month', $nextMonth)
            ->exists();
    }

    protected function normalizePayrollMonth(mixed $month): ?string
    {
        $month = trim((string) $month);

        return $month !== '' ? $month : null;
    }
}
