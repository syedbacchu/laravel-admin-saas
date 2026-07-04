<?php

namespace App\Http\Services\TenantOffice;

use App\Http\Repositories\BaseRepository;
use App\Models\TenantOffice;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class TenantOfficeRepository extends BaseRepository implements TenantOfficeRepositoryInterface
{
    public function __construct(TenantOffice $model)
    {
        parent::__construct($model);
    }

    public function officeList(Request $request): array
    {
        return DataListManager::list(
            request: $request,
            query: TenantOffice::query(),
            searchable: [
                'branch_name',
                'address',
            ],
            filters: [
                'status' => [
                    'column' => 'status',
                ],
            ],
            select: [
                'id',
                'branch_name',
                'opening_balance',
                'address',
                'status',
                'created_at',
                'updated_at',
            ],
        );
    }

    public function createOffice(array $data): Model
    {
        return $this->create($data);
    }

    public function findOffice(int $id): ?TenantOffice
    {
        return TenantOffice::query()->find($id);
    }
}

