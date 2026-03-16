<?php

namespace App\Http\Services\TenantDriver;

use App\Http\Repositories\BaseRepositoryInterface;
use App\Models\TenantDriver;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface TenantDriverRepositoryInterface extends BaseRepositoryInterface
{
    public function driverList(Request $request): array;
    public function createDriver(array $data): Model;
    public function findDriver(int $id): ?TenantDriver;
    public function getDriverLoginMap(int $ownerUserId, array $driverIds): array;
    public function findDriverLoginUser(int $ownerUserId, int $driverId): ?User;
    public function updateDriverLoginUser(int $userId, array $data): bool;
    public function findTenantUserByIdentifier(int $ownerUserId, string $field, string $value, ?int $ignoreUserId = null): ?User;
    public function usernameExists(string $username, ?int $ignoreUserId = null): bool;
}
