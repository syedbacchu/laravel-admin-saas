<?php

namespace App\Http\Services\TenantPayrollSalaryPayment;

use App\Http\Repositories\BaseRepositoryInterface;

interface TenantPayrollSalaryPaymentRepositoryInterface extends BaseRepositoryInterface
{
    public function findSalarySheet(int $salarySheetId): ?\App\Models\Tenant\TenantPayrollSalarySheet;

    public function findEmployee(int $employeeId): ?\App\Models\Tenant\TenantEmployee;

    public function getSalarySheetByEmployeeAndMonth(int $employeeId, string $month): ?\App\Models\Tenant\TenantPayrollSalarySheet;

    public function getPreviousDueAmount(int $employeeId, string $currentMonth): float;

    public function getActiveLoansForEmployee(int $employeeId): \Illuminate\Database\Eloquent\Collection;

    public function updateSalarySheetPayment(int $salarySheetId, float $paidAmount, float $dueAmount, string $paymentStatus, ?string $paidDate = null): bool;

    public function updateLoanPayment(int $employeeId, float $deductionAmount): bool;

    public function getPaymentHistory(int $salarySheetId): \Illuminate\Database\Eloquent\Collection;

    public function getTotalPaidForSalarySheet(int $salarySheetId): float;
}
