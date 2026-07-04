<?php

namespace App\Http\Services\DatabaseBackup;

use App\Http\Repositories\BaseRepository;
use App\Models\DatabaseBackup;
use App\Support\DataListManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class DatabaseBackupRepository extends BaseRepository implements DatabaseBackupRepositoryInterface
{
    public function __construct(DatabaseBackup $model)
    {
        parent::__construct($model);
    }

    public function backupList(Request $request): array
    {
        return DataListManager::list(
            request: $request,
            query: DatabaseBackup::query(),
            searchable: ['file_name', 'database_name'],
            filters: [
                'status' => ['column' => 'status'],
                'database_name' => ['column' => 'database_name'],
            ],
            select: [
                'id',
                'file_name',
                'file_path',
                'file_size',
                'database_name',
                'backup_created_at',
                'status',
                'created_at',
            ],
        );
    }

    public function createBackup(array $data): DatabaseBackup
    {
        return $this->create($data);
    }

    public function getRecentBackups(int $limit = 10): Collection
    {
        return DatabaseBackup::query()
            ->active()
            ->orderBy('backup_created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function deleteBackup(int $id): bool
    {
        $backup = $this->find($id);
        if ($backup && $backup->backup_exists) {
            // Delete physical file
            if (file_exists($backup->file_path)) {
                unlink($backup->file_path);
            }
        }
        return $this->delete($id);
    }

    public function getBackupByFileName(string $fileName): ?DatabaseBackup
    {
        return DatabaseBackup::query()->where('file_name', $fileName)->first();
    }
}
