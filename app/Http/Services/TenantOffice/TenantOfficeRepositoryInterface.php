<?php

namespace App\Http\Services\TenantOffice;

use App\Http\Repositories\BaseRepositoryInterface;
use App\Models\Tenant\TenantOffice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface TenantOfficeRepositoryInterface extends BaseRepositoryInterface
{
    public function officeList(Request $request): array;
    public function createOffice(array $data): Model;
    public function findOffice(int $id): ?TenantOffice;
}

