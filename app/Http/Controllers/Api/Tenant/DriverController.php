<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantApi\TenantDriverCreateRequest;
use App\Http\Resources\Tenant\TenantDriverResource;
use App\Http\Services\Response\ResponseService;
use App\Http\Services\TenantDriver\TenantDriverServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    protected TenantDriverServiceInterface $service;

    public function __construct(TenantDriverServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $response = $this->service->driverList($request);
        if (($response['success'] ?? false) === true && isset($response['data']['data']) && is_iterable($response['data']['data'])) {
            $response['data']['data'] = TenantDriverResource::collection($response['data']['data']);
        }

        return ResponseService::send($response);
    }

    public function store(TenantDriverCreateRequest $request): JsonResponse
    {
        $response = $this->service->storeDriver($request);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantDriverResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $response = $this->service->driverDetails($request, $id);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantDriverResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function update(TenantDriverCreateRequest $request, int $id): JsonResponse
    {
        $request->merge(['edit_id' => $id]);
        $response = $this->service->storeDriver($request);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantDriverResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $response = $this->service->deleteDriver($request, $id);
        return ResponseService::send($response);
    }
}
