<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantApi\TenantCustomerAddressCreateRequest;
use App\Http\Requests\TenantApi\TenantCustomerCreateRequest;
use App\Http\Resources\Tenant\TenantCustomerResource;
use App\Http\Services\Response\ResponseService;
use App\Http\Services\TenantCustomer\TenantCustomerServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    protected TenantCustomerServiceInterface $service;

    public function __construct(TenantCustomerServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $response = $this->service->customerList($request);
        if (($response['success'] ?? false) === true && isset($response['data']['data']) && is_iterable($response['data']['data'])) {
            $response['data']['data'] = TenantCustomerResource::collection($response['data']['data']);
        }

        return ResponseService::send($response);
    }

    public function store(TenantCustomerCreateRequest $request): JsonResponse
    {
        $response = $this->service->storeCustomer($request);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantCustomerResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function show(Request $request, string $company_username, int $id): JsonResponse
    {
        $response = $this->service->customerDetails($request, $id);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantCustomerResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function update(TenantCustomerCreateRequest $request, string $company_username, int $id): JsonResponse
    {
        $request->merge(['edit_id' => $id]);
        $response = $this->service->storeCustomer($request);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantCustomerResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function destroy(Request $request, string $company_username, int $id): JsonResponse
    {
        $response = $this->service->deleteCustomer($request, $id);
        return ResponseService::send($response);
    }

    public function addAddress(TenantCustomerAddressCreateRequest $request, string $company_username, int $customerId): JsonResponse
    {
        $response = $this->service->addCustomerAddress($request, $customerId);
        return ResponseService::send($response);
    }
}

