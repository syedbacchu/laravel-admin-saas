<?php

namespace App\Http\Services\PricingPlan;

use Illuminate\Http\Request;

interface PricingPlanServiceInterface
{
    public function getPublicPricingPlanList(Request $request): array;
    public function getPublicPricingPlanDetails(Request $request, string $identifier): array;
}
