<?php

namespace App\Http\Services\TenantPayrollAttendance;

use App\Http\Repositories\BaseRepositoryInterface;
use App\Models\Tenant\TenantPayrollAttendance;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface TenantPayrollAttendanceRepositoryInterface extends BaseRepositoryInterface
{
    public function attendanceList(Request $request): array;

    public function createAttendance(array $data): Model;

    public function findAttendance(int $id): ?TenantPayrollAttendance;
}
