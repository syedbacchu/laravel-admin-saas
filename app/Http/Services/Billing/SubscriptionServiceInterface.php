<?php

namespace App\Http\Services\Billing;

use App\Http\Requests\Billing\SubscriptionCreateRequest;

interface SubscriptionServiceInterface
{
    public function getDataTableData($request): array;
    public function subscriptionCreateData($request): array;
    public function subscriptionEditData($id): array;
    public function storeOrUpdateSubscription(SubscriptionCreateRequest $request): array;
    public function deleteSubscription($id): array;
}
