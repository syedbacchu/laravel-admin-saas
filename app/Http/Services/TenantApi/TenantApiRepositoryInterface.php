<?php

namespace App\Http\Services\TenantApi;

use App\Http\Repositories\BaseRepositoryInterface;
use App\Models\Tenant;
use App\Models\User;

interface TenantApiRepositoryInterface extends BaseRepositoryInterface
{
    public function findTenantByUsername(string $companyUsername): ?Tenant;
    public function findTenantUserByLogin(Tenant $tenant, string $login): ?User;
    public function findTenantByUser(User $user): ?Tenant;
}

