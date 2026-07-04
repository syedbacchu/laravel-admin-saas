<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Services\Subscription\SubscriptionFeatureService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SubscriptionFeatureController extends Controller
{
    protected SubscriptionFeatureService $subscriptionFeatureService;

    public function __construct(SubscriptionFeatureService $subscriptionFeatureService)
    {
        $this->subscriptionFeatureService = $subscriptionFeatureService;
    }

    /**
     * Get subscription features
     */
    public function index(int $subscriptionId): JsonResponse
    {
        try {
            $features = $this->subscriptionFeatureService->getSubscriptionFeatures($subscriptionId);
            $stats = $this->subscriptionFeatureService->getFeatureUsageStats($subscriptionId);

            return response()->json([
                'success' => true,
                'message' => 'Subscription features retrieved successfully',
                'data' => [
                    'features' => array_values($features),
                    'stats' => $stats,
                ],
                'status' => 200,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve subscription features',
                'data' => [],
                'status' => 500,
                'error_message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add feature to subscription
     */
    public function store(Request $request, int $subscriptionId): JsonResponse
    {
        $request->validate([
            'feature_key' => 'required|string',
            'value' => 'required',
        ]);

        try {
            $result = $this->subscriptionFeatureService->addFeatureToSubscription(
                $subscriptionId,
                $request->feature_key,
                $request->value
            );

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Feature added to subscription successfully',
                    'data' => [
                        'feature_key' => $request->feature_key,
                        'value' => $request->value,
                    ],
                    'status' => 200,
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to add feature to subscription',
                'data' => [],
                'status' => 422,
                'error_message' => 'Feature or subscription not found',
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add feature to subscription',
                'data' => [],
                'status' => 500,
                'error_message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update subscription feature
     */
    public function update(Request $request, int $subscriptionId, string $featureKey): JsonResponse
    {
        $request->validate([
            'value' => 'required',
        ]);

        try {
            $result = $this->subscriptionFeatureService->updateSubscriptionFeature(
                $subscriptionId,
                $featureKey,
                $request->value
            );

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Feature updated successfully',
                    'data' => [
                        'feature_key' => $featureKey,
                        'value' => $request->value,
                    ],
                    'status' => 200,
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to update feature',
                'data' => [],
                'status' => 422,
                'error_message' => 'Feature or subscription not found',
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update feature',
                'data' => [],
                'status' => 500,
                'error_message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove feature from subscription
     */
    public function destroy(int $subscriptionId, string $featureKey): JsonResponse
    {
        try {
            $result = $this->subscriptionFeatureService->removeFeatureFromSubscription(
                $subscriptionId,
                $featureKey
            );

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Feature removed from subscription successfully',
                    'data' => [
                        'feature_key' => $featureKey,
                    ],
                    'status' => 200,
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove feature from subscription',
                'data' => [],
                'status' => 422,
                'error_message' => 'Feature not found in subscription',
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove feature from subscription',
                'data' => [],
                'status' => 500,
                'error_message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Apply plan features to all subscriptions
     */
    public function applyToAll(Request $request): JsonResponse
    {
        $request->validate([
            'plan_id' => 'required|integer',
            'force_refresh' => 'boolean',
        ]);

        try {
            $results = $this->subscriptionFeatureService->applyPlanToAllSubscriptions(
                $request->plan_id,
                $request->force_refresh ?? true
            );

            return response()->json([
                'success' => true,
                'message' => 'Plan features applied to subscriptions',
                'data' => $results,
                'status' => 200,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to apply plan features',
                'data' => [],
                'status' => 500,
                'error_message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Refresh subscription from plan
     */
    public function refreshFromPlan(int $subscriptionId): JsonResponse
    {
        try {
            $result = $this->subscriptionFeatureService->refreshSubscriptionFromPlan($subscriptionId);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Subscription refreshed from plan successfully',
                    'data' => [],
                    'status' => 200,
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh subscription',
                'data' => [],
                'status' => 422,
                'error_message' => 'Subscription not found',
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh subscription',
                'data' => [],
                'status' => 500,
                'error_message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get plan subscriptions
     */
    public function planSubscriptions(int $planId): JsonResponse
    {
        try {
            $subscriptions = $this->subscriptionFeatureService->getPlanSubscriptions($planId);

            return response()->json([
                'success' => true,
                'message' => 'Plan subscriptions retrieved successfully',
                'data' => [
                    'subscriptions' => $subscriptions,
                    'total' => count($subscriptions),
                ],
                'status' => 200,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve plan subscriptions',
                'data' => [],
                'status' => 500,
                'error_message' => $e->getMessage(),
            ], 500);
        }
    }
}
