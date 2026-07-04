<?php

namespace App\Http\Services\TenantPayrollLoan;

use App\Http\Requests\TenantApi\TenantPayrollLoanCreateRequest;
use Illuminate\Http\Request;

interface TenantPayrollLoanServiceInterface
{
    public function loanList(Request $request): array;

    public function storeLoan(TenantPayrollLoanCreateRequest $request): array;

    public function loanDetails(Request $request, int $id): array;

    public function deleteLoan(Request $request, int $id): array;
}
