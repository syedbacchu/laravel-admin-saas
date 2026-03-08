<?php

namespace App\Http\Services\TenantVehicle;

use App\Http\Repositories\BaseRepository;
use App\Models\TenantVehicle;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class TenantVehicleRepository extends BaseRepository implements TenantVehicleRepositoryInterface
{
    public function __construct(TenantVehicle $model)
    {
        parent::__construct($model);
    }

    public function vehicleList(Request $request): array
    {
        return DataListManager::list(
            request: $request,
            query: TenantVehicle::query()->withCount('drivers'),
            searchable: [
                'registration_no',
                'vehicle_type',
                'brand',
                'model',
            ],
            filters: [
                'status' => [
                    'column' => 'status',
                ],
            ],
            select: [
                'id',
                'registration_no',
                'vehicle_type',
                'brand',
                'model',
                'manufacturing_year',
                'color',
                'notes',
                'status',
                'created_at',
                'updated_at',
            ],
        );
    }

    public function createVehicle(array $data): Model
    {
        return $this->create($data);
    }

    public function findVehicle(int $id): ?TenantVehicle
    {
        return TenantVehicle::query()->withCount('drivers')->find($id);
    }

    public function totalVehicle(): int
    {
        return TenantVehicle::query()->count();
    }
}
