<?php

namespace App\Http\Services\User;

use App\Http\Repositories\BaseRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function dataListJoin($request): array;
    public function dataList(Request $request): array;
    public function createData(array $data): Model;
    public function getUserByAny(string|int $value): ?Model;
}
