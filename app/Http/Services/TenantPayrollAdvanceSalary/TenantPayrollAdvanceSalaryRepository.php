<?php

namespace App\Http\Services\TenantPayrollAdvanceSalary;

use App\Http\Repositories\BaseRepository;
use App\Models\Tenant\TenantPayrollAdvanceSalary;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class TenantPayrollAdvanceSalaryRepository extends BaseRepository implements TenantPayrollAdvanceSalaryRepositoryInterface
{
    public function __construct(TenantPayrollAdvanceSalary $model)
    {
        parent::__construct($model);
    }

    public function advanceSalaryList(Request $request): array
    {
        return DataListManager::list(
            request: $request,
            query: TenantPayrollAdvanceSalary::query(),
            searchable: [
                'salary_month',
                'status',
            ],
            filters: [
                'employee_id' => [
                    'column' => 'employee_id',
                ],
                'salary_month' => [
                    'column' => 'salary_month',
                ],
                'status' => [
                    'column' => 'status',
                ],
                'date' => [
                    'column' => 'date',
                    'type' => 'date',
                ],
            ],
            select: [
                'id',
                'added_by',
                'updated_by',
                'date',
                'employee_id',
                'advance_amount',
                'salary_month',
                'after_adjustment_amount',
                'status',
                'created_at',
                'updated_at',
            ],
        );
    }

    public function createAdvanceSalary(array $data): Model
    {
        return $this->create($data);
    }

    public function findAdvanceSalary(int $id): ?TenantPayrollAdvanceSalary
    {
        return TenantPayrollAdvanceSalary::query()->find($id);
    }
}
