<?php

namespace App\Http\Services\TenantMigrationLog;

interface TenantMigrationLogServiceInterface
{
    public function createMigrationLog(array $data): array;
    public function updateMigrationLog(int $logId, array $data): array;
    public function getTenantMigrationLogs(int $tenantId): array;
    public function getRecentMigrationLogs(int $limit = 10): array;
    public function getMigrationLogById(int $logId): array;
    public function logMigrationStart(int $tenantId, string $migrationType, string $reason = null): int;
    public function logMigrationComplete(int $logId, string $output, int $migrationsRun = 0): bool;
    public function logMigrationFailure(int $logId, string $errorMessage, string $output = null): bool;
}
