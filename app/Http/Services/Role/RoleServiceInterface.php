<?php

namespace App\Http\Services\Role;

use App\Http\Requests\Role\RoleCreateRequest;
use App\Http\Services\BaseServiceInterface;

interface RoleServiceInterface extends BaseServiceInterface
{

    public function getDataTableData($request): array;
    public function storeOrUpdateData(RoleCreateRequest $request): array;
    public function deleteData($id): array;
    public function permissionDelete($id): array;
    public function publishPermission($id,$status): array;
    public function statusRole($id,$status): array;
    public function getSinglePermission($id): array;
    public function getPermissionList($request);
    public function roleCreateData($guard): array;
    public function roleEditData($id): array;

}
