<?php

namespace App\Http\Services\TenantPayrollAdvanceSalary;

use App\Http\Repositories\BaseRepositoryInterface;
use App\Models\Tenant\TenantPayrollAdvanceSalary;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface TenantPayrollAdvanceSalaryRepositoryInterface extends BaseRepositoryInterface
{
    public function advanceSalaryList(Request $request): array;

    public function createAdvanceSalary(array $data): Model;

    public function findAdvanceSalary(int $id): ?TenantPayrollAdvanceSalary;
}
