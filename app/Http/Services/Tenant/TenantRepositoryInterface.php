<?php

namespace App\Http\Services\Tenant;

use App\Http\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface TenantRepositoryInterface extends BaseRepositoryInterface
{
    public function tenantList(Request $request): array;
    public function createTenant(array $data): Model;
}
