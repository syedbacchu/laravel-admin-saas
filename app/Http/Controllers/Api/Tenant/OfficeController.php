<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantApi\TenantOfficeCreateRequest;
use App\Http\Resources\Tenant\TenantOfficeResource;
use App\Http\Services\Response\ResponseService;
use App\Http\Services\TenantOffice\TenantOfficeServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfficeController extends Controller
{
    protected TenantOfficeServiceInterface $service;

    public function __construct(TenantOfficeServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $response = $this->service->officeList($request);
        if (($response['success'] ?? false) === true && isset($response['data']['data']) && is_iterable($response['data']['data'])) {
            $response['data']['data'] = TenantOfficeResource::collection($response['data']['data']);
        }

        return ResponseService::send($response);
    }

    public function store(TenantOfficeCreateRequest $request): JsonResponse
    {
        $response = $this->service->storeOffice($request);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantOfficeResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function show(Request $request, string $company_username, int $id): JsonResponse
    {
        $response = $this->service->officeDetails($request, $id);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantOfficeResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function update(TenantOfficeCreateRequest $request, string $company_username, int $id): JsonResponse
    {
        $request->merge(['edit_id' => $id]);
        $response = $this->service->storeOffice($request);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantOfficeResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function destroy(Request $request, string $company_username, int $id): JsonResponse
    {
        $response = $this->service->deleteOffice($request, $id);
        return ResponseService::send($response);
    }
}

