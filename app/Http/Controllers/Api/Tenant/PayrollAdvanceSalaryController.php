<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantApi\TenantPayrollAdvanceSalaryCreateRequest;
use App\Http\Resources\Tenant\TenantPayrollAdvanceSalaryResource;
use App\Http\Services\Response\ResponseService;
use App\Http\Services\TenantPayrollAdvanceSalary\TenantPayrollAdvanceSalaryServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollAdvanceSalaryController extends Controller
{
    protected TenantPayrollAdvanceSalaryServiceInterface $service;

    public function __construct(TenantPayrollAdvanceSalaryServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $response = $this->service->advanceSalaryList($request);
        if (($response['success'] ?? false) === true && isset($response['data']['data']) && is_iterable($response['data']['data'])) {
            $response['data']['data'] = TenantPayrollAdvanceSalaryResource::collection($response['data']['data']);
        }

        return ResponseService::send($response);
    }

    public function store(TenantPayrollAdvanceSalaryCreateRequest $request): JsonResponse
    {
        $response = $this->service->storeAdvanceSalary($request);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantPayrollAdvanceSalaryResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function show(Request $request, string $company_username, int $id): JsonResponse
    {
        $response = $this->service->advanceSalaryDetails($request, $id);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantPayrollAdvanceSalaryResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function update(TenantPayrollAdvanceSalaryCreateRequest $request, string $company_username, int $id): JsonResponse
    {
        $request->merge(['edit_id' => $id]);
        $response = $this->service->storeAdvanceSalary($request);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantPayrollAdvanceSalaryResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function destroy(Request $request, string $company_username, int $id): JsonResponse
    {
        $response = $this->service->deleteAdvanceSalary($request, $id);
        return ResponseService::send($response);
    }
}
