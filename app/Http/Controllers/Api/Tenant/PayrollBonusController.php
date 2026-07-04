<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantApi\TenantPayrollBonusCreateRequest;
use App\Http\Resources\Tenant\TenantPayrollBonusResource;
use App\Http\Services\Response\ResponseService;
use App\Http\Services\TenantPayrollBonus\TenantPayrollBonusServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollBonusController extends Controller
{
    protected TenantPayrollBonusServiceInterface $service;

    public function __construct(TenantPayrollBonusServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $response = $this->service->bonusList($request);
        if (($response['success'] ?? false) === true && isset($response['data']['data']) && is_iterable($response['data']['data'])) {
            $response['data']['data'] = TenantPayrollBonusResource::collection($response['data']['data']);
        }

        return ResponseService::send($response);
    }

    public function store(TenantPayrollBonusCreateRequest $request): JsonResponse
    {
        $response = $this->service->storeBonus($request);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantPayrollBonusResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function show(Request $request, string $company_username, int $id): JsonResponse
    {
        $response = $this->service->bonusDetails($request, $id);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantPayrollBonusResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function update(TenantPayrollBonusCreateRequest $request, string $company_username, int $id): JsonResponse
    {
        $request->merge(['edit_id' => $id]);
        $response = $this->service->storeBonus($request);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantPayrollBonusResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function destroy(Request $request, string $company_username, int $id): JsonResponse
    {
        $response = $this->service->deleteBonus($request, $id);
        return ResponseService::send($response);
    }
}
