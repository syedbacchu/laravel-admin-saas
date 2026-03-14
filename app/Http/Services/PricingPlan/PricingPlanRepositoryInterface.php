<?php

namespace App\Http\Services\PricingPlan;

use App\Http\Repositories\BaseRepositoryInterface;
use App\Models\Plan;
use Illuminate\Http\Request;

interface PricingPlanRepositoryInterface extends BaseRepositoryInterface
{
    public function pricingPlanList(Request $request): array;
    public function findPublicPricingPlanByIdentifier(string $identifier): ?Plan;
}
