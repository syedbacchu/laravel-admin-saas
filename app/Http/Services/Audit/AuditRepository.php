<?php

namespace App\Http\Services\Audit;

use App\Http\Repositories\BaseRepository;
use App\Models\AuditLog;
use App\Models\Role;
use App\Support\DataListManager;

class AuditRepository extends BaseRepository implements AuditRepositoryInterface
{
    public function __construct(AuditLog $model)
    {
        parent::__construct($model);
    }

    public function getDataTableQuery($request): array
    {
        return DataListManager::list(
            request: $request,
            query: AuditLog::query()->with('user'),

            searchable: [
                'event',
                'model_type',
                'old_value',
                'new_value',
            ],

            filters: [
                'model_type' => [
                    'column' => 'model_type',
                ],
                'user_id' => [
                    'column' => 'user_id',
                ],
                'event' => [
                    'column' => 'event',
                ],
            ],

            select: [
                'id',
                'user_id',
                'event',
                'model_type',
                'ip_address',
                'user_agent',
                'old_values',
                'new_values',
                'created_at',
            ],
        );
    }

    public function deleteData($id): mixed
    {
        return $this->delete($id);
    }

}
