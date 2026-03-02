<?php

namespace App\Http\Services\Billing;

use App\Http\Repositories\BaseRepository;
use App\Models\Plan;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class PlanRepository extends BaseRepository implements PlanRepositoryInterface
{
    public function __construct(Plan $model)
    {
        parent::__construct($model);
    }

    public function planList(Request $request): array
    {
        return DataListManager::list(
            request: $request,
            query: Plan::query(),
            searchable: [
                'name',
                'slug',
                'subtitle',
            ],
            filters: [
                'is_active' => [
                    'column' => 'is_active',
                ],
            ],
            select: [
                'id',
                'name',
                'slug',
                'subtitle',
                'sort_order',
                'is_active',
                'created_at',
            ],
        );
    }

    public function createPlan(array $data): Model
    {
        return $this->create($data);
    }
}
