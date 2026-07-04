<?php

namespace App\Http\Services\TenantDailyOfficeExpense;

use App\Http\Requests\TenantApi\TenantDailyOfficeExpenseCreateRequest;
use Illuminate\Http\Request;

interface TenantDailyOfficeExpenseServiceInterface
{
    public function dailyOfficeExpenseList(Request $request): array;
    public function storeDailyOfficeExpense(TenantDailyOfficeExpenseCreateRequest $request): array;
    public function dailyOfficeExpenseDetails(Request $request, int $id): array;
    public function deleteDailyOfficeExpense(Request $request, int $id): array;
}

