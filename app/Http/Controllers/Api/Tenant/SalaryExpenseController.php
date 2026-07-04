<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantApi\TenantSalaryExpenseCreateRequest;
use App\Http\Resources\Tenant\TenantSalaryExpenseResource;
use App\Http\Services\Response\ResponseService;
use App\Http\Services\TenantSalaryExpense\TenantSalaryExpenseServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalaryExpenseController extends Controller
{
    protected TenantSalaryExpenseServiceInterface $service;

    public function __construct(TenantSalaryExpenseServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $response = $this->service->salaryExpenseList($request);
        if (($response['success'] ?? false) === true && isset($response['data']['data']) && is_iterable($response['data']['data'])) {
            $response['data']['data'] = TenantSalaryExpenseResource::collection($response['data']['data']);
        }

        return ResponseService::send($response);
    }

    public function store(TenantSalaryExpenseCreateRequest $request): JsonResponse
    {
        $response = $this->service->storeSalaryExpense($request);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantSalaryExpenseResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function show(Request $request, string $company_username, int $id): JsonResponse
    {
        $response = $this->service->salaryExpenseDetails($request, $id);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantSalaryExpenseResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function update(TenantSalaryExpenseCreateRequest $request, string $company_username, int $id): JsonResponse
    {
        $request->merge(['edit_id' => $id]);
        $response = $this->service->storeSalaryExpense($request);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantSalaryExpenseResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function destroy(Request $request, string $company_username, int $id): JsonResponse
    {
        $response = $this->service->deleteSalaryExpense($request, $id);
        return ResponseService::send($response);
    }

    public function calculatePayableAmount(Request $request): JsonResponse
    {
        $response = $this->service->calculatePayableAmount($request);
        return ResponseService::send($response);
    }
}

