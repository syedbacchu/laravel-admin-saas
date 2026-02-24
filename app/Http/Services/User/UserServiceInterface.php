<?php

namespace App\Http\Services\User;

use App\Http\Requests\User\UpdatePasswordRequest;
use App\Http\Requests\User\UserCreateRequest;
use App\Http\Services\BaseServiceInterface;

interface UserServiceInterface extends BaseServiceInterface
{

    public function getListData($request): array;
    public function storeOrUpdateData(UserCreateRequest $request): array;
    public function deleteData($id): array;
    public function statusUpdate($id,$status): array;
    public function createData($request): array;
    public function singleData($request): array;
    public function updateProfileProcess($request): array;
    public function updatePasswordProcess(UpdatePasswordRequest $request): array;

}
