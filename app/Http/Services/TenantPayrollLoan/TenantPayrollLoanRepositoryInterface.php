<?php

namespace App\Http\Services\TenantPayrollLoan;

use App\Http\Repositories\BaseRepositoryInterface;
use App\Models\Tenant\TenantPayrollLoan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface TenantPayrollLoanRepositoryInterface extends BaseRepositoryInterface
{
    public function loanList(Request $request): array;

    public function createLoan(array $data): Model;

    public function findLoan(int $id): ?TenantPayrollLoan;
}
