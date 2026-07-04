<?php

namespace App\Http\Middleware\Tenant;

use App\Models\StaffFeatureAssignment;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaffFeatureAccess
{
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

        // Get authenticated user
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => __('Authentication required'),
                'data' => [],
                'status' => 401,
                'error_message' => __('Authentication required'),
            ], 401);
        }

        // Check if user is a staff member
        if ($user->user_type !== 'staff') {
            // Non-staff users bypass this middleware
            return $next($request);
        }

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

        // Store the feature access in request attributes for potential use
        $request->attributes->set('staff_feature_key', $featureKey);
        $request->attributes->set('staff_feature_accessible', true);

        return $next($request);
    }
}