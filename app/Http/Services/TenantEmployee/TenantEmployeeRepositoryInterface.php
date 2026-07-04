<?php

namespace App\Http\Services\TenantEmployee;

use App\Http\Repositories\BaseRepositoryInterface;
use App\Models\Tenant\TenantEmployee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface TenantEmployeeRepositoryInterface extends BaseRepositoryInterface
{
    public function employeeList(Request $request): array;
    public function allEmployeesList(Request $request): array;
    public function createEmployee(array $data): Model;
    public function findEmployee(int $id): ?TenantEmployee;
}
