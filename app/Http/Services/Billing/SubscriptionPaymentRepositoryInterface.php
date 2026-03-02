<?php

namespace App\Http\Services\Billing;

use App\Http\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface SubscriptionPaymentRepositoryInterface extends BaseRepositoryInterface
{
    public function subscriptionPaymentList(Request $request): array;
    public function createSubscriptionPayment(array $data): Model;
}

