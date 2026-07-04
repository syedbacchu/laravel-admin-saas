<?php

namespace App\Http\Services\Subscription;

use App\Models\Feature;
use App\Models\PlanFeatureValue;
use App\Models\Subscription;
use App\Models\SubscriptionFeatureSnapshot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SubscriptionFeatureService
{
    /**
     * Add feature to specific subscription
     */
    public function addFeatureToSubscription(int $subscriptionId, string $featureKey, mixed $value): bool
    {
        $subscription = Subscription::find($subscriptionId);
        if (!$subscription) {
            return false;
        }

        $feature = Feature::where('key', $featureKey)->first();
        if (!$feature) {
            return false;
        }

        // Remove existing snapshot for this feature if exists
        SubscriptionFeatureSnapshot::where('subscription_id', $subscriptionId)
            ->where('feature_key', $featureKey)
            ->delete();

        // Create new snapshot
        SubscriptionFeatureSnapshot::create([
            'subscription_id' => $subscriptionId,
            'feature_key' => $featureKey,
            'feature_type' => $feature->value_type,
            'feature_value_json' => $this->formatValue($value, $feature->value_type),
        ]);

        // Clear cache for this tenant
        $this->clearSubscriptionCache($subscription->tenant_id);

        return true;
    }

    /**
     * Remove feature from specific subscription
     */
    public function removeFeatureFromSubscription(int $subscriptionId, string $featureKey): bool
    {
        $deleted = SubscriptionFeatureSnapshot::where('subscription_id', $subscriptionId)
            ->where('feature_key', $featureKey)
            ->delete();

        if ($deleted > 0) {
            $subscription = Subscription::find($subscriptionId);
            if ($subscription) {
                $this->clearSubscriptionCache($subscription->tenant_id);
            }
            return true;
        }

        return false;
    }

    /**
     * Update feature value in subscription
     */
    public function updateSubscriptionFeature(int $subscriptionId, string $featureKey, mixed $newValue): bool
    {
        return $this->addFeatureToSubscription($subscriptionId, $featureKey, $newValue);
    }

    /**
     * Get subscription features
     */
    public function getSubscriptionFeatures(int $subscriptionId): array
    {
        $subscription = Subscription::with(['featureSnapshots', 'plan.featureValues.feature'])->find($subscriptionId);
        if (!$subscription) {
            return [];
        }

        $features = [];

        // First load from snapshots
        foreach ($subscription->featureSnapshots as $snapshot) {
            $features[$snapshot->feature_key] = [
                'key' => $snapshot->feature_key,
                'type' => $snapshot->feature_type,
                'value' => $this->extractValue($snapshot->feature_value_json, $snapshot->feature_type),
                'source' => 'snapshot',
            ];
        }

        // Fill in missing features from plan
        foreach ($subscription->plan->featureValues as $planFeature) {
            if (!isset($features[$planFeature->feature->key])) {
                $features[$planFeature->feature->key] = [
                    'key' => $planFeature->feature->key,
                    'type' => $planFeature->feature->value_type,
                    'value' => $this->extractPlanValue($planFeature, $planFeature->feature->value_type),
                    'source' => 'plan',
                ];
            }
        }

        return $features;
    }

    /**
     * Apply plan features to all active subscriptions (with option to force)
     */
    public function applyPlanToAllSubscriptions(int $planId, bool $forceRefresh = true): array
    {
        $subscriptions = Subscription::where('plan_id', $planId)
            ->whereIn('status', ['trialing', 'active', 'past_due'])
            ->get();

        $results = [
            'total' => $subscriptions->count(),
            'updated' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($subscriptions as $subscription) {
            try {
                if ($forceRefresh) {
                    $this->refreshSubscriptionFromPlan($subscription->id);
                    $results['updated']++;
                } else {
                    // Only add missing features
                    $this->addMissingPlanFeatures($subscription->id);
                    $results['updated']++;
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][$subscription->id] = $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Refresh subscription features from current plan (overwrite snapshots)
     */
    public function refreshSubscriptionFromPlan(int $subscriptionId): bool
    {
        return DB::transaction(function () use ($subscriptionId) {
            $subscription = Subscription::with('plan.featureValues.feature')->find($subscriptionId);
            if (!$subscription) {
                return false;
            }

            // Delete all existing snapshots
            SubscriptionFeatureSnapshot::where('subscription_id', $subscriptionId)->delete();

            // Create new snapshots from current plan
            foreach ($subscription->plan->featureValues as $planFeature) {
                if (!$planFeature->feature) {
                    continue;
                }

                SubscriptionFeatureSnapshot::create([
                    'subscription_id' => $subscriptionId,
                    'feature_key' => $planFeature->feature->key,
                    'feature_type' => $planFeature->feature->value_type,
                    'feature_value_json' => $this->formatPlanValue($planFeature, $planFeature->feature->value_type),
                ]);
            }

            // Clear cache
            $this->clearSubscriptionCache($subscription->tenant_id);

            return true;
        });
    }

    /**
     * Add missing features from plan to subscription
     */
    public function addMissingPlanFeatures(int $subscriptionId): bool
    {
        $subscription = Subscription::with('plan.featureValues.feature')->find($subscriptionId);
        if (!$subscription) {
            return false;
        }

        $existingFeatures = SubscriptionFeatureSnapshot::where('subscription_id', $subscriptionId)
            ->pluck('feature_key')
            ->toArray();

        $added = false;

        foreach ($subscription->plan->featureValues as $planFeature) {
            if (!$planFeature->feature) {
                continue;
            }

            // Skip if already exists
            if (in_array($planFeature->feature->key, $existingFeatures)) {
                continue;
            }

            SubscriptionFeatureSnapshot::create([
                'subscription_id' => $subscriptionId,
                'feature_key' => $planFeature->feature->key,
                'feature_type' => $planFeature->feature->value_type,
                'feature_value_json' => $this->formatPlanValue($planFeature, $planFeature->feature->value_type),
            ]);

            $added = true;
        }

        if ($added) {
            $this->clearSubscriptionCache($subscription->tenant_id);
        }

        return $added;
    }

    /**
     * Get all subscriptions for a plan
     */
    public function getPlanSubscriptions(int $planId): array
    {
        return Subscription::where('plan_id', $planId)
            ->with(['tenant', 'featureSnapshots'])
            ->get()
            ->toArray();
    }

    /**
     * Get subscription feature usage statistics
     */
    public function getFeatureUsageStats(int $subscriptionId): array
    {
        $features = $this->getSubscriptionFeatures($subscriptionId);

        $stats = [
            'total_features' => count($features),
            'active_features' => 0,
            'boolean_features' => 0,
            'numeric_features' => 0,
            'features_by_value' => [],
        ];

        foreach ($features as $key => $feature) {
            if ($this->isFeatureActive($feature['value'], $feature['type'])) {
                $stats['active_features']++;
            }

            if ($feature['type'] === 'boolean') {
                $stats['boolean_features']++;
            } elseif (in_array($feature['type'], ['integer', 'decimal'])) {
                $stats['numeric_features']++;
            }

            $stats['features_by_value'][$key] = $feature['value'];
        }

        return $stats;
    }

    /**
     * Clear subscription cache
     */
    protected function clearSubscriptionCache(int $tenantId): void
    {
        Cache::forget('tenant_feature_map_' . $tenantId);
    }

    /**
     * Format value for storage
     */
    protected function formatValue(mixed $value, string $type): array
    {
        return ['value' => $value];
    }

    /**
     * Format plan value for storage
     */
    protected function formatPlanValue(PlanFeatureValue $planFeature, string $type): array
    {
        if ($type === 'boolean') {
            return ['value' => $planFeature->value_bool];
        }
        if ($type === 'integer') {
            return ['value' => $planFeature->value_int];
        }
        if ($type === 'decimal') {
            return ['value' => $planFeature->value_decimal];
        }
        if ($type === 'json') {
            return $planFeature->value_json;
        }

        return ['value' => $planFeature->value_text];
    }

    /**
     * Extract value from snapshot
     */
    protected function extractValue(mixed $jsonValue, string $type): mixed
    {
        if (is_array($jsonValue) && array_key_exists('value', $jsonValue) && $type !== 'json') {
            return $jsonValue['value'];
        }

        return $jsonValue;
    }

    /**
     * Extract value from plan feature
     */
    protected function extractPlanValue(PlanFeatureValue $planFeature, string $type): mixed
    {
        if ($type === 'boolean') {
            return (bool) $planFeature->value_bool;
        }
        if ($type === 'integer') {
            return $planFeature->value_int !== null ? (int) $planFeature->value_int : null;
        }
        if ($type === 'decimal') {
            return $planFeature->value_decimal !== null ? (float) $planFeature->value_decimal : null;
        }
        if ($type === 'json') {
            return $planFeature->value_json;
        }

        return $planFeature->value_text;
    }

    /**
     * Check if feature is active
     */
    protected function isFeatureActive(mixed $value, string $type): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (float) $value > 0;
        }

        if (is_array($value)) {
            return !empty($value);
        }

        return !empty($value);
    }
}
