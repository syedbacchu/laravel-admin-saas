<?php

namespace App\Http\Services\TenantPayrollSalaryPayment;

use App\Http\Repositories\BaseRepository;
use App\Models\Tenant\TenantEmployee;
use App\Models\Tenant\TenantPayrollLoan;
use App\Models\Tenant\TenantPayrollSalaryPayment;
use App\Models\Tenant\TenantPayrollSalarySheet;
use Illuminate\Database\Eloquent\Collection;

class TenantPayrollSalaryPaymentRepository extends BaseRepository implements TenantPayrollSalaryPaymentRepositoryInterface
{
    public function __construct(TenantPayrollSalaryPayment $model)
    {
        parent::__construct($model);
    }

    public function findSalarySheet(int $salarySheetId): ?TenantPayrollSalarySheet
    {
        return TenantPayrollSalarySheet::find($salarySheetId);
    }

    public function findEmployee(int $employeeId): ?TenantEmployee
    {
        return TenantEmployee::find($employeeId);
    }

    public function getSalarySheetByEmployeeAndMonth(int $employeeId, string $month): ?TenantPayrollSalarySheet
    {
        return TenantPayrollSalarySheet::whereHas('generatedSalary', function ($query) use ($month) {
            $query->where('month', $month);
        })->where('employee_id', $employeeId)
            ->first();
    }

    public function getPreviousDueAmount(int $employeeId, string $currentMonth): float
    {
        $previousMonth = date('Y-m', strtotime($currentMonth . '-01 -1 month'));

        $salarySheet = TenantPayrollSalarySheet::whereHas('generatedSalary', function ($query) use ($previousMonth) {
            $query->where('month', $previousMonth);
        })->where('employee_id', $employeeId)
            ->first();

        if (!$salarySheet) {
            return 0;
        }

        return (float) ($salarySheet->due_amount ?? 0);
    }

    public function getActiveLoansForEmployee(int $employeeId): Collection
    {
        return TenantPayrollLoan::where('employee_id', $employeeId)
            ->where('status', 'pending')
            ->whereColumn('remaining_balance', '>', 'paid_amount')
            ->get();
    }

    public function updateSalarySheetPayment(int $salarySheetId, float $paidAmount, float $dueAmount, string $paymentStatus, ?string $paidDate = null): bool
    {
        $salarySheet = $this->findSalarySheet($salarySheetId);
        if (!$salarySheet) {
            return false;
        }

        $salarySheet->paid_amount = $paidAmount;
        $salarySheet->due_amount = $dueAmount;
        $salarySheet->payment_status = $paymentStatus;

        if ($paidDate && in_array($paymentStatus, ['partial', 'paid'])) {
            $salarySheet->paid_date = $paidDate;
        }

        return $salarySheet->save();
    }

    public function updateLoanPayment(int $employeeId, float $deductionAmount): bool
    {
        $loans = $this->getActiveLoansForEmployee($employeeId);

        $remainingDeduction = $deductionAmount;

        foreach ($loans as $loan) {
            if ($remainingDeduction <= 0) {
                break;
            }

            $loanRemainingBalance = (float) ($loan->remaining_balance ?? $loan->loan_amount);
            $currentPaidAmount = (float) ($loan->paid_amount ?? 0);

            $deductAmount = min($remainingDeduction, $loanRemainingBalance);

            $loan->paid_amount = $currentPaidAmount + $deductAmount;
            $loan->remaining_balance = $loanRemainingBalance - $deductAmount;

            if ($loan->remaining_balance <= 0) {
                $loan->status = 'paid';
                $loan->remaining_balance = 0;
            }

            $loan->save();

            $remainingDeduction -= $deductAmount;
        }

        return true;
    }

    public function getPaymentHistory(int $salarySheetId): Collection
    {
        return TenantPayrollSalaryPayment::where('salary_sheet_id', $salarySheetId)
            ->orderBy('payment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getTotalPaidForSalarySheet(int $salarySheetId): float
    {
        return (float) TenantPayrollSalaryPayment::where('salary_sheet_id', $salarySheetId)
            ->sum('payment_amount');
    }
}
