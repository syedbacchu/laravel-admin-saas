<?php

namespace App\Http\Services\TenantPayrollGenerateSalary;

use App\Http\Requests\TenantApi\TenantPayrollGenerateSalaryCreateRequest;
use App\Http\Services\BaseService;
use App\Models\Tenant;
use App\Models\TenantEmployee;
use App\Models\TenantPayrollAdvanceSalary;
use App\Models\TenantPayrollAttendance;
use App\Models\TenantPayrollBonus;
use App\Models\TenantPayrollGeneratedSalary;
use App\Models\TenantPayrollLoan;
use App\Models\TenantPayrollSalarySheet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class TenantPayrollGenerateSalaryService extends BaseService implements TenantPayrollGenerateSalaryServiceInterface
{
    protected TenantPayrollGenerateSalaryRepositoryInterface $tenantPayrollGenerateSalaryRepository;

    public function __construct(TenantPayrollGenerateSalaryRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->tenantPayrollGenerateSalaryRepository = $repository;
    }

    public function generatedSalaryList(Request $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $data = $this->tenantPayrollGenerateSalaryRepository->generatedSalaryList($request);
        $this->attachReferenceDataToGeneratedSalaryList($data);
        $this->attachSalarySheetDataToGeneratedSalaryList($data);

        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function storeGeneratedSalary(TenantPayrollGenerateSalaryCreateRequest $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        try {
            $userId = (int) ($request->user()?->id ?? 0);
            $data = [
                'added_by' => $userId > 0 ? $userId : null,
                'generate_date' => $request->generate_date,
                'month' => (string) $request->month,
                'generated_by' => $userId > 0 ? $userId : null,
                'status' => (int) ($request->status ?? 1),
            ];

            if ($request->edit_id) {
                $item = $this->tenantPayrollGenerateSalaryRepository->findGeneratedSalary((int) $request->edit_id);
                if (!$item) {
                    return $this->sendResponse(false, __('Generated salary not found'), [], 404);
                }

                $this->tenantPayrollGenerateSalaryRepository->update((int) $item->id, $data);
                $item = $this->tenantPayrollGenerateSalaryRepository->findGeneratedSalary((int) $item->id);
                if (!$item) {
                    return $this->sendResponse(false, __('Generated salary not found'), [], 404);
                }

                $this->refreshSalarySheetRows((int) $item->id, (string) $item->month, $userId);
                $this->attachReferenceDataToGeneratedSalary($item);
                $this->attachSalarySheetData($item);

                return $this->sendResponse(true, __('Salary sheet updated successfully'), $item);
            }

            $item = $this->tenantPayrollGenerateSalaryRepository->createGeneratedSalary($data);
            $item = $this->tenantPayrollGenerateSalaryRepository->findGeneratedSalary((int) $item->id);
            if (!$item) {
                return $this->sendResponse(false, __('Generated salary not found'), [], 404);
            }

            $this->refreshSalarySheetRows((int) $item->id, (string) $item->month, $userId);
            $this->attachReferenceDataToGeneratedSalary($item);
            $this->attachSalarySheetData($item);

            return $this->sendResponse(true, __('Salary sheet generated successfully'), $item);
        } catch (Throwable $e) {
            logStore('TenantPayrollGenerateSalaryService storeGeneratedSalary', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong'), [], 500, $e->getMessage());
        }
    }

    public function generatedSalaryDetails(Request $request, int $id): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $item = $this->tenantPayrollGenerateSalaryRepository->findGeneratedSalary($id);
        if (!$item) {
            return $this->sendResponse(false, __('Generated salary not found'), [], 404);
        }

        $this->attachReferenceDataToGeneratedSalary($item);
        $this->attachSalarySheetData($item);

        return $this->sendResponse(true, __('Data get successfully.'), $item);
    }

    public function deleteGeneratedSalary(Request $request, int $id): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $item = $this->tenantPayrollGenerateSalaryRepository->findGeneratedSalary($id);
        if (!$item) {
            return $this->sendResponse(false, __('Generated salary not found'), [], 404);
        }

        $this->tenantPayrollGenerateSalaryRepository->deleteSalarySheetRows($id);
        $this->tenantPayrollGenerateSalaryRepository->delete($id);

        return $this->sendResponse(true, __('Generated salary deleted successfully'));
    }

    public function salarySheet(Request $request, int $id): array
    {
        return $this->generatedSalaryDetails($request, $id);
    }

    protected function resolveTenantFromRequest(Request $request): ?Tenant
    {
        $tenant = $request->attributes->get('tenant');
        return $tenant instanceof Tenant ? $tenant : null;
    }

    protected function refreshSalarySheetRows(int $generatedSalaryId, string $month, int $actorId): void
    {
        $this->tenantPayrollGenerateSalaryRepository->deleteSalarySheetRows($generatedSalaryId);
        $rows = $this->buildSalarySheetRows($generatedSalaryId, $month, $actorId);
        $this->tenantPayrollGenerateSalaryRepository->insertSalarySheetRows($rows);
    }

    protected function buildSalarySheetRows(int $generatedSalaryId, string $month, int $actorId): array
    {
        // Get all types of employees (employee, helper, supervisor) directly from database
        $allEmployees = DB::connection('tenant')
            ->table('employees')
            ->where('status', 1)
            ->get([
                'id',
                'employee_type',
                'name',
                'designation',
                'basic_salary',
                'house_rent',
                'medical',
                'allowance',
                'extra_allowance',
                'conveyance',
                'gross_salary',
            ]);

        if ($allEmployees->isEmpty()) {
            // Try without status filter
            $allEmployees = DB::connection('tenant')
                ->table('employees')
                ->get([
                    'id',
                    'employee_type',
                    'name',
                    'designation',
                    'basic_salary',
                    'house_rent',
                    'medical',
                    'allowance',
                    'extra_allowance',
                    'conveyance',
                    'gross_salary',
                ]);
        }

        if ($allEmployees->isEmpty()) {
            return [];
        }

        $employeeIds = $allEmployees->pluck('id')->map(fn ($id) => (int) $id)->all();

        $attendanceMap = TenantPayrollAttendance::query()
            ->where('month', $month)
            ->whereIn('employee_id', $employeeIds)
            ->pluck('working_day', 'employee_id')
            ->toArray();

        $bonusMap = TenantPayrollBonus::query()
            ->where('salary_month', $month)
            ->whereIn('employee_id', $employeeIds)
            ->groupBy('employee_id')
            ->select('employee_id', DB::raw('SUM(bonus_amount) as total_bonus'))
            ->pluck('total_bonus', 'employee_id')
            ->toArray();

        $advanceMap = TenantPayrollAdvanceSalary::query()
            ->where('salary_month', $month)
            ->whereIn('employee_id', $employeeIds)
            ->groupBy('employee_id')
            ->select('employee_id', DB::raw('SUM(COALESCE(after_adjustment_amount, advance_amount)) as total_advance'))
            ->pluck('total_advance', 'employee_id')
            ->toArray();

        $loanMap = TenantPayrollLoan::query()
            ->whereIn('employee_id', $employeeIds)
            ->where('status', 'pending') // Only include active loans
            ->groupBy('employee_id')
            ->select('employee_id', DB::raw('SUM(monthly_deduction) as total_loan'))
            ->pluck('total_loan', 'employee_id')
            ->toArray();

        // Calculate previous month's due amounts
        $previousMonthDueMap = [];
        try {
            $previousMonthDate = date('Y-m', strtotime($month . '-01 -1 month'));
            $previousMonthDueSheets = TenantPayrollSalarySheet::query()
                ->whereIn('employee_id', $employeeIds)
                ->whereHas('generatedSalary', function ($query) use ($previousMonthDate) {
                    $query->where('month', $previousMonthDate);
                })
                ->select('employee_id', DB::raw('SUM(due_amount) as total_due'))
                ->groupBy('employee_id')
                ->pluck('total_due', 'employee_id')
                ->toArray();

            $previousMonthDueMap = $previousMonthDueSheets;
        } catch (\Exception $e) {
            logStore('Error calculating previous month due', $e->getMessage());
            $previousMonthDueMap = [];
        }

        $rows = [];
        $now = now();

        foreach ($allEmployees as $employee) {
            $employeeId = (int) $employee->id;

            $workingDay = (int) ($attendanceMap[$employeeId] ?? 0);
            $basicSalary = $this->toMoney($employee->basic_salary);
            $houseRent = $this->toMoney($employee->house_rent);
            $medical = $this->toMoney($employee->medical);
            $allowance = $this->toMoney($employee->allowance);
            $extra_allowance = $this->toMoney($employee->extra_allowance);
            $conveyance = $this->toMoney($employee->conveyance);
            $gross_salary = $this->toMoney($employee->gross_salary);
            $bonus = $this->toMoney($bonusMap[$employeeId] ?? 0);

            $totalEarnings = $this->toMoney($gross_salary + $bonus);

            $advanceDeduction = $this->toMoney($advanceMap[$employeeId] ?? 0);
            $loanDeduction = $this->toMoney($loanMap[$employeeId] ?? 0);
            $previousMonthDue = $this->toMoney($previousMonthDueMap[$employeeId] ?? 0);
            $totalDeduction = $this->toMoney($advanceDeduction + $loanDeduction);
            $netPayable = $this->toMoney($totalEarnings + $previousMonthDue - $totalDeduction);

            $rows[] = [
                'added_by' => $actorId > 0 ? $actorId : null,
                'updated_by' => $actorId > 0 ? $actorId : null,
                'generated_salary_id' => $generatedSalaryId,
                'employee_id' => $employeeId,
                'working_day' => $workingDay,
                'designation' => $employee->designation,
                'basic_salary' => $basicSalary,
                'house_rent' => $houseRent,
                'extra_allowance' => $extra_allowance,
                'conveyance' => $conveyance,
                'medical' => $medical,
                'allowance' => $allowance,
                'gross_salary' => $gross_salary,
                'bonus' => $bonus,
                'total_earnings' => $totalEarnings,
                'advance_deduction' => $advanceDeduction,
                'loan_deduction' => $loanDeduction,
                'total_deduction' => $totalDeduction,
                'net_payable' => $netPayable,
                'paid_amount' => 0,
                'due_amount' => $netPayable, // Initially due amount equals net payable
                'payment_status' => 'unpaid',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return $rows;
    }

    protected function attachReferenceDataToGeneratedSalaryList(array &$data): void
    {
        if (!isset($data['data']) || !is_iterable($data['data'])) {
            return;
        }

        $userIds = [];
        foreach ($data['data'] as $item) {
            $userIds[] = (int) $item->generated_by;
            $userIds[] = (int) $item->added_by;
        }

        $userMap = $this->resolveUserMap($userIds);

        foreach ($data['data'] as $item) {
            $this->applyReferenceDataToGeneratedSalary($item, $userMap);
        }
    }

    protected function attachReferenceDataToGeneratedSalary(TenantPayrollGeneratedSalary $item): void
    {
        $userMap = $this->resolveUserMap([(int) $item->generated_by, (int) $item->added_by]);
        $this->applyReferenceDataToGeneratedSalary($item, $userMap);
    }

    protected function applyReferenceDataToGeneratedSalary(TenantPayrollGeneratedSalary $item, array $userMap): void
    {
        $generatedBy = (int) $item->generated_by;
        $addedBy = (int) $item->added_by;

        $item->setAttribute('generated_by_user', $userMap[$generatedBy] ?? null);
        $item->setAttribute('created_by_user', $userMap[$addedBy] ?? null);
    }

    protected function attachSalarySheetData(TenantPayrollGeneratedSalary $item): void
    {
        $rows = $this->tenantPayrollGenerateSalaryRepository->salarySheetRows((int) $item->id);
        
        $employeeIds = $rows->pluck('employee_id')->map(fn ($id) => (int) $id)->all();
        $employeeMap = $this->resolveEmployeeMap($employeeIds);

        $grandTotalEarnings = 0.0;
        $grandTotalDeduction = 0.0;
        $grandTotalNetPayable = 0.0;

        foreach ($rows as $row) {
            if (!$row instanceof TenantPayrollSalarySheet) {
                continue;
            }

            $employeeId = (int) $row->employee_id;
            $employeeData = $employeeMap[$employeeId] ?? null;
            $row->setAttribute('employee', $employeeData);
            $row->setAttribute('employee_type', $employeeData['employee_type'] ?? 'employee');

            $grandTotalEarnings += (float) $row->total_earnings;
            $grandTotalDeduction += (float) $row->total_deduction;
            $grandTotalNetPayable += (float) $row->net_payable;
        }

        $item->setAttribute('salary_sheet', $rows);
        $item->setAttribute('summary', [
            'total_employee' => (int) $rows->count(),
            'grand_total_earnings' => $this->toMoney($grandTotalEarnings),
            'grand_total_deduction' => $this->toMoney($grandTotalDeduction),
            'grand_total_net_payable' => $this->toMoney($grandTotalNetPayable),
        ]);
    }

    protected function attachSalarySheetDataToGeneratedSalaryList(array &$data): void
    {
        if (!isset($data['data']) || !is_iterable($data['data'])) {
            return;
        }

        foreach ($data['data'] as $item) {
            if ($item instanceof TenantPayrollGeneratedSalary) {
                $this->attachSalarySheetData($item);
            }
        }
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

    protected function resolveEmployeeMap(array $employeeIds): array
    {
        $employeeIds = array_values(array_unique(array_filter(array_map('intval', $employeeIds))));
        if (empty($employeeIds)) {
            return [];
        }

        $employees = DB::connection('tenant')
            ->table('employees')
            ->whereIn('id', $employeeIds)
            ->get(['id', 'name', 'mobile', 'designation', 'status', 'employee_type']);

        $map = [];
        foreach ($employees as $employee) {
            $map[(int) $employee->id] = [
                'id' => (int) $employee->id,
                'name' => (string) $employee->name,
                'mobile' => $employee->mobile,
                'designation' => $employee->designation,
                'status' => (int) $employee->status,
                'employee_type' => (string) $employee->employee_type,
            ];
        }

        return $map;
    }

    protected function toMoney(mixed $value): float
    {
        return round((float) $value, 2);
    }
}
