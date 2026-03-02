<?php

namespace App\Http\Services\Billing;

use App\Http\Repositories\BaseRepository;
use App\Models\Subscription;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class SubscriptionRepository extends BaseRepository implements SubscriptionRepositoryInterface
{
    public function __construct(Subscription $model)
    {
        parent::__construct($model);
    }

    public function subscriptionList(Request $request): array
    {
        return DataListManager::list(
            request: $request,
            query: Subscription::query(),
            searchable: [
                'tenants.company_name',
                'tenants.company_username',
                'plans.name',
                'subscriptions.status',
            ],
            filters: [
                'status' => [
                    'column' => 'subscriptions.status',
                ],
                'plan_id' => [
                    'column' => 'subscriptions.plan_id',
                ],
            ],
            select: [
                'subscriptions.id',
                'subscriptions.tenant_id',
                'subscriptions.plan_id',
                'subscriptions.plan_pricing_id',
                'subscriptions.status',
                'subscriptions.starts_at',
                'subscriptions.ends_at',
                'subscriptions.auto_renew',
                'tenants.company_name',
                'tenants.company_username',
                'plans.name as plan_name',
                'plan_pricings.term_months',
                'plan_pricings.final_amount',
                'plan_pricings.currency',
            ],
            joinCallback: function ($query) {
                $query->leftJoin('tenants', 'tenants.id', '=', 'subscriptions.tenant_id')
                    ->leftJoin('plans', 'plans.id', '=', 'subscriptions.plan_id')
                    ->leftJoin('plan_pricings', 'plan_pricings.id', '=', 'subscriptions.plan_pricing_id');
            }
        );
    }

    public function createSubscription(array $data): Model
    {
        return $this->create($data);
    }
}
