<?php

namespace App\Http\Services\TenantDriver;

use App\Http\Requests\TenantApi\TenantDriverCreateRequest;
use Illuminate\Http\Request;

interface TenantDriverServiceInterface
{
    public function driverList(Request $request): array;
    public function storeDriver(TenantDriverCreateRequest $request): array;
    public function driverDetails(Request $request, int $id): array;
    public function deleteDriver(Request $request, int $id): array;
}
