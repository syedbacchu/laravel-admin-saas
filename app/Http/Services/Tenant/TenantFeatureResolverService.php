<?php

namespace App\Http\Services\Tenant;

use App\Models\PlanFeatureValue;
use App\Models\Subscription;
use Illuminate\Support\Facades\Cache;

class TenantFeatureResolverService
{
    public function getActiveSubscription(int $tenantId): ?Subscription
    {
        Subscription::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['trialing', 'active', 'past_due'])
            ->where('ends_at', '<', now())
            ->update([
                'status' => 'expired',
                'auto_renew' => 0,
                'updated_at' => now(),
            ]);

        return Subscription::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['trialing', 'active', 'past_due'])
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->latest('id')
            ->first();
    }

    public function getFeatureMap(int $tenantId, bool $forceRefresh = false): array
    {
        $cacheKey = $this->cacheKey($tenantId);
        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, 600, function () use ($tenantId) {
            $subscription = $this->getActiveSubscription($tenantId);
            if (!$subscription) {
                return [];
            }

            $snapshots = $subscription->featureSnapshots()->get(['feature_key', 'feature_type', 'feature_value_json']);
            if ($snapshots->isNotEmpty()) {
                $map = [];
                foreach ($snapshots as $snapshot) {
                    $map[$snapshot->feature_key] = $this->extractSnapshotValue($snapshot->feature_value_json, $snapshot->feature_type);
                }
                return $map;
            }

            $planValues = PlanFeatureValue::query()
                ->where('plan_id', $subscription->plan_id)
                ->with('feature:id,key,value_type')
                ->get();

            $map = [];
            foreach ($planValues as $item) {
                if (!$item->feature) {
                    continue;
                }
                $map[$item->feature->key] = $this->extractPlanValue($item, $item->feature->value_type);
            }

            return $map;
        });
    }

    public function getValue(int $tenantId, string $featureKey, mixed $default = null): mixed
    {
        $map = $this->getFeatureMap($tenantId);
        return array_key_exists($featureKey, $map) ? $map[$featureKey] : $default;
    }

    public function canUse(int $tenantId, string $featureKey): bool
    {
        $value = $this->getValue($tenantId, $featureKey, false);
        return (bool) $value;
    }

    public function withinLimit(int $tenantId, string $featureKey, int|float $currentCount): bool
    {
        $limit = $this->getValue($tenantId, $featureKey, null);
        if ($limit === null || $limit === '') {
            return true;
        }

        return (float) $currentCount < (float) $limit;
    }

    public function clearFeatureCache(int $tenantId): void
    {
        Cache::forget($this->cacheKey($tenantId));
    }

    protected function extractSnapshotValue(mixed $jsonValue, string $type): mixed
    {
        if (is_array($jsonValue) && array_key_exists('value', $jsonValue) && $type !== 'json') {
            return $jsonValue['value'];
        }

        return $jsonValue;
    }

    protected function extractPlanValue(PlanFeatureValue $item, string $type): mixed
    {
        if ($type === 'boolean') {
            return (bool) $item->value_bool;
        }
        if ($type === 'integer') {
            return $item->value_int !== null ? (int) $item->value_int : null;
        }
        if ($type === 'decimal') {
            return $item->value_decimal !== null ? (float) $item->value_decimal : null;
        }
        if ($type === 'json') {
            return $item->value_json;
        }

        return $item->value_text;
    }

    protected function cacheKey(int $tenantId): string
    {
        return 'tenant_feature_map_' . $tenantId;
    }
}
