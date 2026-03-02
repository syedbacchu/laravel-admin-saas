<?php

namespace App\Http\Services\TenantApi;

use App\Enums\UserRole;
use App\Http\Repositories\BaseRepository;
use App\Models\Tenant;
use App\Models\User;

class TenantApiRepository extends BaseRepository implements TenantApiRepositoryInterface
{
    public function __construct(Tenant $model)
    {
        parent::__construct($model);
    }

    public function findTenantByUsername(string $companyUsername): ?Tenant
    {
        return Tenant::query()
            ->with(['owner:id,name,username,email,phone,status,enable_login'])
            ->where('company_username', $companyUsername)
            ->first();
    }

    public function findTenantUserByLogin(Tenant $tenant, string $login): ?User
    {
        return User::query()
            ->where('role_module', enum(UserRole::USER_ROLE))
            ->where(function ($query) use ($tenant) {
                $query->where('id', (int) $tenant->owner_user_id)
                    ->orWhere('parent_id', (int) $tenant->owner_user_id);
            })
            ->where(function ($query) use ($login) {
                $query->where('email', $login)
                    ->orWhere('username', $login)
                    ->orWhere('phone', $login);
            })
            ->first();
    }

    public function findTenantByUser(User $user): ?Tenant
    {
        return Tenant::query()
            ->where(function ($query) use ($user) {
                $query->where('owner_user_id', (int) $user->id)
                    ->orWhere('owner_user_id', (int) $user->parent_id);
            })
            ->first();
    }
}

