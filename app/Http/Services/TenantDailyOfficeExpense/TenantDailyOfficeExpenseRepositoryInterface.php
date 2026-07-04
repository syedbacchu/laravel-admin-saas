<?php

namespace App\Http\Services\TenantDailyOfficeExpense;

use App\Http\Repositories\BaseRepositoryInterface;
use App\Models\Tenant\TenantDailyOfficeExpense;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface TenantDailyOfficeExpenseRepositoryInterface extends BaseRepositoryInterface
{
    public function dailyOfficeExpenseList(Request $request): array;
    public function createDailyOfficeExpense(array $data): Model;
    public function findDailyOfficeExpense(int $id): ?TenantDailyOfficeExpense;
}

