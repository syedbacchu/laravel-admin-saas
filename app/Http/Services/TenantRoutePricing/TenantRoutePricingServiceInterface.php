<?php

namespace App\Http\Services\TenantRoutePricing;

use App\Http\Requests\TenantApi\TenantRoutePricingCreateRequest;
use Illuminate\Http\Request;

interface TenantRoutePricingServiceInterface
{
    public function routePricingList(Request $request): array;
    public function storeRoutePricing(TenantRoutePricingCreateRequest $request): array;
    public function routePricingDetails(Request $request, int $id): array;
    public function deleteRoutePricing(Request $request, int $id): array;
    public function getDistanceByRoute(Request $request): array;
    public function calculateDistanceByGoogleMaps(Request $request): array;
}

