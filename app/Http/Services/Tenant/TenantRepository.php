<?php

namespace App\Http\Services\Tenant;

use App\Http\Repositories\BaseRepository;
use App\Models\Tenant;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class TenantRepository extends BaseRepository implements TenantRepositoryInterface
{
    public function __construct(Tenant $model)
    {
        parent::__construct($model);
    }

    public function tenantList(Request $request): array
    {
        return DataListManager::list(
            request: $request,
            query: Tenant::query(),
            searchable: [
                'tenants.company_name',
                'tenants.company_username',
                'owner.name',
                'owner.email',
                'owner.phone',
                'tdb.db_name',
            ],
            filters: [
                'status' => [
                    'column' => 'tenants.status',
                ],
            ],
            select: [
                'tenants.id',
                'tenants.company_name',
                'tenants.company_username',
                'tenants.status',
                'tenants.created_at',
                'owner.name as owner_name',
                'owner.email as owner_email',
                'owner.phone as owner_phone',
                'tdb.db_name as db_name',
            ],
            joinCallback: function ($query) {
                $query->leftJoin('users as owner', 'owner.id', '=', 'tenants.owner_user_id')
                    ->leftJoin('tenant_databases as tdb', 'tdb.tenant_id', '=', 'tenants.id');
            }
        );
    }

    public function createTenant(array $data): Model
    {
        return $this->create($data);
    }
}
