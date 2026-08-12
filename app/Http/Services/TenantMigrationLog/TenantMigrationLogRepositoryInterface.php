<?php

namespace App\Http\Services\TenantMigrationLog;

use App\Http\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

interface TenantMigrationLogRepositoryInterface extends BaseRepositoryInterface
{
    public function createLog(array $data): Model;
    public function updateLog(int $id, array $data): bool;
    public function getByTenantId(int $tenantId): array;
    public function getRecentLogs(int $limit = 10): array;
    public function getLogById(int $id): ?Model;
}
