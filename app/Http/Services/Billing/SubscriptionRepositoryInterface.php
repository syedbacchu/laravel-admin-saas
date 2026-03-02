<?php

namespace App\Http\Services\Billing;

use App\Http\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface SubscriptionRepositoryInterface extends BaseRepositoryInterface
{
    public function subscriptionList(Request $request): array;
    public function createSubscription(array $data): Model;
}
