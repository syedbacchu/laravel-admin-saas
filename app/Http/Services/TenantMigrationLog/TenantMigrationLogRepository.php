<?php

namespace App\Http\Services\TenantMigrationLog;

use App\Http\Repositories\BaseRepository;
use App\Models\TenantMigrationLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class TenantMigrationLogRepository extends BaseRepository implements TenantMigrationLogRepositoryInterface
{
    public function __construct(TenantMigrationLog $model)
    {
        parent::__construct($model);
    }

    public function createLog(array $data): Model
    {
        return $this->model->create($data);
    }

    public function updateLog(int $id, array $data): bool
    {
        return $this->model->where('id', $id)->update($data);
    }

    public function getByTenantId(int $tenantId): array
    {
        return $this->model->byTenant($tenantId)
            ->with(['tenant', 'performedBy'])
            ->recent()
            ->get()
            ->toArray();
    }

    public function getRecentLogs(int $limit = 10): array
    {
        return $this->model->recent()
            ->with(['tenant', 'performedBy'])
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getLogById(int $id): ?Model
    {
        return $this->model->find($id);
    }
}
