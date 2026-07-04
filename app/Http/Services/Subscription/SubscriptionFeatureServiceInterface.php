<?php

namespace App\Http\Services\Subscription;

interface SubscriptionFeatureServiceInterface
{
    /**
     * Add feature to specific subscription
     */
    public function addFeatureToSubscription(int $subscriptionId, string $featureKey, mixed $value): bool;

    /**
     * Remove feature from specific subscription
     */
    public function removeFeatureFromSubscription(int $subscriptionId, string $featureKey): bool;

    /**
     * Update feature value in subscription
     */
    public function updateSubscriptionFeature(int $subscriptionId, string $featureKey, mixed $newValue): bool;

    /**
     * Get subscription features
     */
    public function getSubscriptionFeatures(int $subscriptionId): array;

    /**
     * Apply plan features to all active subscriptions
     */
    public function applyPlanToAllSubscriptions(int $planId, bool $forceRefresh = true): array;

    /**
     * Refresh subscription features from current plan
     */
    public function refreshSubscriptionFromPlan(int $subscriptionId): bool;

    /**
     * Add missing features from plan to subscription
     */
    public function addMissingPlanFeatures(int $subscriptionId): bool;

    /**
     * Get all subscriptions for a plan
     */
    public function getPlanSubscriptions(int $planId): array;

    /**
     * Get subscription feature usage statistics
     */
    public function getFeatureUsageStats(int $subscriptionId): array;
}
