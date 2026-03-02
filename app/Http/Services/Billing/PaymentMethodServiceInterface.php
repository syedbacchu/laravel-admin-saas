<?php

namespace App\Http\Services\Billing;

use App\Http\Requests\Billing\PaymentMethodCreateRequest;

interface PaymentMethodServiceInterface
{
    public function getDataTableData($request): array;
    public function paymentMethodCreateData($request): array;
    public function paymentMethodEditData($id): array;
    public function storeOrUpdatePaymentMethod(PaymentMethodCreateRequest $request): array;
    public function deletePaymentMethod($id): array;
    public function publishPaymentMethod($id, $status): array;
}

