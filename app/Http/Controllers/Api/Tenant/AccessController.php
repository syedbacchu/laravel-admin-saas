<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Services\Response\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccessController extends Controller
{
    public function featureCheck(Request $request, string $company_username, string $feature_key): JsonResponse
    {
        $tenant = $request->attributes->get('tenant');
        $activeSubscription = $request->attributes->get('active_subscription');
        $value = $request->attributes->get('tenant_feature_value');

        $response = sendResponse(true, __('Feature access granted'), [
            'tenant_id' => $tenant?->id,
            'feature_key' => $feature_key,
            'feature_value' => $value,
            'subscription_id' => $activeSubscription?->id,
        ]);

        return ResponseService::send($response);
    }
}
