<?php

namespace App\Http\Services\TenantDriver;

use App\Http\Repositories\BaseRepository;
use App\Models\TenantDriver;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class TenantDriverRepository extends BaseRepository implements TenantDriverRepositoryInterface
{
    public function __construct(TenantDriver $model)
    {
        parent::__construct($model);
    }

    public function driverList(Request $request): array
    {
        return DataListManager::list(
            request: $request,
            query: TenantDriver::query()->with('vehicle:id,registration_no,vehicle_type,brand,model'),
            searchable: [
                'name',
                'phone',
                'license_no',
                'nid_no',
            ],
            filters: [
                'status' => [
                    'column' => 'status',
                ],
                'vehicle_id' => [
                    'column' => 'vehicle_id',
                ],
            ],
            select: [
                'id',
                'vehicle_id',
                'name',
                'phone',
                'license_no',
                'nid_no',
                'joining_date',
                'address',
                'notes',
                'status',
                'created_at',
                'updated_at',
            ],
        );
    }

    public function createDriver(array $data): Model
    {
        return $this->create($data);
    }

    public function findDriver(int $id): ?TenantDriver
    {
        return TenantDriver::query()
            ->with('vehicle:id,registration_no,vehicle_type,brand,model')
            ->find($id);
    }
}
