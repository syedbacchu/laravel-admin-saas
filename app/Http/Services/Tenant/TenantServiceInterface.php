<?php

namespace App\Http\Services\Tenant;

use App\Http\Requests\Tenant\TenantCreateRequest;

interface TenantServiceInterface
{
    public function getDataTableData($request): array;
    public function storeOrUpdateTenant(TenantCreateRequest $request): array;
    public function tenantCreateData($request): array;
    public function getTenant($tenantId): mixed;
    public function backupTenantDatabase($tenantId): array;
    public function getTenantBackups($tenantId): array;
    public function downloadBackup($tenantId, $filename): array;
    public function deleteBackup($tenantId, $filename): array;
    public function reencryptTenantPassword($tenantId, $plainPassword): array;
    public function diagnoseTenantConnection($tenantId): array;
}
