<?php

namespace App\Http\Services\TenantPayrollBonus;

use App\Http\Requests\TenantApi\TenantPayrollBonusCreateRequest;
use App\Http\Services\BaseService;
use App\Models\Tenant;
use App\Models\Tenant\TenantAllEmployee;
use App\Models\Tenant\TenantPayrollBonus;
use App\Models\User;
use App\Traits\BlocksLockedPayrollMonths;
use Illuminate\Http\Request;
use Throwable;

class TenantPayrollBonusService extends BaseService implements TenantPayrollBonusServiceInterface
{
    use BlocksLockedPayrollMonths;

    protected TenantPayrollBonusRepositoryInterface $tenantPayrollBonusRepository;

    public function __construct(TenantPayrollBonusRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->tenantPayrollBonusRepository = $repository;
    }

    public function bonusList(Request $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $data = $this->tenantPayrollBonusRepository->bonusList($request);
        $this->attachReferenceDataToBonusList($data);

        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function storeBonus(TenantPayrollBonusCreateRequest $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        try {
            $data = [
                'date' => $request->date,
                'employee_id' => (int) $request->employee_id,
                'bonus_amount' => $request->bonus_amount,
                'salary_month' => (string) $request->salary_month,
                'status' => (string) $request->status,
            ];

            if ($request->edit_id) {
                $item = $this->tenantPayrollBonusRepository->findBonus((int) $request->edit_id);
                if (!$item) {
                    return $this->sendResponse(false, __('Bonus not found'), [], 404);
                }
                if ($lockResponse = $this->ensurePayrollMonthsAreEditable([(string) $item->salary_month, (string) $data['salary_month']], __('Bonus records'))) {
                    return $lockResponse;
                }

                $this->tenantPayrollBonusRepository->update((int) $item->id, $data);
                $item = $this->tenantPayrollBonusRepository->findBonus((int) $item->id);
                if (!$item) {
                    return $this->sendResponse(false, __('Bonus not found'), [], 404);
                }

                $this->attachReferenceDataToBonus($item);

                return $this->sendResponse(true, __('Bonus updated successfully'), $item);
            }

            if ($lockResponse = $this->ensurePayrollMonthsAreEditable([(string) $data['salary_month']], __('Bonus records'))) {
                return $lockResponse;
            }

            $item = $this->tenantPayrollBonusRepository->createBonus($data);
            $item = $this->tenantPayrollBonusRepository->findBonus((int) $item->id);
            if (!$item) {
                return $this->sendResponse(false, __('Bonus not found'), [], 404);
            }

            $this->attachReferenceDataToBonus($item);

            return $this->sendResponse(true, __('Bonus created successfully'), $item);
        } catch (Throwable $e) {
            logStore('TenantPayrollBonusService storeBonus', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong'), [], 500, $e->getMessage());
        }
    }

    public function bonusDetails(Request $request, int $id): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $item = $this->tenantPayrollBonusRepository->findBonus($id);
        if (!$item) {
            return $this->sendResponse(false, __('Bonus not found'), [], 404);
        }

        $this->attachReferenceDataToBonus($item);

        return $this->sendResponse(true, __('Data get successfully.'), $item);
    }

    public function deleteBonus(Request $request, int $id): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $item = $this->tenantPayrollBonusRepository->findBonus($id);
        if (!$item) {
            return $this->sendResponse(false, __('Bonus not found'), [], 404);
        }
        if ($lockResponse = $this->ensurePayrollMonthsAreEditable([(string) $item->salary_month], __('Bonus records'))) {
            return $lockResponse;
        }

        $this->tenantPayrollBonusRepository->delete($id);

        return $this->sendResponse(true, __('Bonus deleted successfully'));
    }

    protected function resolveTenantFromRequest(Request $request): ?Tenant
    {
        $tenant = $request->attributes->get('tenant');
        return $tenant instanceof Tenant ? $tenant : null;
    }

    protected function attachReferenceDataToBonusList(array &$data): void
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
            $this->applyReferenceDataToBonus($item, $employeeMap, $userMap);
        }
    }

    protected function attachReferenceDataToBonus(TenantPayrollBonus $item): void
    {
        $employeeMap = $this->resolveEmployeeMap([(int) $item->employee_id]);
        $userMap = $this->resolveUserMap([(int) $item->added_by]);
        $this->applyReferenceDataToBonus($item, $employeeMap, $userMap);
    }

    protected function applyReferenceDataToBonus(TenantPayrollBonus $item, array $employeeMap, array $userMap): void
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
