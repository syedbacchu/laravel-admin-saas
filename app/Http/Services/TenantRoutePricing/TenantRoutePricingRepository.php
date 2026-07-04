<?php

namespace App\Http\Services\TenantRoutePricing;

use App\Http\Repositories\BaseRepository;
use App\Models\TenantRoutePricing;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class TenantRoutePricingRepository extends BaseRepository implements TenantRoutePricingRepositoryInterface
{
    public function __construct(TenantRoutePricing $model)
    {
        parent::__construct($model);
    }

    public function routePricingList(Request $request): array
    {
        return DataListManager::list(
            request: $request,
            query: TenantRoutePricing::query(),
            searchable: [
                'rate',
                'load_area_name',
                'load_area_address',
            ],
            filters: [
                'status' => [
                    'column' => 'status',
                ],
                'customer_id' => [
                    'column' => 'customer_id',
                ],
                'vehicle_category_id' => [
                    'column' => 'vehicle_category_id',
                ],
                'load_area_id' => [
                    'column' => 'load_area_id',
                ],
                'load_area_name' => [
                    'column' => 'load_area_name',
                ],
                'unload_area_id' => [
                    'column' => 'unload_area_id',
                ],
                'vehicle_size_id' => [
                    'column' => 'vehicle_size_id',
                ],
            ],
            select: [
                'id',
                'customer_id',
                'vehicle_category_id',
                'load_area_id',
                'load_area_name',
                'load_area_address',
                'unload_area_id',
                'vehicle_size_id',
                'distance',
                'rate',
                'status',
                'created_at',
                'updated_at',
            ],
        );
    }

    public function createRoutePricing(array $data): Model
    {
        return $this->create($data);
    }

    public function findRoutePricing(int $id): ?TenantRoutePricing
    {
        return TenantRoutePricing::query()->find($id);
    }
}
