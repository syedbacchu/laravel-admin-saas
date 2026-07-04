<?php

namespace App\Http\Services\DatabaseBackup;

use App\Http\Requests\DatabaseBackup\DatabaseBackupCreateRequest;
use Illuminate\Http\Request;

interface DatabaseBackupServiceInterface
{
    public function getDataTableData(Request $request): array;
    public function backupCreateData(Request $request): array;
    public function createDatabaseBackup(DatabaseBackupCreateRequest $request): array;
    public function deleteDatabaseBackup(int $id): array;
    public function downloadBackup(int $id): array;
    public function getBackupStatistics(): array;
}