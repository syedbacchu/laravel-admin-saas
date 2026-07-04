<?php

namespace App\Http\Services\TenantDailyOfficeExpense;

use App\Http\Repositories\BaseRepository;
use App\Models\TenantDailyOfficeExpense;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class TenantDailyOfficeExpenseRepository extends BaseRepository implements TenantDailyOfficeExpenseRepositoryInterface
{
    public function __construct(TenantDailyOfficeExpense $model)
    {
        parent::__construct($model);
    }

    public function dailyOfficeExpenseList(Request $request): array
    {
        return DataListManager::list(
            request: $request,
            query: TenantDailyOfficeExpense::query(),
            searchable: [
                'paid_to',
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
                'date' => [
                    'column' => 'date',
                    'type' => 'date',
                ],
                'category' => [
                    'column' => 'category',
                ],
            ],
            select: [
                'id',
                'date',
                'paid_to',
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

    public function createDailyOfficeExpense(array $data): Model
    {
        return $this->create($data);
    }

    public function findDailyOfficeExpense(int $id): ?TenantDailyOfficeExpense
    {
        return TenantDailyOfficeExpense::query()->find($id);
    }
}
