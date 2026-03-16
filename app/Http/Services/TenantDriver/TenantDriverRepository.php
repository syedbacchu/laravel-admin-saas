<?php

namespace App\Http\Services\TenantDriver;

use App\Enums\UserRole;
use App\Http\Repositories\BaseRepository;
use App\Models\TenantDriver;
use App\Models\User;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class TenantDriverRepository extends BaseRepository implements TenantDriverRepositoryInterface
{
    public function __construct(TenantDriver $model)
    {
        parent::__construct($model);
    }

    public function driverList(Request $request): array
    {
        return DataListManager::list(
            request: $request,
            query: TenantDriver::query()->with('vehicle:id,registration_no,vehicle_type,brand,model'),
            searchable: [
                'name',
                'phone',
                'license_no',
                'nid_no',
            ],
            filters: [
                'status' => [
                    'column' => 'status',
                ],
                'vehicle_id' => [
                    'column' => 'vehicle_id',
                ],
            ],
            select: [
                'id',
                'vehicle_id',
                'name',
                'phone',
                'license_no',
                'nid_no',
                'joining_date',
                'address',
                'notes',
                'status',
                'created_at',
                'updated_at',
            ],
        );
    }

    public function createDriver(array $data): Model
    {
        return $this->create($data);
    }

    public function findDriver(int $id): ?TenantDriver
    {
        return TenantDriver::query()
            ->with('vehicle:id,registration_no,vehicle_type,brand,model')
            ->find($id);
    }

    public function getDriverLoginMap(int $ownerUserId, array $driverIds): array
    {
        if (empty($driverIds)) {
            return [];
        }

        $records = User::query()
            ->where('role_module', enum(UserRole::USER_ROLE))
            ->where('parent_id', $ownerUserId)
            ->where('user_type', 'driver')
            ->whereIn('tenant_driver_id', $driverIds)
            ->orderByDesc('id')
            ->get([
                'id',
                'tenant_driver_id',
                'name',
                'username',
                'email',
                'phone',
                'enable_login',
                'status',
            ]);

        $map = [];
        foreach ($records as $record) {
            $driverId = (int) $record->tenant_driver_id;
            if ($driverId <= 0 || isset($map[$driverId])) {
                continue;
            }

            $map[$driverId] = [
                'user_id' => (int) $record->id,
                'name' => $record->name,
                'username' => $record->username,
                'email' => $record->email,
                'phone' => $record->phone,
                'enable_login' => (int) $record->enable_login,
                'status' => (int) $record->status,
            ];
        }

        return $map;
    }

    public function findDriverLoginUser(int $ownerUserId, int $driverId): ?User
    {
        return User::query()
            ->where('role_module', enum(UserRole::USER_ROLE))
            ->where('parent_id', $ownerUserId)
            ->where('user_type', 'driver')
            ->where('tenant_driver_id', $driverId)
            ->orderByDesc('id')
            ->first();
    }

    public function updateDriverLoginUser(int $userId, array $data): bool
    {
        $user = User::query()->find($userId);
        if (!$user) {
            return false;
        }

        return $user->update($data);
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
