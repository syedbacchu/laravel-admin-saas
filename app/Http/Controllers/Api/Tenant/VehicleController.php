<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantApi\TenantVehicleCreateRequest;
use App\Http\Resources\Tenant\TenantVehicleResource;
use App\Http\Services\Response\ResponseService;
use App\Http\Services\TenantVehicle\TenantVehicleServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    protected TenantVehicleServiceInterface $service;

    public function __construct(TenantVehicleServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $response = $this->service->vehicleList($request);
        if (($response['success'] ?? false) === true && isset($response['data']['data']) && is_iterable($response['data']['data'])) {
            $response['data']['data'] = TenantVehicleResource::collection($response['data']['data']);
        }

        return ResponseService::send($response);
    }

    public function store(TenantVehicleCreateRequest $request): JsonResponse
    {
        $response = $this->service->storeVehicle($request);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantVehicleResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function show(Request $request, string $company_username, int $id): JsonResponse
    {
        $response = $this->service->vehicleDetails($request, $id);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantVehicleResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function update(TenantVehicleCreateRequest $request, string $company_username, int $id): JsonResponse
    {
        $request->merge(['edit_id' => $id]);
        $response = $this->service->storeVehicle($request);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantVehicleResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function destroy(Request $request, string $company_username, int $id): JsonResponse
    {
        $response = $this->service->deleteVehicle($request, $id);
        return ResponseService::send($response);
    }
}
