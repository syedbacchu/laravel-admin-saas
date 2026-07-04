<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantApi\TenantPayrollAttendanceCreateRequest;
use App\Http\Resources\Tenant\TenantPayrollAttendanceResource;
use App\Http\Services\Response\ResponseService;
use App\Http\Services\TenantPayrollAttendance\TenantPayrollAttendanceServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollAttendanceController extends Controller
{
    protected TenantPayrollAttendanceServiceInterface $service;

    public function __construct(TenantPayrollAttendanceServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $response = $this->service->attendanceList($request);
        if (($response['success'] ?? false) === true && isset($response['data']['data']) && is_iterable($response['data']['data'])) {
            $response['data']['data'] = TenantPayrollAttendanceResource::collection($response['data']['data']);
        }

        return ResponseService::send($response);
    }

    public function store(TenantPayrollAttendanceCreateRequest $request): JsonResponse
    {
        $response = $this->service->storeAttendance($request);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantPayrollAttendanceResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function show(Request $request, string $company_username, int $id): JsonResponse
    {
        $response = $this->service->attendanceDetails($request, $id);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantPayrollAttendanceResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function update(TenantPayrollAttendanceCreateRequest $request, string $company_username, int $id): JsonResponse
    {
        $request->merge(['edit_id' => $id]);
        $response = $this->service->storeAttendance($request);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantPayrollAttendanceResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function destroy(Request $request, string $company_username, int $id): JsonResponse
    {
        $response = $this->service->deleteAttendance($request, $id);
        return ResponseService::send($response);
    }
}
