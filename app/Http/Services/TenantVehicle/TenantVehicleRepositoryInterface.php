<?php

namespace App\Http\Services\TenantVehicle;

use App\Http\Repositories\BaseRepositoryInterface;
use App\Models\TenantVehicle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface TenantVehicleRepositoryInterface extends BaseRepositoryInterface
{
    public function vehicleList(Request $request): array;
    public function createVehicle(array $data): Model;
    public function findVehicle(int $id): ?TenantVehicle;
    public function totalVehicle(): int;
}
