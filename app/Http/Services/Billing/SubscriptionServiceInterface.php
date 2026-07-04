<?php

namespace App\Http\Services\Billing;

use App\Http\Requests\Billing\SubscriptionCreateRequest;
use Illuminate\Http\Request;

interface SubscriptionServiceInterface
{
    public function getDataTableData($request): array;
    public function subscriptionCreateData($request): array;
    public function subscriptionEditData($id): array;
    public function storeOrUpdateSubscription(SubscriptionCreateRequest $request): array;
    public function deleteSubscription($id): array;

    // Update plan features methods
    public function getSubscriptionForUpdatePlan(string $id): array;
    public function updateSubscriptionPlanFeatures(Request $request, string $id): array;
    public function updateSubscriptionPlan(Request $request, string $id): array;
}
