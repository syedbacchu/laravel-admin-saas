<?php

namespace App\Http\Services\TenantStaff;

use App\Enums\UserRole;
use App\Http\Repositories\BaseRepository;
use App\Models\User;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class TenantStaffRepository extends BaseRepository implements TenantStaffRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function staffList(Request $request, int $ownerUserId): array
    {
        $request->merge([
            'orderColumn' => $request->get('orderColumn', 'id'),
            'orderBy' => $request->get('orderBy', 'desc'),
            'user_type' => $request->get('user_type', 'staff'),
        ]);

        return DataListManager::list(
            request: $request,
            query: User::query()->with('role:id,name,guard,status')
                ->where('role_module', enum(UserRole::USER_ROLE))
                ->where('parent_id', $ownerUserId),
            searchable: [
                'name',
                'username',
                'email',
                'phone',
            ],
            filters: [
                'status' => [
                    'column' => 'status',
                ],
                'enable_login' => [
                    'column' => 'enable_login',
                ],
                'user_type' => [
                    'column' => 'user_type',
                ],
            ],
            select: [
                'id',
                'parent_id',
                'name',
                'username',
                'email',
                'phone',
                'role_module',
                'user_type',
                'tenant_driver_id',
                'role_id',
                'enable_login',
                'status',
                'created_at',
                'updated_at',
            ],
        );
    }

    public function createTenantUser(array $data): Model
    {
        return $this->create($data);
    }

    public function findStaff(int $ownerUserId, int $id): ?User
    {
        return User::query()
            ->with('role:id,name,guard,status')
            ->where('role_module', enum(UserRole::USER_ROLE))
            ->where('parent_id', $ownerUserId)
            ->where('id', $id)
            ->first();
    }

    public function findDriverUser(int $ownerUserId, int $tenantDriverId): ?User
    {
        return User::query()
            ->with('role:id,name,guard,status')
            ->where('role_module', enum(UserRole::USER_ROLE))
            ->where('parent_id', $ownerUserId)
            ->where('user_type', 'driver')
            ->where('tenant_driver_id', $tenantDriverId)
            ->first();
    }

    public function findTenantUserByIdentifier(int $ownerUserId, string $field, string $value, ?int $ignoreUserId = null): ?User
    {
        $allowedFields = ['email', 'phone', 'username'];
        if (!in_array($field, $allowedFields, true) || trim($value) === '') {
            return null;
        }

        return User::query()
            ->where('role_module', enum(UserRole::USER_ROLE))
            ->where(function ($query) use ($ownerUserId) {
                $query->where('id', $ownerUserId)
                    ->orWhere('parent_id', $ownerUserId);
            })
            ->when($ignoreUserId, fn ($query) => $query->where('id', '!=', $ignoreUserId))
            ->where($field, $value)
            ->first();
    }

    public function usernameExists(string $username, ?int $ignoreUserId = null): bool
    {
        return User::query()
            ->where('username', $username)
            ->when($ignoreUserId, fn ($query) => $query->where('id', '!=', $ignoreUserId))
            ->exists();
    }
}
