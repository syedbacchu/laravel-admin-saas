<?php

namespace App\Http\Services\TenantOffice;

use App\Http\Requests\TenantApi\TenantOfficeCreateRequest;
use Illuminate\Http\Request;

interface TenantOfficeServiceInterface
{
    public function officeList(Request $request): array;
    public function storeOffice(TenantOfficeCreateRequest $request): array;
    public function officeDetails(Request $request, int $id): array;
    public function deleteOffice(Request $request, int $id): array;
}

