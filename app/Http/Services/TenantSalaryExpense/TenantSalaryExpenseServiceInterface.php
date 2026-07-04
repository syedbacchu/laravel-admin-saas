<?php

namespace App\Http\Services\TenantSalaryExpense;

use App\Http\Requests\TenantApi\TenantSalaryExpenseCreateRequest;
use Illuminate\Http\Request;

interface TenantSalaryExpenseServiceInterface
{
    public function salaryExpenseList(Request $request): array;
    public function storeSalaryExpense(TenantSalaryExpenseCreateRequest $request): array;
    public function salaryExpenseDetails(Request $request, int $id): array;
    public function deleteSalaryExpense(Request $request, int $id): array;
    public function calculatePayableAmount(Request $request): array;
}

