<?php

namespace App\Http\Services\Billing;

use App\Http\Requests\Billing\SubscriptionCreateRequest;
use App\Http\Services\BaseService;
use App\Models\Plan;
use App\Models\PlanFeatureValue;
use App\Models\PlanPricing;
use App\Models\SubscriptionFeatureSnapshot;
use App\Models\Tenant;
use Carbon\Carbon;
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
}
