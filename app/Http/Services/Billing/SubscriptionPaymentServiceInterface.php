<?php

namespace App\Http\Services\Billing;

use App\Http\Requests\Billing\SubscriptionPaymentCreateRequest;

interface SubscriptionPaymentServiceInterface
{
    public function getDataTableData($request): array;
    public function subscriptionPaymentCreateData($request): array;
    public function subscriptionPaymentEditData($id): array;
    public function storeOrUpdateSubscriptionPayment(SubscriptionPaymentCreateRequest $request): array;
    public function deleteSubscriptionPayment($id): array;
    public function paymentReportData($request): array;
}

