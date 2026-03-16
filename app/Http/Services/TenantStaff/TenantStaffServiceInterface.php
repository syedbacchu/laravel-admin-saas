<?php

namespace App\Http\Services\TenantStaff;

use App\Http\Requests\TenantApi\TenantDriverLoginCreateRequest;
use App\Http\Requests\TenantApi\TenantStaffCreateRequest;
use App\Http\Requests\TenantApi\TenantStaffResetPasswordRequest;
use App\Http\Requests\TenantApi\TenantStaffUpdateRequest;
use Illuminate\Http\Request;

interface TenantStaffServiceInterface
{
    public function staffList(Request $request): array;
    public function createStaff(TenantStaffCreateRequest $request): array;
    public function staffDetails(Request $request, int $id): array;
    public function updateStaff(TenantStaffUpdateRequest $request, int $id): array;
    public function deleteStaff(Request $request, int $id): array;
    public function resetStaffPassword(TenantStaffResetPasswordRequest $request, int $id): array;
    public function createDriverLogin(TenantDriverLoginCreateRequest $request, int $driverId): array;
}

