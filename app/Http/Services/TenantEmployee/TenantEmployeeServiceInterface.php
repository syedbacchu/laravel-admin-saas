<?php

namespace App\Http\Services\TenantEmployee;

use App\Http\Requests\TenantApi\TenantEmployeeCreateRequest;
use Illuminate\Http\Request;

interface TenantEmployeeServiceInterface
{
    public function employeeList(Request $request): array;
    public function storeEmployee(TenantEmployeeCreateRequest $request): array;
    public function employeeDetails(Request $request, int $id): array;
    public function deleteEmployee(Request $request, int $id): array;
    public function allActiveEmployees(Request $request): array;
}
