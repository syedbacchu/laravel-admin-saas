<?php

namespace App\Http\Services\TenantEmployee;

use App\Http\Requests\TenantApi\TenantEmployeeCreateRequest;
use App\Http\Services\BaseService;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Throwable;

class TenantEmployeeService extends BaseService implements TenantEmployeeServiceInterface
{
    protected TenantEmployeeRepositoryInterface $tenantEmployeeRepository;

    public function __construct(TenantEmployeeRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->tenantEmployeeRepository = $repository;
    }

    public function employeeList(Request $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $data = $this->tenantEmployeeRepository->employeeList($request);
        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function storeEmployee(TenantEmployeeCreateRequest $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        try {
            // Calculate gross salary automatically
            $basicSalary = (float) ($request->basic_salary ?? 0);
            $houseRent = (float) ($request->house_rent ?? 0);
            $medical = (float) ($request->medical ?? 0);
            $allowance = (float) ($request->allowance ?? 0);
            $extraAllowance = (float) ($request->extra_allowance ?? 0);
            $conveyance = (float) ($request->conveyance ?? 0);
            $calculatedGrossSalary = $basicSalary + $houseRent + $medical + $allowance + $extraAllowance + $conveyance;

            $data = [
                'employee_type' => 'employee',
                'name' => (string) $request->name,
                'email' => $request->email,
                'mobile' => (string) $request->mobile,
                'gender' => (string) $request->gender,
                'blood_group' => (string) $request->blood_group,
                'birth_date' => $request->birth_date,
                'joining_date' => $request->join_date, // Map join_date to joining_date
                'nid' => (string) $request->nid,
                'designation' => (string) $request->designation,
                'address' => (string) $request->address,
                'basic_salary' => $request->basic_salary,
                'house_rent' => $request->house_rent,
                'medical' => $request->medical,
                'allowance' => $request->allowance,
                'extra_allowance' => $request->extra_allowance,
                'conveyance' => $request->conveyance,
                'gross_salary' => $calculatedGrossSalary,
                'image' => $request->image,
                'status' => (int) ($request->status ?? 1),
            ];

            if ($request->edit_id) {
                $item = $this->tenantEmployeeRepository->findEmployee((int) $request->edit_id);
                if (!$item) {
                    return $this->sendResponse(false, __('Employee not found'), [], 404);
                }

                $this->tenantEmployeeRepository->update((int) $item->id, $data);
                $item = $this->tenantEmployeeRepository->findEmployee((int) $item->id);

                return $this->sendResponse(true, __('Employee updated successfully'), $item);
            }

            $item = $this->tenantEmployeeRepository->createEmployee($data);

            return $this->sendResponse(
                true,
                __('Employee created successfully'),
                $this->tenantEmployeeRepository->findEmployee((int) $item->id)
            );
        } catch (Throwable $e) {
            logStore('TenantEmployeeService storeEmployee', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong'), [], 500, $e->getMessage());
        }
    }

    public function employeeDetails(Request $request, int $id): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $item = $this->tenantEmployeeRepository->findEmployee($id);
        if (!$item) {
            return $this->sendResponse(false, __('Employee not found'), [], 404);
        }

        return $this->sendResponse(true, __('Data get successfully.'), $item);
    }

    public function deleteEmployee(Request $request, int $id): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        $item = $this->tenantEmployeeRepository->findEmployee($id);
        if (!$item) {
            return $this->sendResponse(false, __('Employee not found'), [], 404);
        }

        $this->tenantEmployeeRepository->delete($id);

        return $this->sendResponse(true, __('Employee deleted successfully'));
    }

    public function allActiveEmployees(Request $request): array
    {
        $tenant = $this->resolveTenantFromRequest($request);
        if (!$tenant) {
            return $this->sendResponse(false, __('Tenant context is missing'), [], 422);
        }

        try {
            $data = $this->tenantEmployeeRepository->allEmployeesList($request);

            return $this->sendResponse(true, __('Data get successfully.'), $data);
        } catch (\Exception $e) {
            logStore('TenantEmployeeService allActiveEmployees', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong'), [], 500, $e->getMessage());
        }
    }

    protected function resolveTenantFromRequest(Request $request): ?Tenant
    {
        $tenant = $request->attributes->get('tenant');
        return $tenant instanceof Tenant ? $tenant : null;
    }
}
