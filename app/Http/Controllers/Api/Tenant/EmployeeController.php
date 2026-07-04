<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantApi\TenantEmployeeCreateRequest;
use App\Http\Resources\Tenant\TenantEmployeeResource;
use App\Http\Services\Response\ResponseService;
use App\Http\Services\TenantEmployee\TenantEmployeeServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    protected TenantEmployeeServiceInterface $service;

    public function __construct(TenantEmployeeServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $response = $this->service->employeeList($request);
        if (($response['success'] ?? false) === true && isset($response['data']['data']) && is_iterable($response['data']['data'])) {
            $response['data']['data'] = TenantEmployeeResource::collection($response['data']['data']);
        }

        return ResponseService::send($response);
    }

    public function store(TenantEmployeeCreateRequest $request): JsonResponse
    {
        $response = $this->service->storeEmployee($request);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantEmployeeResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function show(Request $request, string $company_username, int $id): JsonResponse
    {
        $response = $this->service->employeeDetails($request, $id);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantEmployeeResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function update(TenantEmployeeCreateRequest $request, string $company_username, int $id): JsonResponse
    {
        $request->merge(['edit_id' => $id]);
        $response = $this->service->storeEmployee($request);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantEmployeeResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function destroy(Request $request, string $company_username, int $id): JsonResponse
    {
        $response = $this->service->deleteEmployee($request, $id);
        return ResponseService::send($response);
    }

    public function allEmployees(Request $request): JsonResponse
    {
        $response = $this->service->allActiveEmployees($request);
        return ResponseService::send($response);
    }
}

