<?php

namespace App\Http\Services\TenantCustomer;

use App\Http\Repositories\BaseRepository;
use App\Models\Tenant\TenantCustomer;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class TenantCustomerRepository extends BaseRepository implements TenantCustomerRepositoryInterface
{
    public function __construct(TenantCustomer $model)
    {
        parent::__construct($model);
    }

    public function customerList(Request $request): array
    {
        return DataListManager::list(
            request: $request,
            query: TenantCustomer::query()->with('addresses'),
            searchable: [
                'name',
                'mobile',
                'email',
            ],
            filters: [
                'status' => [
                    'column' => 'status',
                ],
                'rate_status' => [
                    'column' => 'rate_status',
                ],
            ],
            select: [
                'id',
                'name',
                'mobile',
                'email',
                'image',
                'address',
                'rate_status',
                'opening_balance',
                'status',
                'created_at',
                'updated_at',
            ],
        );
    }

    public function createCustomer(array $data): Model
    {
        return $this->create($data);
    }

    public function findCustomer(int $id): ?TenantCustomer
    {
        return TenantCustomer::query()
            ->with('addresses')
            ->find($id);
    }
}
