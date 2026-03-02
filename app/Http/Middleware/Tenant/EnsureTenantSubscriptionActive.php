<?php

namespace App\Http\Middleware\Tenant;

use App\Http\Services\Tenant\TenantFeatureResolverService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantSubscriptionActive
{
    public function __construct(
        protected TenantFeatureResolverService $tenantFeatureResolverService
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $request->attributes->get('tenant');
        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => __('Tenant context is missing'),
                'data' => [],
                'status' => 422,
                'error_message' => __('Tenant context is missing'),
            ], 422);
        }

        $activeSubscription = $this->tenantFeatureResolverService->getActiveSubscription((int) $tenant->id);
        if (!$activeSubscription) {
            return response()->json([
                'success' => false,
                'message' => __('Your package is expired or inactive. Please renew your subscription.'),
                'data' => [],
                'status' => 402,
                'error_message' => __('Package expired'),
            ], 402);
        }

        $request->attributes->set('active_subscription', $activeSubscription);

        return $next($request);
    }
}

