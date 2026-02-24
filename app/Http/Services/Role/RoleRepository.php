<?php

namespace App\Http\Services\Role;

use App\Http\Repositories\BaseRepository;
use App\Models\Permission;
use App\Models\Role;
use App\Support\DataListManager;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;


class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    public function __construct(Role $model)
    {
        parent::__construct($model);
    }

    public function dataList($request): array
    {
        return DataListManager::list(
            request: $request,
            query: Role::query(),

            searchable: [
                'name',
                'slug',
                'guard'
            ],

            filters: [
                'status' => [
                    'column' => 'status'
                ],
                'guard' => [
                    'column' => 'guard'
                ],
            ],

            select: [
                'id',
                'name',
                'slug',
                'guard',
                'status',
                'is_system',
                'created_at',
            ],
        );
    }


    public function createData(array $data): Model
    {
        return $this->create($data);
    }

    public function permissionList($request):array
    {
        return DataListManager::list(
            request: $request,
            query: Permission::query(),

            searchable: [
                'name',
                'slug',
                'guard',
                'module'
            ],

            filters: [
                'status' => [
                    'column' => 'status'
                ],
                'guard' => [
                    'column' => 'guard'
                ],
                'module' => [
                    'column' => 'module'
                ],
            ],

            select: [
                'id',
                'name',
                'slug',
                'guard',
                'status',
                'module',
                'created_at',
            ],
        );
    }

    public function getPermission($id): Model {
        return Permission::find($id);
    }

    public function updatePermission($id,array $data): mixed {
        return Permission::where('id',$id)->update($data);
    }

    public function deletePermission($id): mixed {
        return Permission::where('id',$id)->delete();
    }

    public function getModulePermissions($guard = null): Collection {
        $query = Permission::query();

        if (!empty($type)) {
            $query->where('guard', $guard);
        }

        return $query
            ->orderBy('module')
            ->get()
            ->groupBy('module');
    }
}
