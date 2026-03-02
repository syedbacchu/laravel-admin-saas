<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantApi\TenantChangePasswordRequest;
use App\Http\Requests\TenantApi\TenantUpdateProfileRequest;
use App\Http\Resources\Tenant\TenantUserResource;
use App\Http\Services\Response\ResponseService;
use App\Http\Services\TenantApi\TenantApiServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    protected TenantApiServiceInterface $service;

    public function __construct(TenantApiServiceInterface $service)
    {
        $this->service = $service;
    }

    public function profile(Request $request): JsonResponse
    {
        $response = $this->service->profileDetails($request);
        if (($response['success'] ?? false) === true && isset($response['data']['user'])) {
            $response['data']['user'] = TenantUserResource::make($response['data']['user']);
        }

        return ResponseService::send($response);
    }

    public function updateProfile(TenantUpdateProfileRequest $request): JsonResponse
    {
        $response = $this->service->updateProfile($request);
        if (($response['success'] ?? false) === true && isset($response['data'])) {
            $response['data'] = TenantUserResource::make($response['data']);
        }

        return ResponseService::send($response);
    }

    public function changePassword(TenantChangePasswordRequest $request): JsonResponse
    {
        $response = $this->service->changePassword($request);
        return ResponseService::send($response);
    }
}

