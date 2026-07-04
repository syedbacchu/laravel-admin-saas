<?php

namespace App\Http\Services\TenantPayrollAttendance;

use App\Http\Requests\TenantApi\TenantPayrollAttendanceCreateRequest;
use Illuminate\Http\Request;

interface TenantPayrollAttendanceServiceInterface
{
    public function attendanceList(Request $request): array;

    public function storeAttendance(TenantPayrollAttendanceCreateRequest $request): array;

    public function attendanceDetails(Request $request, int $id): array;

    public function deleteAttendance(Request $request, int $id): array;
}
