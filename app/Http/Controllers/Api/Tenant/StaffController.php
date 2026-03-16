<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantApi\TenantDriverLoginCreateRequest;
use App\Http\Requests\TenantApi\TenantStaffCreateRequest;
use App\Http\Requests\TenantApi\TenantStaffResetPasswordRequest;
use App\Http\Requests\TenantApi\TenantStaffUpdateRequest;
use App\Http\Resources\Tenant\TenantUserResource;
use App\Http\Services\Response\ResponseService;
use App\Http\Services\TenantStaff\TenantStaffServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    protected TenantStaffServiceInterface $service;

    public function __construct(TenantStaffServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $response = $this->service->staffList($request);

        if (($response['success'] ?? false) === true && isset($response['data']['data']) && is_iterable($response['data']['data'])) {
            $response['data']['data'] = TenantUserResource::collection($response['data']['data']);
        }

        return ResponseService::send($response);
    }

    public function store(TenantStaffCreateRequest $request, string $company_username): JsonResponse
    {
        $response = $this->service->createStaff($request);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantUserResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function show(Request $request, string $company_username, int $id): JsonResponse
    {
        $response = $this->service->staffDetails($request, $id);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantUserResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function update(TenantStaffUpdateRequest $request, string $company_username, int $id): JsonResponse
    {
        $response = $this->service->updateStaff($request, $id);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantUserResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function destroy(Request $request, string $company_username, int $id): JsonResponse
    {
        $response = $this->service->deleteStaff($request, $id);
        return ResponseService::send($response);
    }

    public function resetPassword(TenantStaffResetPasswordRequest $request, string $company_username, int $id): JsonResponse
    {
        $response = $this->service->resetStaffPassword($request, $id);
        return ResponseService::send($response);
    }

    public function createDriverLogin(TenantDriverLoginCreateRequest $request, string $company_username, int $id): JsonResponse
    {
        $response = $this->service->createDriverLogin($request, $id);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantUserResource::make($response['data']);
        }

        return ResponseService::send($response);
    }
}

