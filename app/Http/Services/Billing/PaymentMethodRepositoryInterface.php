<?php

namespace App\Http\Services\Billing;

use App\Http\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface PaymentMethodRepositoryInterface extends BaseRepositoryInterface
{
    public function paymentMethodList(Request $request): array;
    public function createPaymentMethod(array $data): Model;
}

