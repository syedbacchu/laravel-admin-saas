<?php

namespace App\Http\Services\DatabaseBackup;

use App\Http\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface DatabaseBackupRepositoryInterface extends BaseRepositoryInterface
{
    public function backupList(Request $request): array;
    public function createBackup(array $data): \App\Models\DatabaseBackup;
    public function getRecentBackups(int $limit = 10): Collection;
    public function deleteBackup(int $id): bool;
    public function getBackupByFileName(string $fileName): ?\App\Models\DatabaseBackup;
}