<?php

namespace App\Http\Services\TenantSalaryExpense;

use App\Http\Requests\TenantApi\TenantSalaryExpenseCreateRequest;
use App\Http\Services\BaseService;
use App\Models\Tenant;
use App\Models\TenantAllEmployee;
use App\Models\TenantOffice;
use App\Models\TenantPayrollLoanPayment;
use App\Models\TenantPayrollSalaryPayment;
use App\Models\TenantPayrollSalarySheet;
use App\Models\TenantSalaryExpense;
use App\Models\TenantTrip;
use App\Traits\BlocksLockedPayrollMonths;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class TenantSalaryExpenseService extends BaseService implements TenantSalaryExpenseServiceInterface
{
    use BlocksLockedPayrollMonths;

    protected TenantSalaryExpenseRepositoryInterface $tenantSalaryExpenseRepository;

    public function __construct(TenantSalaryExpenseRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->tenantSalaryExpenseRepository = $repository;
    }

    public function salaryExpenseList(Request $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $data = $this->tenantSalaryExpenseRepository->salaryExpenseList($request);
        $this->attachReferenceDataToExpenseList($data);

        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function storeSalaryExpense(TenantSalaryExpenseCreateRequest $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        try {
            $employee = null;
            if ($request->paid_to_user_id) {
                $employee = $this->resolveTenantEmployee((int) $request->paid_to_user_id);
                if (!$employee) {
                    return $this->sendResponse(false, __('Selected staff member is invalid for this tenant'), [], 422);
                }
            }

            $user = $request->user();
            $actorId = $user?->id;
            $salaryMonth = (string) $request->salary_month;
            $newAmount = (float) $request->amount;

            // Validate amount against remaining payable
            if ($request->paid_to_user_id && $salaryMonth && $newAmount > 0) {
                $employee = $this->resolveTenantEmployee((int) $request->paid_to_user_id);
                if ($employee && $employee->employee_type === 'employee') {
                    $validationResult = $this->getRemainingPayableAmount(
                        (int) $request->paid_to_user_id,
                        $salaryMonth,
                        $newAmount
                    );

                    if (!$validationResult['success']) {
                        return $this->sendResponse(false, $validationResult['message'], [], 422);
                    }

                    $remainingAmount = (float) $validationResult['remaining_amount'];
                    if ($newAmount > $remainingAmount) {
                        return $this->sendResponse(false,
                            "Payment amount ({$newAmount}) cannot exceed remaining payable amount ({$remainingAmount}). " .
                            "Overpayment by: " . number_format($newAmount - $remainingAmount, 2),
                            [], 422
                        );
                    }
                }
            }

            $existingItem = null;

            if ($request->edit_id) {
                $existingItem = $this->tenantSalaryExpenseRepository->findSalaryExpense((int) $request->edit_id);
                if (!$existingItem) {
                    return $this->sendResponse(false, __('Salary expense not found'), [], 404);
                }

                if ($lockResponse = $this->ensurePayrollMonthsAreEditable([(string) $existingItem->salary_month, $salaryMonth], __('Salary expense records'))) {
                    return $lockResponse;
                }
            } else {
                if ($lockResponse = $this->ensurePayrollMonthsAreEditable([$salaryMonth], __('Salary expense records'))) {
                    return $lockResponse;
                }
            }

            $response = DB::connection('tenant')->transaction(function () use ($request, $employee, $user, $actorId, $existingItem) {
                $data = [
                    'date' => $request->date,
                    'salary_month' => $request->salary_month,
                    'paid_to_user_id' => $request->paid_to_user_id,
                    'paid_to' => $employee->name ?? null,
                    'paid_to_user_type' => $employee->employee_type ?? 'employee',
                    'category' => (string) ($request->category ?: 'salary'),
                    'office_id' => (int) $request->office_id,
                    'amount' => $request->amount,
                    'remarks' => (string) $request->remarks,
                    'attachment' => $request->attachment,
                    'status' => (int) ($request->status ?? 1),
                    'updated_by' => $actorId,
                ];

                // Note: Amount validation is now handled in TenantSalaryExpenseCreateRequest withValidator

                if ($request->edit_id) {
                    $previousItem = clone $existingItem;
                    $hasLoanPayments = \App\Models\TenantPayrollLoanPayment::query()
                        ->where('salary_expense_id', $existingItem->id)
                        ->exists();

                    if ($hasLoanPayments && $this->hasProtectedPayrollFieldChanges($existingItem, $data)) {
                        return $this->sendResponse(false, __('This salary expense already has loan deductions. Employee, salary month, category, and date cannot be changed.'), [], 422);
                    }

                    $this->tenantSalaryExpenseRepository->update((int) $existingItem->id, $data);
                    $item = $this->tenantSalaryExpenseRepository->findSalaryExpense((int) $existingItem->id);
                    if (!$item) {
                        return $this->sendResponse(false, __('Salary expense not found'), [], 404);
                    }

                    $this->syncSalaryPaymentForExpense($item, $previousItem);

                    if ($item->category === 'salary' && $item->salary_month && !$hasLoanPayments) {
                        $this->processLoanDeductions($item, $user);
                    }

                    return $this->sendResponse(true, __('Salary expense updated successfully'), $item);
                }

                $data['added_by'] = $actorId;

                $item = $this->tenantSalaryExpenseRepository->createSalaryExpense($data);
                $item = $this->tenantSalaryExpenseRepository->findSalaryExpense((int) $item->id);
                if (!$item) {
                    return $this->sendResponse(false, __('Salary expense not found'), [], 404);
                }

                $this->syncSalaryPaymentForExpense($item);


                return $this->sendResponse(true, __('Salary expense created successfully'), $item);
            });

            if (($response['success'] ?? false) === true && ($response['data'] ?? null) instanceof TenantSalaryExpense) {
                $this->attachReferenceDataToExpense($response['data']);
            }

            return $response;
        } catch (\RuntimeException $e) {
            logStore('TenantSalaryExpenseService storeSalaryExpense runtime', $e->getMessage());
            return $this->sendResponse(false, $e->getMessage(), [], 422, $e->getMessage());
        } catch (Throwable $e) {
            logStore('TenantSalaryExpenseService storeSalaryExpense', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong'), [], 500, $e->getMessage());
        }
    }

    public function salaryExpenseDetails(Request $request, int $id): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $item = $this->tenantSalaryExpenseRepository->findSalaryExpense($id);
        if (!$item) {
            return $this->sendResponse(false, __('Salary expense not found'), [], 404);
        }

        $this->attachReferenceDataToExpense($item);

        return $this->sendResponse(true, __('Data get successfully.'), $item);
    }

    public function deleteSalaryExpense(Request $request, int $id): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $item = $this->tenantSalaryExpenseRepository->findSalaryExpense($id);
        if (!$item) {
            return $this->sendResponse(false, __('Salary expense not found'), [], 404);
        }
        
        // if ($lockResponse = $this->ensurePayrollMonthsAreEditable([(string) $item->salary_month], __('Salary expense records'))) {
        //     return $lockResponse;
        // }

        DB::connection('tenant')->transaction(function () use ($item, $id) {
            $this->deleteSalaryPaymentForExpense((int) $item->id);
            $this->tenantSalaryExpenseRepository->delete($id);
        });

        return $this->sendResponse(true, __('Salary expense deleted successfully'));
    }

    protected function resolveTenantFromRequest(Request $request): ?Tenant
    {
        $tenant = $request->attributes->get('tenant');
        return $tenant instanceof Tenant ? $tenant : null;
    }

    protected function attachReferenceDataToExpenseList(array &$data): void
    {
        if (!isset($data['data']) || !is_iterable($data['data'])) {
            return;
        }

        $officeIds = [];
        $paidToUserIds = [];
        foreach ($data['data'] as $item) {
            $officeIds[] = (int) $item->office_id;
            $paidToUserIds[] = (int) $item->paid_to_user_id;
        }

        $officeMap = $this->resolveOfficeMap($officeIds);
        $paidToUserMap = $this->resolvePaidToUserMap($paidToUserIds);

        foreach ($data['data'] as $item) {
            $this->applyReferenceToSalaryExpense($item, $officeMap, $paidToUserMap);
        }
    }

    protected function attachReferenceDataToExpense(TenantSalaryExpense $item): void
    {
        $officeMap = $this->resolveOfficeMap([(int) $item->office_id]);
        $paidToUserMap = $this->resolvePaidToUserMap([(int) $item->paid_to_user_id]);
        $this->applyReferenceToSalaryExpense($item, $officeMap, $paidToUserMap);
    }

    protected function applyReferenceToSalaryExpense(TenantSalaryExpense $item, array $officeMap, array $paidToUserMap): void
    {
        $officeId = (int) $item->office_id;
        $paidToUserId = (int) $item->paid_to_user_id;

        $item->setAttribute('office', $officeMap[$officeId] ?? null);
        $item->setAttribute('paid_to_user', $paidToUserMap[$paidToUserId] ?? null);
        $item->setAttribute('paid_to_user_type', $paidToUserMap[$paidToUserId]['employee_type'] ?? 'employee');
    }

    protected function resolveOfficeMap(array $officeIds): array
    {
        $officeIds = array_values(array_unique(array_filter(array_map('intval', $officeIds))));
        if (empty($officeIds)) {
            return [];
        }

        $offices = TenantOffice::query()
            ->whereIn('id', $officeIds)
            ->get(['id', 'branch_name']);

        $map = [];
        foreach ($offices as $office) {
            $map[(int) $office->id] = [
                'id' => (int) $office->id,
                'branch_name' => (string) $office->branch_name,
            ];
        }

        return $map;
    }

    protected function resolvePaidToUserMap(array $paidToUserIds): array
    {
        $paidToUserIds = array_values(array_unique(array_filter(array_map('intval', $paidToUserIds))));
        if (empty($paidToUserIds)) {
            return [];
        }

        $employees = TenantAllEmployee::query()
            ->whereIn('id', $paidToUserIds)
            ->get([
                'id',
                'employee_type',
                'name',
                'mobile',
                'designation',
                'status',
            ]);

        $map = [];
        foreach ($employees as $employee) {
            $map[(int) $employee->id] = [
                'id' => (int) $employee->id,
                'employee_type' => (string) $employee->employee_type,
                'name' => (string) $employee->name,
                'mobile' => $employee->mobile,
                'designation' => $employee->designation,
                'status' => (int) $employee->status,
            ];
        }

        return $map;
    }

    protected function resolveTenantEmployee(int $employeeId): ?TenantAllEmployee
    {
        if ($employeeId <= 0) {
            return null;
        }

        return TenantAllEmployee::query()
            ->where('id', $employeeId)
            ->first(['id', 'name', 'employee_type']);
    }

    protected function syncSalaryPaymentForExpense(TenantSalaryExpense $expense, ?TenantSalaryExpense $previousExpense = null): void
    {
        $previousSheetId = null;
        $previousPayment = TenantPayrollSalaryPayment::query()
            ->where('salary_expense_id', $expense->id)
            ->first();

        if ($previousPayment) {
            $previousSheetId = (int) $previousPayment->salary_sheet_id;
        } elseif ($previousExpense) {
            $previousSheet = $this->resolveSalarySheetForExpense($previousExpense);
            $previousSheetId = $previousSheet?->id;
        }

        if (!$this->isSalaryExpensePayment($expense)) {
            if ($previousPayment) {
                $previousPayment->delete();
            }

            if ($previousSheetId) {
                $this->recalculateSalarySheetPaymentState($previousSheetId);
            }

            return;
        }

        $salarySheet = $this->resolveSalarySheetForExpense($expense);
        if (!$salarySheet) {
            throw new \RuntimeException(__('Salary sheet not found for selected employee and month'));
        }

        $paymentAmount = round((float) $expense->amount, 2);
        if ($paymentAmount <= 0) {
            throw new \RuntimeException(__('Salary payment amount must be greater than zero'));
        }

        $totalPaidExcludingCurrent = (float) TenantPayrollSalaryPayment::query()
            ->where('salary_sheet_id', $salarySheet->id)
            ->when($previousPayment, function ($query) use ($previousPayment) {
                $query->where('id', '!=', (int) $previousPayment->id);
            })
            ->sum('payment_amount');

        $newTotalPaid = round($totalPaidExcludingCurrent + $paymentAmount, 2);
        $netPayable = round((float) $salarySheet->net_payable, 2);
        $remainingDue = round(max(0, $netPayable - $newTotalPaid), 2);
        $paidDate = $remainingDue <= 0 ? (string) optional($expense->date)->format('Y-m-d') : null;

        $paymentData = [
            'added_by' => $expense->added_by ?: null,
            'updated_by' => $expense->updated_by ?: null,
            'payment_date' => optional($expense->date)->format('Y-m-d') ?: now()->toDateString(),
            'salary_sheet_id' => (int) $salarySheet->id,
            'employee_id' => (int) $expense->paid_to_user_id,
            'salary_month' => (string) $expense->salary_month,
            'total_payable' => $netPayable,
            'payment_amount' => $paymentAmount,
            'previous_paid' => round($totalPaidExcludingCurrent, 2),
            'remaining_due' => $remainingDue,
            'office_id' => (int) ($expense->office_id ?? 0),
            'payment_method' => 'cash',
            'transaction_id' => null,
            'remarks' => $expense->remarks,
            'attachment' => $expense->attachment,
            'status' => (int) $expense->status,
            'salary_expense_id' => (int) $expense->id,
        ];

        if ($previousPayment) {
            $previousPayment->fill($paymentData)->save();
        } else {
            TenantPayrollSalaryPayment::create($paymentData);
        }

        $this->recalculateSalarySheetPaymentState((int) $salarySheet->id, $paidDate);

        if ($previousSheetId && $previousSheetId !== (int) $salarySheet->id) {
            $this->recalculateSalarySheetPaymentState($previousSheetId);
        }
    }

    protected function deleteSalaryPaymentForExpense(int $salaryExpenseId): void
    {
        $payment = TenantPayrollSalaryPayment::query()
            ->where('salary_expense_id', $salaryExpenseId)
            ->first();

        if (!$payment) {
            return;
        }

        $salarySheetId = (int) $payment->salary_sheet_id;
        $payment->delete();
        $this->recalculateSalarySheetPaymentState($salarySheetId);
    }

    protected function recalculateSalarySheetPaymentState(int $salarySheetId, ?string $paidDate = null): void
    {
        $salarySheet = TenantPayrollSalarySheet::query()->find($salarySheetId);
        if (!$salarySheet) {
            return;
        }

        $totalPaid = round((float) TenantPayrollSalaryPayment::query()
            ->where('salary_sheet_id', $salarySheetId)
            ->sum('payment_amount'), 2);

        $netPayable = round((float) $salarySheet->net_payable, 2);
        $dueAmount = round(max(0, $netPayable - $totalPaid), 2);

        $paymentStatus = 'unpaid';
        if ($totalPaid > 0 && $dueAmount <= 0) {
            $paymentStatus = 'paid';
        } elseif ($totalPaid > 0) {
            $paymentStatus = 'partial';
        }

        if ($paidDate === null && $totalPaid > 0) {
            $paidDate = TenantPayrollSalaryPayment::query()
                ->where('salary_sheet_id', $salarySheetId)
                ->orderByDesc('payment_date')
                ->orderByDesc('created_at')
                ->value('payment_date');
        }

        $salarySheet->update([
            'paid_amount' => $totalPaid,
            'due_amount' => $dueAmount,
            'payment_status' => $paymentStatus,
            'paid_date' => in_array($paymentStatus, ['partial', 'paid'], true) ? $paidDate : null,
        ]);
    }

    protected function resolveSalarySheetForExpense(TenantSalaryExpense $expense): ?TenantPayrollSalarySheet
    {
        $employeeId = (int) $expense->paid_to_user_id;
        $salaryMonth = (string) $expense->salary_month;

        if ($employeeId <= 0 || $salaryMonth === '') {
            return null;
        }

        return TenantPayrollSalarySheet::query()
            ->where('employee_id', $employeeId)
            ->whereHas('generatedSalary', function ($query) use ($salaryMonth) {
                $query->where('month', $salaryMonth);
            })
            ->first();
    }

    protected function isSalaryExpensePayment(TenantSalaryExpense $expense): bool
    {
        return in_array((string) $expense->category, ['salary', 'salary_payment'], true);
    }

    public function calculatePayableAmount(Request $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $employeeId = (int) ($request->employee_id ?? $request->paid_to_user_id ?? 0);
        $salaryMonth = (string) ($request->salary_month ?? '');

        if ($employeeId <= 0) {
            return $this->sendResponse(false, __('Employee ID is required'), [], 422);
        }

        if (!$salaryMonth) {
            return $this->sendResponse(false, __('Salary month is required'), [], 422);
        }

        // Use the core calculation method
        $result = $this->calculateSalaryPayable($employeeId, $salaryMonth);

        if (!$result['success']) {
            return $this->sendResponse(false, $result['message'], [], 422);
        }

        return $this->sendResponse(true, __('Payable amount calculated successfully'), [
            'gross_salary' => number_format($result['gross_salary'], 2, '.', ''),
            'advance_deduction' => number_format($result['advance_deduction'], 2, '.', ''),
            'loan_deduction' => number_format($result['loan_deduction'], 2, '.', ''),
            'loan_total_monthly' => number_format($result['loan_total_monthly'], 2, '.', ''),
            'loan_paid_this_month' => number_format($result['loan_paid_this_month'], 2, '.', ''),
            'previous_month_due' => number_format($result['previous_month_due'], 2, '.', ''),
            'total_deductions' => number_format($result['total_deductions'], 2, '.', ''),
            'payable_amount' => number_format($result['payable_amount'], 2, '.', ''),
            'already_paid' => number_format($result['already_paid_cash'], 2, '.', ''),
            'remaining_amount' => number_format($result['remaining_amount'], 2, '.', ''),
            'trip_profit' => number_format($result['trip_profit'], 2, '.', ''),
        ]);
    }

    protected function resolveGrossSalaryAmount(TenantAllEmployee $employee): float
    {
        return round((float) ($employee->gross_salary ?? 0), 2);
    }

    protected function resolveTripProfitForEmployee(int $employeeId, string $employeeType, string $salaryMonth): float
    {
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $salaryMonth)) {
            return 0.0;
        }

        [$year, $month] = explode('-', $salaryMonth);

        $query = TenantTrip::query()
            ->whereYear('date', (int) $year)
            ->whereMonth('date', (int) $month);

        if ($employeeType === 'helper') {
            $query->where('helper_id', $employeeId);
        } elseif ($employeeType === 'supervisor') {
            $query->where('supervisor_id', $employeeId);
        } else {
            return 0.0;
        }

        $tripProfit = (float) $query
            ->selectRaw('COALESCE(SUM(COALESCE(demurrage_total_rent, 0) - COALESCE(total_expense, 0)), 0) as trip_profit')
            ->value('trip_profit');

        return round($tripProfit, 2);
    }

    protected function resolveAdvanceDeductionAmount(int $employeeId, string $salaryMonth): float
    {
        $advanceDeduction = (float) \App\Models\TenantPayrollAdvanceSalary::query()
            ->where('employee_id', $employeeId)
            ->where('salary_month', $salaryMonth)
            ->whereIn('status', ['paid'])
            ->selectRaw('COALESCE(SUM(CASE WHEN COALESCE(after_adjustment_amount, 0) > 0 THEN after_adjustment_amount ELSE advance_amount END), 0) as total_advance')
            ->value('total_advance');

        return round($advanceDeduction, 2);
    }

    protected function hasProtectedPayrollFieldChanges(TenantSalaryExpense $item, array $data): bool
    {
        return (string) optional($item->date)->format('Y-m-d') !== (string) $data['date']
            || (string) $item->salary_month !== (string) $data['salary_month']
            || (int) $item->paid_to_user_id !== (int) $data['paid_to_user_id']
            || (string) $item->category !== (string) $data['category'];
    }

    protected function processLoanDeductions(TenantSalaryExpense $salaryExpense, $user): void
    {
        try {
            $employeeId = $salaryExpense->paid_to_user_id;
            $salaryMonth = $salaryExpense->salary_month;


            if (!$employeeId || !$salaryMonth) {
                logStore('processLoanDeductions', 'employee id or month not found');
                return;
            }

            // Get all active loans for this employee
            $activeLoans = \App\Models\TenantPayrollLoan::query()
                ->where('employee_id', $employeeId)
                ->where('status', 'pending')
                ->where('remaining_balance', '>', 0)
                ->get();

            if ($activeLoans->isEmpty()) {
                logStore('processLoanDeductions', 'loans empty');
                return;
            }

            $currentUserId = $user ? $user->id : null;
            logStore('processLoanDeductions current user id', $currentUserId);
            foreach ($activeLoans as $loan) {
                $existingMonthlyDeduction = \App\Models\TenantPayrollLoanPayment::query()
                    ->where('loan_id', $loan->id)
                    ->where('salary_month', $salaryMonth)
                    ->where('payment_method', 'salary_deduction')
                    ->exists();

                if ($existingMonthlyDeduction) {
                    logStore('processLoanDeductions ', '$existingMonthlyDeduction already processed');
                    continue;
                }

                // Calculate the deduction amount for this month
                // Use the lesser of monthly_deduction or remaining_balance
                $deductionAmount = min(
                    (float) $loan->monthly_deduction,
                    (float) $loan->remaining_balance
                );

                if ($deductionAmount <= 0) {
                    continue;
                }

                // Create loan payment record
                \App\Models\TenantPayrollLoanPayment::create([
                    'added_by' => $currentUserId,
                    'updated_by' => $currentUserId,
                    'payment_date' => $salaryExpense->date,
                    'loan_id' => $loan->id,
                    'employee_id' => $employeeId,
                    'salary_expense_id' => $salaryExpense->id,
                    'salary_month' => $salaryMonth,
                    'principal_amount' => $deductionAmount,
                    'interest_amount' => 0,
                    'paid_amount' => $deductionAmount,
                    'remaining_balance_before' => $loan->remaining_balance,
                    'remaining_balance_after' => max(0, $loan->remaining_balance - $deductionAmount),
                    'payment_method' => 'salary_deduction',
                    'remarks' => "Loan payment deducted from salary for $salaryMonth",
                    'status' => 1,
                ]);

                logStore('processLoanDeductions ', 'loan payment created');
                // Update loan balance
                $newPaidAmount = round((float) $loan->paid_amount + $deductionAmount, 2);
                $newRemainingBalance = round(max(0, (float) $loan->remaining_balance - $deductionAmount), 2);

                $loan->update([
                    'paid_amount' => $newPaidAmount,
                    'remaining_balance' => $newRemainingBalance,
                    'after_adjustment_amount' => $newRemainingBalance,
                    'status' => $newRemainingBalance <= 0 ? 'completed' : 'pending',
                    'updated_by' => $currentUserId,
                ]);
                logStore('processLoanDeductions ', 'loan updated');
            }
        } catch (\Exception $e) {
            logStore('processLoanDeductions ex', $e->getMessage());
        }

    }

    /**
     * Core calculation method for salary payable amount
     * Used by both API endpoint and internal validation
     */
    protected function calculateSalaryPayable(int $employeeId, string $salaryMonth, ?float $newAmount = null): array
    {
        try {
            if ($employeeId <= 0) {
                return ['success' => false, 'message' => 'Employee ID is required'];
            }

            if (!$salaryMonth) {
                return ['success' => false, 'message' => 'Salary month is required'];
            }

            $employee = TenantAllEmployee::query()->find($employeeId);
            if (!$employee) {
                return ['success' => false, 'message' => 'Employee not found'];
            }

            $grossSalary = $this->resolveGrossSalaryAmount($employee);
            $advanceDeduction = $this->resolveAdvanceDeductionAmount($employeeId, $salaryMonth);

            // Get active loan monthly deductions
            $loanDeduction = (float) \App\Models\TenantPayrollLoan::query()
                ->where('employee_id', $employeeId)
                ->where('status', 'pending')
                ->sum('monthly_deduction');

            // Calculate how much loan has already been deducted this month
            $paidThisMonth = (float) \App\Models\TenantPayrollLoanPayment::query()
                ->where('employee_id', $employeeId)
                ->where('salary_month', $salaryMonth)
                ->sum('paid_amount');

            // Adjust loan deduction by subtracting already paid amount
            $adjustedLoanDeduction = max(0, $loanDeduction - $paidThisMonth);

            $tripProfit = 0.0;
            if ($employee->employee_type === 'helper') {
                $tripProfit = $this->resolveTripProfitForEmployee($employeeId, 'helper', $salaryMonth);
            } elseif ($employee->employee_type === 'supervisor') {
                $tripProfit = $this->resolveTripProfitForEmployee($employeeId, 'supervisor', $salaryMonth);
            }

            // Calculate previous month's due
            $previousMonthDue = 0.0;
            try {
                $previousMonthDate = date('Y-m', strtotime($salaryMonth . '-01 -1 month'));
                $previousMonthDue = (float) \App\Models\TenantPayrollSalarySheet::query()
                    ->where('employee_id', $employeeId)
                    ->whereHas('generatedSalary', function ($query) use ($previousMonthDate) {
                        $query->where('month', $previousMonthDate);
                    })
                    ->sum('due_amount');
            } catch (\Exception $e) {
                $previousMonthDue = 0.0;
            }

            // Calculate total deductions
            $totalDeductions = $advanceDeduction + $loanDeduction;

            // Calculate payable amount
            $payableAmount = ($grossSalary + $tripProfit + $previousMonthDue) - $totalDeductions;

            // Get existing salary expenses for this employee and month
            $salaryExpenseRecords = TenantSalaryExpense::query()
                ->where('paid_to_user_id', $employeeId)
                ->where('salary_month', $salaryMonth)
                ->whereIn('category', ['salary', 'salary_payment'])
                ->where('status', 1)
                ->get();

            // Calculate actual cash given to employee (total expense - loan payments)
            $totalCashToEmployee = 0.0;
            foreach ($salaryExpenseRecords as $expense) {
                $expenseLoanPayments = (float) \App\Models\TenantPayrollLoanPayment::query()
                    ->where('salary_expense_id', $expense->id)
                    ->sum('paid_amount');

                $totalCashToEmployee += max(0, $expense->amount - $expenseLoanPayments);
            }

            $remainingAmount = $payableAmount - $totalCashToEmployee;

            $remainingAmount = max(0, $remainingAmount);


            return [
                'success' => true,
                'gross_salary' => $grossSalary,
                'advance_deduction' => $advanceDeduction,
                'loan_deduction' => $adjustedLoanDeduction,
                'loan_total_monthly' => $loanDeduction,
                'loan_paid_this_month' => $paidThisMonth,
                'previous_month_due' => $previousMonthDue,
                'total_deductions' => $totalDeductions,
                'payable_amount' => max(0, $payableAmount),
                'already_paid_cash' => $totalCashToEmployee,
                'remaining_amount' => $remainingAmount,
                'trip_profit' => $tripProfit,
            ];
        } catch (\Exception $e) {
            logStore('calculateSalaryPayable error', $e->getMessage());
            return ['success' => false, 'message' => 'Error calculating payable amount: ' . $e->getMessage()];
        }
    }

    /**
     * Helper method to get remaining payable amount for validation
     * Used during salary expense creation to prevent overpayment
     */
    protected function getRemainingPayableAmount(int $employeeId, string $salaryMonth, float $newAmount): array
    {
        $result = $this->calculateSalaryPayable($employeeId, $salaryMonth, $newAmount);

        if (!$result['success']) {
            return [
                'success' => false,
                'message' => $result['message'],
                'remaining_amount' => 0
            ];
        }

        return [
            'success' => true,
            'message' => 'Calculation successful',
            'remaining_amount' => $result['remaining_amount'],
            'net_payable' => $result['payable_amount'],
            'already_paid_cash' => $result['already_paid_cash'],
        ];
    }

    /**
     * Static helper method to get remaining payable amount for validation
     * Can be called from Form Request validators
     */
    public static function getRemainingPayableAmountStatic(int $employeeId, string $salaryMonth, ?int $excludeExpenseId = null): array
    {
        try {
            if ($employeeId <= 0) {
                return ['success' => false, 'message' => 'Employee ID is required', 'remaining_amount' => 0];
            }

            if (!$salaryMonth) {
                return ['success' => false, 'message' => 'Salary month is required', 'remaining_amount' => 0];
            }

            $employee = \App\Models\TenantAllEmployee::query()->find($employeeId);
            if (!$employee) {
                return ['success' => false, 'message' => 'Employee not found', 'remaining_amount' => 0];
            }

            // Only calculate for employee type, not helpers/supervisors
            if ($employee->employee_type !== 'employee') {
                return ['success' => true, 'remaining_amount' => PHP_FLOAT_MAX];
            }

            $grossSalary = round((float) ($employee->gross_salary ?? 0), 2);

            $advanceDeduction = (float) \App\Models\TenantPayrollAdvanceSalary::query()
                ->where('employee_id', $employeeId)
                ->where('salary_month', $salaryMonth)
                ->whereIn('status', ['paid'])
                ->selectRaw('COALESCE(SUM(CASE WHEN COALESCE(after_adjustment_amount, 0) > 0 THEN after_adjustment_amount ELSE advance_amount END), 0) as total_advance')
                ->value('total_advance');

            $advanceDeduction = round($advanceDeduction, 2);

            $loanDeduction = (float) \App\Models\TenantPayrollLoan::query()
                ->where('employee_id', $employeeId)
                ->where('status', 'pending')
                ->sum('monthly_deduction');

            $paidThisMonth = (float) \App\Models\TenantPayrollLoanPayment::query()
                ->where('employee_id', $employeeId)
                ->where('salary_month', $salaryMonth)
                ->sum('paid_amount');

            $adjustedLoanDeduction = max(0, $loanDeduction - $paidThisMonth);

            $previousMonthDue = 0.0;
            try {
                $previousMonthDate = date('Y-m', strtotime($salaryMonth . '-01 -1 month'));
                $previousMonthDue = (float) \App\Models\TenantPayrollSalarySheet::query()
                    ->where('employee_id', $employeeId)
                    ->whereHas('generatedSalary', function ($query) use ($previousMonthDate) {
                        $query->where('month', $previousMonthDate);
                    })
                    ->sum('due_amount');
            } catch (\Exception $e) {
                $previousMonthDue = 0.0;
            }

            $totalDeductions = $advanceDeduction + $adjustedLoanDeduction;
            $payableAmount = max(0, $grossSalary + $previousMonthDue - $totalDeductions);

            $query = \App\Models\TenantSalaryExpense::query()
                ->where('paid_to_user_id', $employeeId)
                ->where('salary_month', $salaryMonth)
                ->whereIn('category', ['salary', 'salary_payment'])
                ->where('status', 1);

            // Exclude current record when editing
            if ($excludeExpenseId) {
                $query->where('id', '!=', $excludeExpenseId);
            }

            $salaryExpenseRecords = $query->get();

            $totalCashToEmployee = 0.0;
            foreach ($salaryExpenseRecords as $expense) {
                $expenseLoanPayments = (float) \App\Models\TenantPayrollLoanPayment::query()
                    ->where('salary_expense_id', $expense->id)
                    ->sum('paid_amount');

                $totalCashToEmployee += max(0, $expense->amount - $expenseLoanPayments);
            }

            $remainingAmount = max(0, $payableAmount - $totalCashToEmployee);

            return [
                'success' => true,
                'message' => 'Calculation successful',
                'remaining_amount' => $remainingAmount,
                'payable_amount' => $payableAmount,
                'already_paid_cash' => $totalCashToEmployee,
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('getRemainingPayableAmountStatic: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error calculating payable amount', 'remaining_amount' => 0];
        }
    }
}
