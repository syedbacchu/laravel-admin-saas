<?php

namespace App\Http\Services\User;

use App\Http\Repositories\BaseRepository;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\DataListManager;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;


class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function dataListJoin($request): array
    {
        return DataListManager::list(
            request: $request,
            query: User::query(),
            searchable: [
                'users.name',
                'users.email',
                'roles.name',
            ],
            filters: [
                'status' => 'users.status',
            ],
            select: [
                'users.id',
                'users.name',
                'users.email',
                'roles.name as role_name',
            ],
            notIn:isset($request->notIn) ? $request->notIn : [],
            joinCallback: function ($query) {
                $query->leftJoin('roles', 'roles.id', '=', 'users.role_id');
            }
        );
    }
    public function dataList($request): array
    {
        return DataListManager::list(
            request: $request,
            query: User::query(),

            searchable: [
                'users.name',
                'users.email',
                'users.phone',
                'users.username',
            ],

            filters: [
                'status' => [
                    'column' => 'users.status'
                ],
                'role_module' => [
                    'column' => 'users.role_module'
                ],
                'created_at' => [
                    'column' => 'users.created_at',
                    'type' => 'date'
                ],
                'created_range' => [
                    'column' => 'users.created_at',
                    'type' => 'daterange'
                ],
            ],

            select: [
                'users.id',
                'users.name',
                'users.phone',
                'users.username',
                'users.image',
                'users.role_module',
                'users.role_id',
                'users.status',
                'users.created_at',
            ],
            notIn:isset($request->notIn) ? $request->notIn : [],
        );
    }

    public function createData(array $data): Model
    {
        return $this->create($data);
    }

    public function getUserByAny(string|int $value): ?Model
    {
        if (is_numeric($value)) {
            $user = User::find($value);
            if ($user) {
                return $user;
            }
        }

        return User::where('phone', $value)
            ->orWhere('email', $value)
            ->orWhere('username', $value)
            ->first();
    }

}
