<?php

namespace App\Http\Services\TenantPayrollAttendance;

use App\Http\Requests\TenantApi\TenantPayrollAttendanceCreateRequest;
use App\Http\Services\BaseService;
use App\Models\Tenant;
use App\Models\Tenant\TenantAllEmployee;
use App\Models\Tenant\TenantPayrollAttendance;
use App\Models\User;
use App\Traits\BlocksLockedPayrollMonths;
use Illuminate\Http\Request;
use Throwable;

class TenantPayrollAttendanceService extends BaseService implements TenantPayrollAttendanceServiceInterface
{
    use BlocksLockedPayrollMonths;

    protected TenantPayrollAttendanceRepositoryInterface $tenantPayrollAttendanceRepository;

    public function __construct(TenantPayrollAttendanceRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->tenantPayrollAttendanceRepository = $repository;
    }

    public function attendanceList(Request $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $data = $this->tenantPayrollAttendanceRepository->attendanceList($request);
        $this->attachReferenceDataToAttendanceList($data);

        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function storeAttendance(TenantPayrollAttendanceCreateRequest $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        try {
            $data = [
                'date' => $request->date,
                'employee_id' => (int) $request->employee_id,
                'working_day' => (int) $request->working_day,
                'month' => (string) $request->month,
                'status' => (int) ($request->status ?? 1),
            ];

            if ($request->edit_id) {
                $item = $this->tenantPayrollAttendanceRepository->findAttendance((int) $request->edit_id);
                if (!$item) {
                    return $this->sendResponse(false, __('Attendance not found'), [], 404);
                }
                if ($lockResponse = $this->ensurePayrollMonthsAreEditable([(string) $item->month, (string) $data['month']], __('Attendance records'))) {
                    return $lockResponse;
                }

                $this->tenantPayrollAttendanceRepository->update((int) $item->id, $data);
                $item = $this->tenantPayrollAttendanceRepository->findAttendance((int) $item->id);
                if (!$item) {
                    return $this->sendResponse(false, __('Attendance not found'), [], 404);
                }

                $this->attachReferenceDataToAttendance($item);

                return $this->sendResponse(true, __('Attendance updated successfully'), $item);
            }

            if ($lockResponse = $this->ensurePayrollMonthsAreEditable([(string) $data['month']], __('Attendance records'))) {
                return $lockResponse;
            }

            $item = $this->tenantPayrollAttendanceRepository->createAttendance($data);
            $item = $this->tenantPayrollAttendanceRepository->findAttendance((int) $item->id);
            if (!$item) {
                return $this->sendResponse(false, __('Attendance not found'), [], 404);
            }

            $this->attachReferenceDataToAttendance($item);

            return $this->sendResponse(true, __('Attendance created successfully'), $item);
        } catch (Throwable $e) {
            logStore('TenantPayrollAttendanceService storeAttendance', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong'), [], 500, $e->getMessage());
        }
    }

    public function attendanceDetails(Request $request, int $id): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $item = $this->tenantPayrollAttendanceRepository->findAttendance($id);
        if (!$item) {
            return $this->sendResponse(false, __('Attendance not found'), [], 404);
        }

        $this->attachReferenceDataToAttendance($item);

        return $this->sendResponse(true, __('Data get successfully.'), $item);
    }

    public function deleteAttendance(Request $request, int $id): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $item = $this->tenantPayrollAttendanceRepository->findAttendance($id);
        if (!$item) {
            return $this->sendResponse(false, __('Attendance not found'), [], 404);
        }

        $this->tenantPayrollAttendanceRepository->delete($id);

        return $this->sendResponse(true, __('Attendance deleted successfully'));
    }

    protected function resolveTenantFromRequest(Request $request): ?Tenant
    {
        $tenant = $request->attributes->get('tenant');
        return $tenant instanceof Tenant ? $tenant : null;
    }

    protected function attachReferenceDataToAttendanceList(array &$data): void
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
            $this->applyReferenceDataToAttendance($item, $employeeMap, $userMap);
        }
    }

    protected function attachReferenceDataToAttendance(TenantPayrollAttendance $item): void
    {
        $employeeMap = $this->resolveEmployeeMap([(int) $item->employee_id]);
        $userMap = $this->resolveUserMap([(int) $item->added_by]);
        $this->applyReferenceDataToAttendance($item, $employeeMap, $userMap);
    }

    protected function applyReferenceDataToAttendance(TenantPayrollAttendance $item, array $employeeMap, array $userMap): void
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
