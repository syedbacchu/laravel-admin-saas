<?php

namespace App\Http\Services\Audit;

use App\Http\Repositories\BaseRepositoryInterface;
use Illuminate\Http\Request;

interface AuditRepositoryInterface extends BaseRepositoryInterface
{
    public function getDataTableQuery(Request $request): array;
    public function deleteData($id): mixed;
}
