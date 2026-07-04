<?php

namespace App\Http\Services\TenantSalaryExpense;

use App\Http\Repositories\BaseRepositoryInterface;
use App\Models\Tenant\TenantSalaryExpense;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface TenantSalaryExpenseRepositoryInterface extends BaseRepositoryInterface
{
    public function salaryExpenseList(Request $request): array;
    public function createSalaryExpense(array $data): Model;
    public function findSalaryExpense(int $id): ?TenantSalaryExpense;
}

