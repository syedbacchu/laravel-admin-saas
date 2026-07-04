<?php

namespace App\Http\Services\TenantPayrollBonus;

use App\Http\Repositories\BaseRepository;
use App\Models\TenantPayrollBonus;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class TenantPayrollBonusRepository extends BaseRepository implements TenantPayrollBonusRepositoryInterface
{
    public function __construct(TenantPayrollBonus $model)
    {
        parent::__construct($model);
    }

    public function bonusList(Request $request): array
    {
        return DataListManager::list(
            request: $request,
            query: TenantPayrollBonus::query(),
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
                'bonus_amount',
                'salary_month',
                'status',
                'created_at',
                'updated_at',
            ],
        );
    }

    public function createBonus(array $data): Model
    {
        return $this->create($data);
    }

    public function findBonus(int $id): ?TenantPayrollBonus
    {
        return TenantPayrollBonus::query()->find($id);
    }
}
