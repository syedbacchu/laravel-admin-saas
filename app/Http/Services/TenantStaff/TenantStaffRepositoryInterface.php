<?php

namespace App\Http\Services\TenantStaff;

use App\Http\Repositories\BaseRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface TenantStaffRepositoryInterface extends BaseRepositoryInterface
{
    public function staffList(Request $request, int $ownerUserId): array;
    public function createTenantUser(array $data): Model;
    public function findStaff(int $ownerUserId, int $id): ?User;
    public function findDriverUser(int $ownerUserId, int $tenantDriverId): ?User;
    public function findTenantUserByIdentifier(int $ownerUserId, string $field, string $value, ?int $ignoreUserId = null): ?User;
    public function usernameExists(string $username, ?int $ignoreUserId = null): bool;
}
