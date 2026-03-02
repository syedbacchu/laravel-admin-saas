<?php

namespace App\Http\Services\TenantApi;

use App\Http\Requests\TenantApi\TenantChangePasswordRequest;
use App\Http\Requests\TenantApi\TenantForgotPasswordRequest;
use App\Http\Requests\TenantApi\TenantLoginRequest;
use App\Http\Requests\TenantApi\TenantResetPasswordRequest;
use App\Http\Requests\TenantApi\TenantUpdateProfileRequest;
use Illuminate\Http\Request;

interface TenantApiServiceInterface
{
    public function login(TenantLoginRequest $request, string $companyUsername): array;
    public function forgotPassword(TenantForgotPasswordRequest $request, string $companyUsername): array;
    public function resetPassword(TenantResetPasswordRequest $request, string $companyUsername): array;
    public function profileDetails(Request $request): array;
    public function updateProfile(TenantUpdateProfileRequest $request): array;
    public function changePassword(TenantChangePasswordRequest $request): array;
    public function subscriptionDetails(Request $request): array;
    public function dashboardData(Request $request): array;
}

