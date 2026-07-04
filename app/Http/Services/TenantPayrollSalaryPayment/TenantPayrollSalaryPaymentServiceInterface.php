<?php

namespace App\Http\Services\TenantPayrollSalaryPayment;

use App\Http\Requests\TenantApi\TenantPayrollSalaryPaymentCreateRequest;
use App\Http\Services\BaseServiceInterface;
use Illuminate\Http\Request;


interface TenantPayrollSalaryPaymentServiceInterface extends BaseServiceInterface
{
    public function getPayableAmount(Request $request, int $salarySheetId): array;

    public function processPayment(TenantPayrollSalaryPaymentCreateRequest $request): array;

    public function getPaymentHistory(Request $request, int $salarySheetId): array;

    public function getEmployeePaymentHistory(Request $request, int $employeeId): array;
}
