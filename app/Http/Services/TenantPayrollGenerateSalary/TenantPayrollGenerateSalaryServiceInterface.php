<?php

namespace App\Http\Services\TenantPayrollGenerateSalary;

use App\Http\Requests\TenantApi\TenantPayrollGenerateSalaryCreateRequest;
use Illuminate\Http\Request;

interface TenantPayrollGenerateSalaryServiceInterface
{
    public function generatedSalaryList(Request $request): array;

    public function storeGeneratedSalary(TenantPayrollGenerateSalaryCreateRequest $request): array;

    public function generatedSalaryDetails(Request $request, int $id): array;

    public function deleteGeneratedSalary(Request $request, int $id): array;

    public function salarySheet(Request $request, int $id): array;
}
