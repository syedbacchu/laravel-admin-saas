<?php

namespace App\Http\Services\Billing;

use App\Http\Repositories\BaseRepository;
use App\Models\PaymentMethod;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class PaymentMethodRepository extends BaseRepository implements PaymentMethodRepositoryInterface
{
    public function __construct(PaymentMethod $model)
    {
        parent::__construct($model);
    }

    public function paymentMethodList(Request $request): array
    {
        return DataListManager::list(
            request: $request,
            query: PaymentMethod::query(),
            searchable: [
                'code',
                'name',
            ],
            filters: [
                'is_active' => [
                    'column' => 'is_active',
                ],
            ],
            select: [
                'id',
                'code',
                'name',
                'is_active',
                'sort_order',
                'created_at',
            ],
        );
    }

    public function createPaymentMethod(array $data): Model
    {
        return $this->create($data);
    }
}

