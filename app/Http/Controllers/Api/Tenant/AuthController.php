<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantApi\TenantForgotPasswordRequest;
use App\Http\Requests\TenantApi\TenantLoginRequest;
use App\Http\Requests\TenantApi\TenantResetPasswordRequest;
use App\Http\Resources\Tenant\TenantSubscriptionResource;
use App\Http\Resources\Tenant\TenantUserResource;
use App\Http\Services\Response\ResponseService;
use App\Http\Services\TenantApi\TenantApiServiceInterface;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    protected TenantApiServiceInterface $service;

    public function __construct(TenantApiServiceInterface $service)
    {
        $this->service = $service;
    }

    public function login(TenantLoginRequest $request, string $company_username): JsonResponse
    {
        $response = $this->service->login($request, $company_username);

        if (($response['success'] ?? false) === true) {
            if (isset($response['data']['user'])) {
                $response['data']['user'] = TenantUserResource::make($response['data']['user']);
            }
            if (!empty($response['data']['package']['subscription'])) {
                $response['data']['package']['subscription'] = TenantSubscriptionResource::make($response['data']['package']['subscription']);
            }
        }

        return ResponseService::send($response);
    }

    public function forgotPassword(TenantForgotPasswordRequest $request, string $company_username): JsonResponse
    {
        $response = $this->service->forgotPassword($request, $company_username);
        return ResponseService::send($response);
    }

    public function resetPassword(TenantResetPasswordRequest $request, string $company_username): JsonResponse
    {
        $response = $this->service->resetPassword($request, $company_username);
        return ResponseService::send($response);
    }
}

