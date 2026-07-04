<?php

namespace App\Http\Services\TenantCustomer;

use App\Http\Requests\TenantApi\TenantCustomerCreateRequest;
use Illuminate\Http\Request;

interface TenantCustomerServiceInterface
{
    public function customerList(Request $request): array;
    public function storeCustomer(TenantCustomerCreateRequest $request): array;
    public function customerDetails(Request $request, int $id): array;
    public function deleteCustomer(Request $request, int $id): array;
    public function addCustomerAddress(Request $request, int $customerId): array;
}

