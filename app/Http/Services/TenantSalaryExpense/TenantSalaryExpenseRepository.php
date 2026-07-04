<?php

namespace App\Http\Services\TenantSalaryExpense;

use App\Http\Repositories\BaseRepository;
use App\Models\TenantSalaryExpense;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class TenantSalaryExpenseRepository extends BaseRepository implements TenantSalaryExpenseRepositoryInterface
{
    public function __construct(TenantSalaryExpense $model)
    {
        parent::__construct($model);
    }

    public function salaryExpenseList(Request $request): array
    {
        return DataListManager::list(
            request: $request,
            query: TenantSalaryExpense::query(),
            searchable: [
                'category',
                'remarks',
            ],
            filters: [
                'status' => [
                    'column' => 'status',
                ],
                'office_id' => [
                    'column' => 'office_id',
                ],
                'salary_month' => [
                    'column' => 'salary_month',
                ],
                'date' => [
                    'column' => 'date',
                    'type' => 'date',
                ],
                'category' => [
                    'column' => 'category',
                ],
                'employee_id' => [
                    'column' => 'paid_to_user_id',
                ],
            ],
            select: [
                'id',
                'date',
                'salary_month',
                'paid_to_user_id',
                'category',
                'office_id',
                'amount',
                'remarks',
                'attachment',
                'status',
                'created_at',
                'updated_at',
            ],
        );
    }

    public function createSalaryExpense(array $data): Model
    {
        return $this->create($data);
    }

    public function findSalaryExpense(int $id): ?TenantSalaryExpense
    {
        return TenantSalaryExpense::query()->find($id);
    }
}
