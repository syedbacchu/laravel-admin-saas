<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantApi\TenantDailyOfficeExpenseCreateRequest;
use App\Http\Resources\Tenant\TenantDailyOfficeExpenseResource;
use App\Http\Services\Response\ResponseService;
use App\Http\Services\TenantDailyOfficeExpense\TenantDailyOfficeExpenseServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DailyOfficeExpenseController extends Controller
{
    protected TenantDailyOfficeExpenseServiceInterface $service;

    public function __construct(TenantDailyOfficeExpenseServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $response = $this->service->dailyOfficeExpenseList($request);
        if (($response['success'] ?? false) === true && isset($response['data']['data']) && is_iterable($response['data']['data'])) {
            $response['data']['data'] = TenantDailyOfficeExpenseResource::collection($response['data']['data']);
        }

        return ResponseService::send($response);
    }

    public function store(TenantDailyOfficeExpenseCreateRequest $request): JsonResponse
    {
        $response = $this->service->storeDailyOfficeExpense($request);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantDailyOfficeExpenseResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function show(Request $request, string $company_username, int $id): JsonResponse
    {
        $response = $this->service->dailyOfficeExpenseDetails($request, $id);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantDailyOfficeExpenseResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function update(TenantDailyOfficeExpenseCreateRequest $request, string $company_username, int $id): JsonResponse
    {
        $request->merge(['edit_id' => $id]);
        $response = $this->service->storeDailyOfficeExpense($request);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantDailyOfficeExpenseResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function destroy(Request $request, string $company_username, int $id): JsonResponse
    {
        $response = $this->service->deleteDailyOfficeExpense($request, $id);
        return ResponseService::send($response);
    }
}

