<?php

namespace App\Http\Services\TenantFile;

use App\Http\Repositories\BaseRepositoryInterface;
use App\Models\TenantFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface TenantFileRepositoryInterface extends BaseRepositoryInterface
{
    public function fileList(Request $request): array;
    public function createTenantFile(array $data): Model;
    public function findTenantFile(int $id): ?TenantFile;
}
