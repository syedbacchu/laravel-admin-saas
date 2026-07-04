<?php

namespace App\Http\Services\TenantRoutePricing;

use App\Http\Repositories\BaseRepositoryInterface;
use App\Models\TenantRoutePricing;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface TenantRoutePricingRepositoryInterface extends BaseRepositoryInterface
{
    public function routePricingList(Request $request): array;
    public function createRoutePricing(array $data): Model;
    public function findRoutePricing(int $id): ?TenantRoutePricing;
}

