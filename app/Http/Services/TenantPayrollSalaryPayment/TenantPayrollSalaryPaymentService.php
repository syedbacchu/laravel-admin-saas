<?php

namespace App\Http\Services\TenantPayrollSalaryPayment;

use App\Http\Services\BaseService;
use App\Models\Tenant;
use App\Models\TenantPayrollSalarySheet;
use App\Traits\BlocksLockedPayrollMonths;
use Illuminate\Http\Request;
use App\Http\Requests\TenantApi\TenantPayrollSalaryPaymentCreateRequest;
use Illuminate\Support\Facades\DB;
use Throwable;

class TenantPayrollSalaryPaymentService extends BaseService implements TenantPayrollSalaryPaymentServiceInterface
{
    use BlocksLockedPayrollMonths;
    protected TenantPayrollSalaryPaymentRepositoryInterface $salaryPaymentRepository;

    public function __construct(TenantPayrollSalaryPaymentRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->salaryPaymentRepository = $repository;
    }

    public function getPayableAmount(Request $request, int $salarySheetId): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $salarySheet = $this->salaryPaymentRepository->findSalarySheet($salarySheetId);
        if (!$salarySheet) {
            return $this->sendResponse(false, __('Salary sheet not found'), [], 404);
        }

        $totalPaid = $this->salaryPaymentRepository->getTotalPaidForSalarySheet($salarySheetId);
        $netPayable = (float) $salarySheet->net_payable;
        $dueAmount = $netPayable - $totalPaid;

        $employee = $this->salaryPaymentRepository->findEmployee((int) $salarySheet->employee_id);
        $loans = $this->salaryPaymentRepository->getActiveLoansForEmployee((int) $salarySheet->employee_id);

        $loanInfo = [];
        foreach ($loans as $loan) {
            $loanInfo[] = [
                'id' => (int) $loan->id,
                'loan_amount' => (float) $loan->loan_amount,
                'paid_amount' => (float) $loan->paid_amount,
                'remaining_balance' => (float) $loan->remaining_balance,
                'monthly_deduction' => (float) $loan->monthly_deduction,
            ];
        }

