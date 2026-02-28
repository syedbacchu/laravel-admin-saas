<?php

namespace App\Http\Services\Tenant;

use App\Http\Requests\Tenant\TenantCreateRequest;

interface TenantServiceInterface
{
    public function getDataTableData($request): array;
    public function storeOrUpdateTenant(TenantCreateRequest $request): array;
    public function tenantCreateData($request): array;
}
