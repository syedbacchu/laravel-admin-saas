<?php

namespace App\Http\Middleware\Tenant;

use App\Http\Services\Tenant\TenantFeatureResolverService;
use App\Models\StaffFeatureAssignment;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantFeatureAccess
{
    public function __construct(
        protected TenantFeatureResolverService $tenantFeatureResolverService
    ) {
    }

    public function handle(Request $request, Closure $next, ?string $featureKey = null): Response
    {
        $featureKey = $featureKey ?: (string) $request->route('feature_key');
        if ($featureKey === '') {
            return response()->json([
                'success' => false,
                'message' => __('Feature key is required'),
                'data' => [],
                'status' => 422,
                'error_message' => __('Feature key is required'),
            ], 422);
        }

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

        // Get authenticated user
        $user = auth()->user();
        if ($user && $user->user_type === 'staff') {
            // Handle staff feature access
            return $this->handleStaffFeatureAccess($request, $next, $featureKey, $user);
        }

        // Handle tenant admin/owner feature access
        return $this->handleTenantFeatureAccess($request, $next, $featureKey, $tenant);
    }

    protected function handleStaffFeatureAccess(Request $request, Closure $next, string $featureKey, $user): Response
    {
        // Check if staff has access to the requested feature
        $hasAccess = StaffFeatureAssignment::where('staff_id', $user->id)
            ->where('feature_key', $featureKey)
            ->where('is_accessible', true)
            ->exists();

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => __('You do not have access to this feature'),
                'data' => [
                    'feature_key' => $featureKey,
                ],
                'status' => 403,
                'error_message' => __('Feature access denied'),
            ], 403);
        }

        $request->attributes->set('staff_feature_key', $featureKey);
        $request->attributes->set('staff_feature_accessible', true);

        return $next($request);
    }

    protected function handleTenantFeatureAccess(Request $request, Closure $next, string $featureKey, $tenant): Response
    {
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

        $featureValue = $this->tenantFeatureResolverService->getValue((int) $tenant->id, $featureKey, null);
        $allowed = $this->isAllowed($featureValue);

        if (!$allowed) {
            return response()->json([
                'success' => false,
                'message' => __('Feature access denied for current package'),
                'data' => [
                    'feature_key' => $featureKey,
                ],
                'status' => 403,
                'error_message' => __('Feature access denied'),
            ], 403);
        }

        $request->attributes->set('tenant_feature_key', $featureKey);
        $request->attributes->set('tenant_feature_value', $featureValue);

        return $next($request);
    }

    protected function isAllowed(mixed $featureValue): bool
    {
        if (is_bool($featureValue)) {
            return $featureValue;
        }

        if (is_numeric($featureValue)) {
            return (float) $featureValue > 0;
        }

        if (is_array($featureValue)) {
            return !empty($featureValue);
        }

        return !empty($featureValue);
    }
}
