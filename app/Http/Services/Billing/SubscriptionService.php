<?php

namespace App\Http\Services\Billing;

use App\Http\Requests\Billing\SubscriptionCreateRequest;
use App\Http\Services\BaseService;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeatureValue;
use App\Models\PlanPricing;
use App\Models\Subscription;
use App\Models\SubscriptionFeatureSnapshot;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class SubscriptionService extends BaseService implements SubscriptionServiceInterface
{
    protected SubscriptionRepositoryInterface $subscriptionRepository;

    public function __construct(SubscriptionRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->subscriptionRepository = $repository;
    }

    public function getDataTableData($request): array
    {
        $data = $this->subscriptionRepository->subscriptionList($request);
        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function subscriptionCreateData($request): array
    {
        $tenants = Tenant::query()
            ->whereIn('status', ['active', 'provisioning'])
            ->orderBy('company_name')
            ->get(['id', 'company_name', 'company_username']);

        $plans = Plan::query()
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        $pricings = PlanPricing::query()
            ->where('is_active', 1)
            ->with('plan:id,name')
            ->orderBy('plan_id')
            ->orderBy('term_months')
            ->get(['id', 'plan_id', 'term_months', 'final_amount', 'currency']);

        return $this->sendResponse(true, '', [
            'tenants' => $tenants,
            'plans' => $plans,
            'pricings' => $pricings,
        ]);
    }

    public function subscriptionEditData($id): array
    {
        $item = $this->subscriptionRepository->find((int) $id);
        if (!$item) {
            return $this->sendResponse(false, __('Data not found'));
        }

        return $this->sendResponse(true, '', $item);
    }

    public function storeOrUpdateSubscription(SubscriptionCreateRequest $request): array
    {
        try {
            return DB::transaction(function () use ($request) {
                $pricing = PlanPricing::query()
                    ->where('id', $request->plan_pricing_id)
                    ->where('plan_id', $request->plan_id)
                    ->first();

                if (!$pricing) {
                    return $this->sendResponse(false, __('Invalid pricing for selected plan'));
                }

                $startsAt = $request->starts_at ? Carbon::parse($request->starts_at) : now();
                $endsAt = $startsAt->copy()->addMonthsNoOverflow((int) $pricing->term_months);
                $status = $request->status ?: 'active';

                if (!$request->edit_id && in_array($status, ['trialing', 'active', 'past_due'], true)) {
                    $this->expireRunningSubscriptions((int) $request->tenant_id);
                }

                $data = [
                    'tenant_id' => (int) $request->tenant_id,
                    'plan_id' => (int) $request->plan_id,
                    'plan_pricing_id' => (int) $request->plan_pricing_id,
                    'status' => $status,
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                    'grace_ends_at' => $request->grace_ends_at ? Carbon::parse($request->grace_ends_at) : null,
                    'auto_renew' => (int) ($request->auto_renew ?? 1),
                ];

                if ($request->edit_id) {
                    $item = $this->subscriptionRepository->find((int) $request->edit_id);
                    if (!$item) {
                        return $this->sendResponse(false, __('Data not found'));
                    }

                    $this->subscriptionRepository->update($item->id, $data);
                    $subscription = $this->subscriptionRepository->find((int) $item->id);
                    $message = __('Subscription updated successfully');
                } else {
                    $subscription = $this->subscriptionRepository->createSubscription($data);
                    $message = __('Subscription created successfully');
                }

                $this->syncFeatureSnapshots($subscription->id, (int) $request->plan_id);
                app(\App\Http\Services\Tenant\TenantFeatureResolverService::class)->clearFeatureCache((int) $subscription->tenant_id);

                return $this->sendResponse(true, $message, $subscription->fresh());
            });
        } catch (Throwable $e) {
            logStore('SubscriptionService storeOrUpdateSubscription', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong'), [], 500, $e->getMessage());
        }
    }

    public function deleteSubscription($id): array
    {
        $item = $this->subscriptionRepository->find((int) $id);
        if (!$item) {
            return $this->sendResponse(false, __('Data not found'));
        }

        $this->subscriptionRepository->update((int) $item->id, [
            'status' => 'canceled',
            'canceled_at' => now(),
            'auto_renew' => 0,
        ]);
        app(\App\Http\Services\Tenant\TenantFeatureResolverService::class)->clearFeatureCache((int) $item->tenant_id);

        return $this->sendResponse(true, __('Subscription canceled successfully'));
    }

    public function getSubscriptionForUpdatePlan(string $id): array
    {
        try {
            $subscription = Subscription::with(['tenant', 'plan.featureValues.feature', 'featureSnapshots'])
                ->find((int) $id);

            if (!$subscription) {
                return $this->sendResponse(false, __('Subscription not found'));
            }

            // Get all available features
            $allFeatures = Feature::where('is_active', 1)
                ->orderBy('group')
                ->orderBy('key')
                ->get(['id', 'key', 'name', 'description', 'value_type', 'group']);

            // Get current subscription features ONLY from snapshots (no plan defaults)
            $currentFeatures = [];
            foreach ($subscription->featureSnapshots as $snapshot) {
                $jsonValue = $snapshot->feature_value_json;
                // Handle both array (already decoded) and string (needs decoding)
                if (is_array($jsonValue)) {
                    $decodedValue = $jsonValue;
                } else {
                    $decodedValue = json_decode($jsonValue, true);
                }

                $currentFeatures[$snapshot->feature_key] = [
                    'type' => $snapshot->feature_type,
                    'value' => $decodedValue,
                    'source' => 'snapshot'
                ];
            }

            // Organize features by category
            $featuresByCategory = $this->organizeFeaturesByCategory($allFeatures, $currentFeatures);

            return $this->sendResponse(true, __('Subscription data retrieved successfully.'), [
                'subscription' => $subscription,
                'featuresByCategory' => $featuresByCategory,
                'currentFeatures' => $currentFeatures,
                'allFeatures' => $allFeatures,
            ]);
        } catch (Throwable $e) {
            logStore('SubscriptionService getSubscriptionForUpdatePlan', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong'), [], 500, $e->getMessage());
        }
    }

    public function updateSubscriptionPlanFeatures(Request $request, string $id): array
    {
        try {
            $subscription = Subscription::with(['plan.featureValues.feature'])->find((int) $id);

            if (!$subscription) {
                return $this->sendResponse(false, __('Subscription not found'));
            }

            return DB::transaction(function () use ($request, $subscription, $id) {
                $selectedFeatures = $request->input('features', []);

                // Log for debugging
                logStore('Updating features', [
                    'subscription_id' => $id,
                    'selected_features' => $selectedFeatures
                ]);

                // Delete existing snapshots - this should work correctly
                $deletedCount = SubscriptionFeatureSnapshot::where('subscription_id', (int) $id)->delete();

                logStore('Deleted existing snapshots', ['deleted_count' => $deletedCount]);

                // Create new snapshots only for selected features
                $addedCount = 0;
                foreach ($selectedFeatures as $featureKey) {
                    $feature = Feature::where('key', $featureKey)->first();
                    if (!$feature) {
                        logStore('Feature not found', ['feature_key' => $featureKey]);
                        continue;
                    }

                    // Get value from plan or default
                    $planFeature = $subscription->plan->featureValues->firstWhere('feature_id', $feature->id);

                    $value = $planFeature ? $this->getPlanFeatureValue($planFeature, $feature->value_type) : true;

                    SubscriptionFeatureSnapshot::create([
                        'subscription_id' => (int) $id,
                        'feature_key' => $featureKey,
                        'feature_type' => $feature->value_type,
                        'feature_value_json' => ['value' => $value]
                    ]);
                    $addedCount++;
                }

                // Clear cache for this tenant
                Cache::forget('tenant_feature_map_' . $subscription->tenant_id);

                logStore('Feature update completed', [
                    'subscription_id' => $id,
                    'deleted_count' => $deletedCount,
                    'added_count' => $addedCount
                ]);

                return $this->sendResponse(true, __('Subscription features updated successfully.'), [
                    'total_features' => $addedCount,
                    'subscription_id' => (int) $id,
                    'deleted_count' => $deletedCount
                ]);
            });
        } catch (Throwable $e) {
            logStore('SubscriptionService updateSubscriptionPlanFeatures', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong'), [], 500, $e->getMessage());
        }
    }

    protected function expireRunningSubscriptions(int $tenantId): void
    {
        DB::table('subscriptions')
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['trialing', 'active', 'past_due'])
            ->update([
                'status' => 'expired',
                'ends_at' => now(),
                'auto_renew' => 0,
                'updated_at' => now(),
            ]);
    }

    protected function syncFeatureSnapshots(int $subscriptionId, int $planId): void
    {
        SubscriptionFeatureSnapshot::query()
            ->where('subscription_id', $subscriptionId)
            ->delete();

        $planValues = PlanFeatureValue::query()
            ->where('plan_id', $planId)
            ->with('feature:id,key,value_type')
            ->get();

        foreach ($planValues as $item) {
            if (!$item->feature) {
                continue;
            }

            SubscriptionFeatureSnapshot::query()->create([
                'subscription_id' => $subscriptionId,
                'feature_key' => $item->feature->key,
                'feature_type' => $item->feature->value_type,
                'feature_value_json' => $this->buildSnapshotValue($item->feature->value_type, $item),
            ]);
        }
    }

    protected function buildSnapshotValue(string $type, PlanFeatureValue $item): array
    {
        if ($type === 'boolean') {
            return ['value' => (bool) $item->value_bool];
        }

        if ($type === 'integer') {
            return ['value' => $item->value_int !== null ? (int) $item->value_int : null];
        }

        if ($type === 'decimal') {
            return ['value' => $item->value_decimal !== null ? (float) $item->value_decimal : null];
        }

        if ($type === 'json') {
            return is_array($item->value_json) ? $item->value_json : ['value' => $item->value_json];
        }

        return ['value' => $item->value_text];
    }

    /**
     * Get plan feature value
     */
    protected function getPlanFeatureValue(PlanFeatureValue $planFeature, string $type): mixed
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
     * Organize features by category
     */
    protected function organizeFeaturesByCategory($allFeatures, $currentFeatures): array
    {
        $groupedFeatures = [];

        foreach ($allFeatures as $feature) {
            $group = $feature->group ?? 'ungrouped';
            $groupName = ucwords(str_replace('_', ' ', $group));

            if (!isset($groupedFeatures[$group])) {
                $groupedFeatures[$group] = [
                    'name' => $groupName,
                    'features' => []
                ];
            }

            $isEnabled = isset($currentFeatures[$feature->key]);
            $source = $currentFeatures[$feature->key]['source'] ?? 'available';

            $groupedFeatures[$group]['features'][] = [
                'key' => $feature->key,
                'name' => $feature->name,
                'description' => $feature->description,
                'type' => $feature->value_type,
                'enabled' => $isEnabled,
                'source' => $source,
                'current_value' => $currentFeatures[$feature->key]['value'] ?? null
            ];
        }

        // Remove empty groups
        return array_filter($groupedFeatures, function($group) {
            return !empty($group['features']);
        });
    }

    /**
     * Update subscription plan
     */
    public function updateSubscriptionPlan(Request $request, string $id): array
    {
        try {
            $subscription = Subscription::find((int) $id);

            if (!$subscription) {
                return $this->sendResponse(false, __('Subscription not found'));
            }

            return DB::transaction(function () use ($request, $subscription, $id) {
                $planId = (int) $request->input('plan_id');
                $pricingId = (int) $request->input('plan_pricing_id');

                // Validate plan and pricing
                $pricing = PlanPricing::query()
                    ->where('id', $pricingId)
                    ->where('plan_id', $planId)
                    ->where('is_active', 1)
                    ->first();

                if (!$pricing) {
                    return $this->sendResponse(false, __('Invalid pricing for selected plan'));
                }

                // Update subscription
                $this->subscriptionRepository->update((int) $subscription->id, [
                    'plan_id' => $planId,
                    'plan_pricing_id' => $pricingId,
                ]);

                // Sync features from new plan
                $this->syncFeatureSnapshots((int) $subscription->id, $planId);

                // Clear tenant feature cache
                app(\App\Http\Services\Tenant\TenantFeatureResolverService::class)->clearFeatureCache((int) $subscription->tenant_id);

                return $this->sendResponse(true, __('Subscription plan updated successfully'), [
                    'subscription_id' => (int) $id,
                    'new_plan_id' => $planId,
                    'new_pricing_id' => $pricingId
                ]);
            });
        } catch (Throwable $e) {
            logStore('SubscriptionService updateSubscriptionPlan', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong'), [], 500, $e->getMessage());
        }
    }
}
