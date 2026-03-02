<?php

namespace App\Http\Services\Billing;

use App\Http\Repositories\BaseRepository;
use App\Models\SubscriptionPayment;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class SubscriptionPaymentRepository extends BaseRepository implements SubscriptionPaymentRepositoryInterface
{
    public function __construct(SubscriptionPayment $model)
    {
        parent::__construct($model);
    }

    public function subscriptionPaymentList(Request $request): array
    {
        return DataListManager::list(
            request: $request,
            query: SubscriptionPayment::query(),
            searchable: [
                'tenants.company_name',
                'tenants.company_username',
                'payment_methods.name',
                'plans.name',
                'subscription_payments.payment_reference',
                'subscription_payments.status',
            ],
            filters: [
                'status' => [
                    'column' => 'subscription_payments.status',
                ],
                'payment_method_id' => [
                    'column' => 'subscription_payments.payment_method_id',
                ],
                'tenant_id' => [
                    'column' => 'subscription_payments.tenant_id',
                ],
            ],
            select: [
                'subscription_payments.id',
                'subscription_payments.subscription_id',
                'subscription_payments.tenant_id',
                'subscription_payments.payment_method_id',
                'subscription_payments.amount',
                'subscription_payments.currency',
                'subscription_payments.status',
                'subscription_payments.payment_reference',
                'subscription_payments.paid_at',
                'subscription_payments.verified_at',
                'subscription_payments.created_at',
                'tenants.company_name',
                'tenants.company_username',
                'payment_methods.name as payment_method_name',
                'plans.name as plan_name',
            ],
            joinCallback: function ($query) {
                $query->leftJoin('tenants', 'tenants.id', '=', 'subscription_payments.tenant_id')
                    ->leftJoin('payment_methods', 'payment_methods.id', '=', 'subscription_payments.payment_method_id')
                    ->leftJoin('subscriptions', 'subscriptions.id', '=', 'subscription_payments.subscription_id')
                    ->leftJoin('plans', 'plans.id', '=', 'subscriptions.plan_id');
            }
        );
    }

    public function createSubscriptionPayment(array $data): Model
    {
        return $this->create($data);
    }
}

