<?php

namespace App\Http\Services\TenantPayrollLoan;

use App\Http\Requests\TenantApi\TenantPayrollLoanCreateRequest;
use App\Http\Services\BaseService;
use App\Models\Tenant;
use App\Models\TenantAllEmployee;
use App\Models\TenantPayrollLoan;
use App\Models\User;
use Illuminate\Http\Request;
use Throwable;

class TenantPayrollLoanService extends BaseService implements TenantPayrollLoanServiceInterface
{
    protected TenantPayrollLoanRepositoryInterface $tenantPayrollLoanRepository;

    public function __construct(TenantPayrollLoanRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->tenantPayrollLoanRepository = $repository;
    }

    public function loanList(Request $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $data = $this->tenantPayrollLoanRepository->loanList($request);
        $this->attachReferenceDataToLoanList($data);

        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function storeLoan(TenantPayrollLoanCreateRequest $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        try {
            $data = [
                'loan_date' => $request->loan_date,
                'employee_id' => (int) $request->employee_id,
                'loan_amount' => $request->loan_amount,
                'monthly_deduction' => $request->monthly_deduction,
                'status' => (string) $request->status,
            ];

            if ($request->edit_id) {
                $item = $this->tenantPayrollLoanRepository->findLoan((int) $request->edit_id);
                if (!$item) {
                    return $this->sendResponse(false, __('Loan not found'), [], 404);
                }

                $this->tenantPayrollLoanRepository->update((int) $item->id, $data);
                $item = $this->tenantPayrollLoanRepository->findLoan((int) $item->id);
                if (!$item) {
                    return $this->sendResponse(false, __('Loan not found'), [], 404);
                }

                $this->attachReferenceDataToLoan($item);

                return $this->sendResponse(true, __('Loan updated successfully'), $item);
            } else {
                $data['remaining_balance'] = $request->loan_amount;
                $data['after_adjustment_amount'] = $request->after_adjustment_amount;
            }

            $item = $this->tenantPayrollLoanRepository->createLoan($data);
            $item = $this->tenantPayrollLoanRepository->findLoan((int) $item->id);
            if (!$item) {
                return $this->sendResponse(false, __('Loan not found'), [], 404);
            }

            $this->attachReferenceDataToLoan($item);

            return $this->sendResponse(true, __('Loan created successfully'), $item);
        } catch (Throwable $e) {
            logStore('TenantPayrollLoanService storeLoan', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong'), [], 500, $e->getMessage());
        }
    }

    public function loanDetails(Request $request, int $id): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $item = $this->tenantPayrollLoanRepository->findLoan($id);
        if (!$item) {
            return $this->sendResponse(false, __('Loan not found'), [], 404);
        }

        $this->attachReferenceDataToLoan($item);

        return $this->sendResponse(true, __('Data get successfully.'), $item);
    }

    public function deleteLoan(Request $request, int $id): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $item = $this->tenantPayrollLoanRepository->findLoan($id);
        if (!$item) {
            return $this->sendResponse(false, __('Loan not found'), [], 404);
        }

        $this->tenantPayrollLoanRepository->delete($id);

        return $this->sendResponse(true, __('Loan deleted successfully'));
    }

    protected function resolveTenantFromRequest(Request $request): ?Tenant
    {
        $tenant = $request->attributes->get('tenant');
        return $tenant instanceof Tenant ? $tenant : null;
    }

    protected function attachReferenceDataToLoanList(array &$data): void
    {
        if (!isset($data['data']) || !is_iterable($data['data'])) {
            return;
        }

        $employeeIds = [];
        $userIds = [];

        foreach ($data['data'] as $item) {
            $employeeIds[] = (int) $item->employee_id;
            $userIds[] = (int) $item->added_by;
        }

        $employeeMap = $this->resolveEmployeeMap($employeeIds);
        $userMap = $this->resolveUserMap($userIds);

        foreach ($data['data'] as $item) {
            $this->applyReferenceDataToLoan($item, $employeeMap, $userMap);
        }
    }

    protected function attachReferenceDataToLoan(TenantPayrollLoan $item): void
    {
        $employeeMap = $this->resolveEmployeeMap([(int) $item->employee_id]);
        $userMap = $this->resolveUserMap([(int) $item->added_by]);
        $this->applyReferenceDataToLoan($item, $employeeMap, $userMap);
    }

    protected function applyReferenceDataToLoan(TenantPayrollLoan $item, array $employeeMap, array $userMap): void
    {
        $employeeId = (int) $item->employee_id;
        $addedBy = (int) $item->added_by;

        $item->setAttribute('employee', $employeeMap[$employeeId] ?? null);
        $item->setAttribute('created_by_user', $userMap[$addedBy] ?? null);
    }

    protected function resolveEmployeeMap(array $employeeIds): array
    {
        $employeeIds = array_values(array_unique(array_filter(array_map('intval', $employeeIds))));
        if (empty($employeeIds)) {
            return [];
        }

        $employees = TenantAllEmployee::query()
            ->whereIn('id', $employeeIds)
            ->get(['id', 'employee_type', 'name', 'mobile', 'designation', 'status']);

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

    protected function resolveUserMap(array $userIds): array
    {
        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));
        if (empty($userIds)) {
            return [];
        }

        $users = User::query()
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
}