        return $this->sendResponse(true, __('Payable amount retrieved successfully'), [
            'salary_sheet_id' => (int) $salarySheetId,
            'employee' => $employee ? [
                'id' => (int) $employee->id,
                'name' => (string) $employee->name,
                'mobile' => (string) $employee->mobile,
                'designation' => (string) $employee->designation,
            ] : null,
            'total_payable' => $this->toMoney($netPayable),
            'total_paid' => $this->toMoney($totalPaid),
            'due_amount' => $this->toMoney($dueAmount),
            'can_pay' => $dueAmount > 0,
            'max_payment_amount' => $this->toMoney($dueAmount),
            'payment_status' => (string) $salarySheet->payment_status,
            'loans' => $loanInfo,
        ]);
    }

    public function processPayment(TenantPayrollSalaryPaymentCreateRequest $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $salarySheetId = (int) $request->salary_sheet_id;
        $paymentAmount = $this->toMoney((float) $request->payment_amount);

        $salarySheet = $this->salaryPaymentRepository->findSalarySheet($salarySheetId);
        if (!$salarySheet) {
            return $this->sendResponse(false, __('Salary sheet not found'), [], 404);
        }

        $totalPaid = $this->salaryPaymentRepository->getTotalPaidForSalarySheet($salarySheetId);
        $netPayable = (float) $salarySheet->net_payable;
        $dueAmount = $netPayable - $totalPaid;

        if ($paymentAmount <= 0) {
            return $this->sendResponse(false, __('Payment amount must be greater than zero'), [], 422);
        }

        if ($paymentAmount > $dueAmount) {
            return $this->sendResponse(false, __('Payment amount cannot exceed due amount. Maximum payable: ') . number_format($dueAmount, 2), [], 422);
        }

        $employee = $this->salaryPaymentRepository->findEmployee((int) $salarySheet->employee_id);
        if (!$employee) {
            return $this->sendResponse(false, __('Employee not found'), [], 404);
        }

        $generatedSalary = $salarySheet->generatedSalary;
        $salaryMonth = $generatedSalary ? (string) $generatedSalary->month : date('Y-m');

        // Check if this month is locked (next month's salary already generated)
        if ($this->isPayrollMonthLocked($salaryMonth)) {
            $nextMonth = date('Y-m', strtotime($salaryMonth . '-01 +1 month'));
            return $this->sendResponse(
                false,
                __("Cannot process payment for :month. Salary for :nextMonth has already been generated and this month's dues have been carried forward. Please process payments for the current month instead.", [
                    'month' => $salaryMonth,
                    'nextMonth' => $nextMonth,
                ]),
                [],
                422
            );
        }

        try {
            DB::connection('tenant')->beginTransaction();

            $userId = (int) ($request->user()?->id ?? 0);
            $previousPaid = $totalPaid;
            $newTotalPaid = $previousPaid + $paymentAmount;
            $newDueAmount = $netPayable - $newTotalPaid;
            $paymentStatus = 'unpaid';

            if ($newDueAmount <= 0) {
                $paymentStatus = 'paid';
                $newDueAmount = 0;
            } elseif ($newTotalPaid > 0) {
                $paymentStatus = 'partial';
            }

            $paymentDate = $request->payment_date ?? now()->toDateString();
            $paymentMethod = (string) ($request->payment_method ?? 'cash');

            $paymentData = [
                'added_by' => $userId > 0 ? $userId : null,
                'updated_by' => $userId > 0 ? $userId : null,
                'payment_date' => $paymentDate,
                'salary_sheet_id' => $salarySheetId,
                'employee_id' => (int) $salarySheet->employee_id,
                'salary_month' => $salaryMonth,
                'total_payable' => $netPayable,
                'payment_amount' => $paymentAmount,
                'previous_paid' => $previousPaid,
                'remaining_due' => $newDueAmount,
                'office_id' => (int) $request->office_id,
                'payment_method' => $paymentMethod,
                'transaction_id' => $request->transaction_id,
                'remarks' => $request->remarks,
                'attachment' => $request->attachment,
                'status' => (int) ($request->status ?? 1),
            ];

            $payment = $this->salaryPaymentRepository->create($paymentData);

            $salaryExpenseData = [
                'added_by' => $userId > 0 ? $userId : null,
                'updated_by' => $userId > 0 ? $userId : null,
                'date' => $paymentDate,
                'salary_month' => $salaryMonth,
                'paid_to_user_id' => (int) $salarySheet->employee_id,
                'paid_to_user_type' => $employee->employee_type ?? 'employee',
                'paid_to' => $employee->name,
                'category' => 'salary_payment',
                'office_id' => (int) $request->office_id,
                'amount' => $paymentAmount,
                'remarks' => $this->buildSalaryExpenseRemarks($salaryMonth, $paymentAmount, $newTotalPaid, $netPayable, $request->remarks),
                'attachment' => $request->attachment,
                'status' => (int) ($request->status ?? 1),
            ];

            $salaryExpense = \App\Models\TenantSalaryExpense::create($salaryExpenseData);

            $payment->salary_expense_id = (int) $salaryExpense->id;
            $payment->save();

            $this->salaryPaymentRepository->updateSalarySheetPayment(
                $salarySheetId,
                $newTotalPaid,
                $newDueAmount,
                $paymentStatus,
                $paymentStatus === 'paid' ? now()->toDateString() : null
            );

            $loanDeduction = (float) $salarySheet->loan_deduction;
            if ($loanDeduction > 0) {
                $this->salaryPaymentRepository->updateLoanPayment((int) $salarySheet->employee_id, $loanDeduction);
            }

            DB::connection('tenant')->commit();

            $payment = $this->salaryPaymentRepository->find((int) $payment->id);
            $payment->load('salaryExpense');
            $this->attachReferenceDataToPayment($payment);

            return $this->sendResponse(true, __('Salary payment processed successfully'), $payment);

        } catch (Throwable $e) {
            DB::connection('tenant')->rollBack();
            logStore('TenantPayrollSalaryPaymentService processPayment', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong'), [], 500, $e->getMessage());
        }
    }

    public function getPaymentHistory(Request $request, int $salarySheetId): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $salarySheet = $this->salaryPaymentRepository->findSalarySheet($salarySheetId);
        if (!$salarySheet) {
            return $this->sendResponse(false, __('Salary sheet not found'), [], 404);
        }

        $payments = $this->salaryPaymentRepository->getPaymentHistory($salarySheetId);
        $payments->load('salaryExpense');
        $this->attachReferenceDataToPayments($payments);

        $totalPaid = $this->salaryPaymentRepository->getTotalPaidForSalarySheet($salarySheetId);

        return $this->sendResponse(true, __('Payment history retrieved successfully'), [
            'salary_sheet_id' => $salarySheetId,
            'total_payable' => $this->toMoney((float) $salarySheet->net_payable),
            'total_paid' => $this->toMoney($totalPaid),
            'remaining_due' => $this->toMoney((float) $salarySheet->due_amount),
            'payment_status' => (string) $salarySheet->payment_status,
            'payments' => $payments,
        ]);
    }

    public function getEmployeePaymentHistory(Request $request, int $employeeId): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $employee = $this->salaryPaymentRepository->findEmployee($employeeId);
        if (!$employee) {
            return $this->sendResponse(false, __('Employee not found'), [], 404);
        }

        $month = $request->query('month');
        $salarySheet = null;

        if ($month) {
            $salarySheet = $this->salaryPaymentRepository->getSalarySheetByEmployeeAndMonth($employeeId, $month);
        }

        $payments = \App\Models\TenantPayrollSalaryPayment::where('employee_id', $employeeId)
            ->when($month, function ($query) use ($month) {
                return $query->where('salary_month', $month);
            })
            ->orderBy('payment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $payments->load('salaryExpense');
        $this->attachReferenceDataToPayments($payments);

        $totalPaidAmount = (float) $payments->sum('payment_amount');

        return $this->sendResponse(true, __('Employee payment history retrieved successfully'), [
            'employee' => [
                'id' => (int) $employee->id,
                'name' => (string) $employee->name,
                'mobile' => (string) $employee->mobile,
                'designation' => (string) $employee->designation,
            ],
            'month' => $month,
            'salary_sheet' => $salarySheet ? [
                'id' => (int) $salarySheet->id,
                'net_payable' => (float) $salarySheet->net_payable,
                'paid_amount' => (float) $salarySheet->paid_amount,
                'due_amount' => (float) $salarySheet->due_amount,
                'payment_status' => (string) $salarySheet->payment_status,
            ] : null,
            'total_paid_amount' => $this->toMoney($totalPaidAmount),
            'payments' => $payments,
        ]);
    }

    protected function resolveTenantFromRequest(Request $request): ?Tenant
    {
        $tenant = $request->attributes->get('tenant');
        return $tenant instanceof Tenant ? $tenant : null;
    }

    protected function attachReferenceDataToPayments($payments): void
    {
        if (!is_iterable($payments)) {
            return;
        }

        $userIds = [];
        foreach ($payments as $payment) {
            $userIds[] = (int) $payment->added_by;
            $userIds[] = (int) $payment->updated_by;
        }

        $userMap = $this->resolveUserMap($userIds);

        foreach ($payments as $payment) {
            $this->applyReferenceDataToPayment($payment, $userMap);
        }
    }

    protected function attachReferenceDataToPayment($payment): void
    {
        if (!$payment) {
            return;
        }

        $userMap = $this->resolveUserMap([(int) $payment->added_by, (int) $payment->updated_by]);
        $this->applyReferenceDataToPayment($payment, $userMap);
    }

    protected function applyReferenceDataToPayment($payment, array $userMap): void
    {
        $addedBy = (int) $payment->added_by;
        $updatedBy = (int) $payment->updated_by;

        $payment->setAttribute('created_by_user', $userMap[$addedBy] ?? null);
        $payment->setAttribute('updated_by_user', $userMap[$updatedBy] ?? null);
    }

    protected function resolveUserMap(array $userIds): array
    {
        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));
        if (empty($userIds)) {
            return [];
        }

        $users = \App\Models\User::query()
            ->whereIn('id', $userIds)
            ->get(['id', 'name']);

        $map = [];
        foreach ($users as $user) {
            $map[(int) $user->id] = [
                'id' => (int) $user->id,
                'name' => (string) $user->name,
            ];
        }

        return $map;
    }

    protected function toMoney(mixed $value): float
    {
        return round((float) $value, 2);
    }

    protected function buildSalaryExpenseRemarks(string $salaryMonth, float $paymentAmount, float $totalPaid, float $netPayable, ?string $customRemarks = null): string
    {
        $remarks = "Salary payment for {$salaryMonth}: " . number_format($paymentAmount, 2) . " TK";

        if ($totalPaid < $netPayable) {
            $remaining = $netPayable - $totalPaid;
            $remarks .= " (Total paid: " . number_format($totalPaid, 2) . " TK, Due: " . number_format($remaining, 2) . " TK)";
        } else {
            $remarks .= " (Fully paid)";
        }

        if ($customRemarks) {
            $remarks .= " - " . $customRemarks;
        }

        return $remarks;
    }
}
