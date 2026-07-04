<?php

namespace App\Http\Services\TenantEmployee;

use App\Http\Repositories\BaseRepository;
use App\Models\TenantEmployee;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class TenantEmployeeRepository extends BaseRepository implements TenantEmployeeRepositoryInterface
{
    public function __construct(TenantEmployee $model)
    {
        parent::__construct($model);
    }

    public function employeeList(Request $request): array
    {
        return DataListManager::list(
            request: $request,
            query: TenantEmployee::query(),
            searchable: [
                'name',
                'email',
                'mobile',
                'nid',
                'designation',
                'address',
            ],
            filters: [
                'status' => [
                    'column' => 'status',
                ],
                'employee_type' => [
                    'column' => 'employee_type',
                ],
                'gender' => [
                    'column' => 'gender',
                ],
                'blood_group' => [
                    'column' => 'blood_group',
                ],
                'joining_date' => [
                    'column' => 'joining_date',
                    'type' => 'date',
                ],
            ],
            select: [
                'id',
                'name',
                'email',
                'mobile',
                'gender',
                'blood_group',
                'birth_date',
                'joining_date',
                'nid',
                'designation',
                'address',
                'basic_salary',
                'gross_salary',
                'house_rent',
                'medical',
                'allowance',
                'conveyance',
                'image',
                'status',
                'created_at',
                'updated_at',
            ],
        );
    }

    public function allEmployeesList(Request $request): array
    {
        return TenantEmployee::query()
            ->withoutGlobalScopes()
            ->where('status', 1)
            ->orderBy('employee_type')
            ->orderBy('name')
            ->get([
                'id',
                'employee_type',
                'name',
                'email',
                'mobile',
                'gender',
                'blood_group',
                'birth_date',
                'joining_date',
                'nid',
                'designation',
                'address',
                'basic_salary',
                'gross_salary',
                'house_rent',
                'medical',
                'allowance',
                'extra_allowance',
                'conveyance',
                'image',
                'status',
                'created_at',
                'updated_at',
            ])
            ->toArray();
    }

    public function createEmployee(array $data): Model
    {
        return $this->create($data);
    }

    public function findEmployee(int $id): ?TenantEmployee
    {
        return TenantEmployee::query()->find($id);
    }
}
