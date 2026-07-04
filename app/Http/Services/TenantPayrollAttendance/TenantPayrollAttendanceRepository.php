<?php

namespace App\Http\Services\TenantPayrollAttendance;

use App\Http\Repositories\BaseRepository;
use App\Models\TenantPayrollAttendance;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class TenantPayrollAttendanceRepository extends BaseRepository implements TenantPayrollAttendanceRepositoryInterface
{
    public function __construct(TenantPayrollAttendance $model)
    {
        parent::__construct($model);
    }

    public function attendanceList(Request $request): array
    {
        return DataListManager::list(
            request: $request,
            query: TenantPayrollAttendance::query(),
            searchable: [
                'month',
            ],
            filters: [
                'status' => [
                    'column' => 'status',
                ],
                'employee_id' => [
                    'column' => 'employee_id',
                ],
                'month' => [
                    'column' => 'month',
                ],
                'date' => [
                    'column' => 'date',
                    'type' => 'date',
                ],
            ],
            select: [
                'id',
                'added_by',
                'updated_by',
                'date',
                'employee_id',
                'working_day',
                'month',
                'status',
                'created_at',
                'updated_at',
            ],
        );
    }

    public function createAttendance(array $data): Model
    {
        return $this->create($data);
    }

    public function findAttendance(int $id): ?TenantPayrollAttendance
    {
        return TenantPayrollAttendance::query()->find($id);
    }
}
