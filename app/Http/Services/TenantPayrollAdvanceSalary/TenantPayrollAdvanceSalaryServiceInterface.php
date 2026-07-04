<?php

namespace App\Http\Services\TenantPayrollAdvanceSalary;

use App\Http\Requests\TenantApi\TenantPayrollAdvanceSalaryCreateRequest;
use Illuminate\Http\Request;

interface TenantPayrollAdvanceSalaryServiceInterface
{
    public function advanceSalaryList(Request $request): array;

    public function storeAdvanceSalary(TenantPayrollAdvanceSalaryCreateRequest $request): array;

    public function advanceSalaryDetails(Request $request, int $id): array;

    public function deleteAdvanceSalary(Request $request, int $id): array;
}
