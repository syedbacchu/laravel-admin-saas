<?php

namespace App\Http\Services\TenantDriver;

use App\Http\Repositories\BaseRepositoryInterface;
use App\Models\TenantDriver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface TenantDriverRepositoryInterface extends BaseRepositoryInterface
{
    public function driverList(Request $request): array;
    public function createDriver(array $data): Model;
    public function findDriver(int $id): ?TenantDriver;
}
