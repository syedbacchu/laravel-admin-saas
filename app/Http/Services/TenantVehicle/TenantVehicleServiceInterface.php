<?php

namespace App\Http\Services\TenantVehicle;

use App\Http\Requests\TenantApi\TenantVehicleCreateRequest;
use Illuminate\Http\Request;

interface TenantVehicleServiceInterface
{
    public function vehicleList(Request $request): array;
    public function storeVehicle(TenantVehicleCreateRequest $request): array;
    public function vehicleDetails(Request $request, int $id): array;
    public function deleteVehicle(Request $request, int $id): array;
}
