<?php

namespace App\Http\Services\Tenant;

interface TenantProvisionServiceInterface
{
    public function provision(array $payload): array;
}
