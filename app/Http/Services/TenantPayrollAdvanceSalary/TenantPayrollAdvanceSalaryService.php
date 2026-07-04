<?php

namespace App\Http\Services\TenantPayrollAdvanceSalary;

use App\Http\Requests\TenantApi\TenantPayrollAdvanceSalaryCreateRequest;
use App\Http\Services\BaseService;
use App\Models\Tenant;
use App\Models\Tenant\TenantAllEmployee;
use App\Models\Tenant\TenantPayrollAdvanceSalary;
use App\Models\Tenant\TenantSalaryExpense;
use App\Models\User;
use App\Traits\BlocksLockedPayrollMonths;
use Illuminate\Http\Request;
use Throwable;

class TenantPayrollAdvanceSalaryService extends BaseService implements TenantPayrollAdvanceSalaryServiceInterface
{
    use BlocksLockedPayrollMonths;

    protected TenantPayrollAdvanceSalaryRepositoryInterface $tenantPayrollAdvanceSalaryRepository;

    public function __construct(TenantPayrollAdvanceSalaryRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->tenantPayrollAdvanceSalaryRepository = $repository;
    }

    public function advanceSalaryList(Request $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $data = $this->tenantPayrollAdvanceSalaryRepository->advanceSalaryList($request);
        $this->attachReferenceDataToAdvanceSalaryList($data);

        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function storeAdvanceSalary(TenantPayrollAdvanceSalaryCreateRequest $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        try {
            $data = [
                'date' => $request->date,
                'employee_id' => (int) $request->employee_id,
                'advance_amount' => $request->advance_amount,
                'salary_month' => (string) $request->salary_month,
                'after_adjustment_amount' => $request->advance_amount,
                'status' => (string) $request->status,
            ];

            if ($request->edit_id) {
                $item = $this->tenantPayrollAdvanceSalaryRepository->findAdvanceSalary((int) $request->edit_id);
                if (!$item) {
                    return $this->sendResponse(false, __('Advance salary not found'), [], 404);
                }
                if ($lockResponse = $this->ensurePayrollMonthsAreEditable([(string) $item->salary_month, (string) $data['salary_month']], __('Advance salary records'))) {
                    return $lockResponse;
                }
                if ($this->checkSalaryPaidAlready($request->employee_id, $request->salary_month)) {
                    return $this->sendResponse(false, __('Advance salary paid already exists. so you can not edit it'), [], 400);
                }
                $this->tenantPayrollAdvanceSalaryRepository->update((int) $item->id, $data);
                $item = $this->tenantPayrollAdvanceSalaryRepository->findAdvanceSalary((int) $item->id);
                if (!$item) {
                    return $this->sendResponse(false, __('Advance salary not found'), [], 404);
                }

                $this->attachReferenceDataToAdvanceSalary($item);

                return $this->sendResponse(true, __('Advance salary updated successfully'), $item);
            }

            if ($lockResponse = $this->ensurePayrollMonthsAreEditable([(string) $data['salary_month']], __('Advance salary records'))) {
                return $lockResponse;
            }

            $item = $this->tenantPayrollAdvanceSalaryRepository->createAdvanceSalary($data);
            $item = $this->tenantPayrollAdvanceSalaryRepository->findAdvanceSalary((int) $item->id);
            if (!$item) {
                return $this->sendResponse(false, __('Advance salary not found'), [], 404);
            }

            $this->attachReferenceDataToAdvanceSalary($item);

            return $this->sendResponse(true, __('Advance salary created successfully'), $item);
        } catch (Throwable $e) {
            logStore('TenantPayrollAdvanceSalaryService storeAdvanceSalary', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong'), [], 500, $e->getMessage());
        }
    }

    public function checkSalaryPaidAlready($employee_id, $salary_month)
    {
        return TenantSalaryExpense::where(['paid_to_user_id' => $employee_id, 'salary_month' => $salary_month])->first();
    }
    public function advanceSalaryDetails(Request $request, int $id): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $item = $this->tenantPayrollAdvanceSalaryRepository->findAdvanceSalary($id);
        if (!$item) {
            return $this->sendResponse(false, __('Advance salary not found'), [], 404);
        }

        $this->attachReferenceDataToAdvanceSalary($item);

        return $this->sendResponse(true, __('Data get successfully.'), $item);
    }

    public function deleteAdvanceSalary(Request $request, int $id): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $item = $this->tenantPayrollAdvanceSalaryRepository->findAdvanceSalary($id);
        if (!$item) {
            return $this->sendResponse(false, __('Advance salary not found'), [], 404);
        }
        if ($lockResponse = $this->ensurePayrollMonthsAreEditable([(string) $item->salary_month], __('Advance salary records'))) {
            return $lockResponse;
        }
        if ($this->checkSalaryPaidAlready($item->employee_id, $item->salary_month)) {
            return $this->sendResponse(false, __('Advance salary paid already exists. so you can not delete it'), [], 400);
        }
        $this->tenantPayrollAdvanceSalaryRepository->delete($id);

        return $this->sendResponse(true, __('Advance salary deleted successfully'));
    }

    protected function resolveTenantFromRequest(Request $request): ?Tenant
    {
        $tenant = $request->attributes->get('tenant');
        return $tenant instanceof Tenant ? $tenant : null;
    }

    protected function attachReferenceDataToAdvanceSalaryList(array &$data): void
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
            $this->applyReferenceDataToAdvanceSalary($item, $employeeMap, $userMap);
        }
    }

    protected function attachReferenceDataToAdvanceSalary(TenantPayrollAdvanceSalary $item): void
    {
        $employeeMap = $this->resolveEmployeeMap([(int) $item->employee_id]);
        $userMap = $this->resolveUserMap([(int) $item->added_by]);
        $this->applyReferenceDataToAdvanceSalary($item, $employeeMap, $userMap);
    }

    protected function applyReferenceDataToAdvanceSalary(TenantPayrollAdvanceSalary $item, array $employeeMap, array $userMap): void
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
