<?php

namespace App\Http\Services\TenantCustomer;

use App\Http\Repositories\BaseRepositoryInterface;
use App\Models\TenantCustomer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface TenantCustomerRepositoryInterface extends BaseRepositoryInterface
{
    public function customerList(Request $request): array;
    public function createCustomer(array $data): Model;
    public function findCustomer(int $id): ?TenantCustomer;
}

