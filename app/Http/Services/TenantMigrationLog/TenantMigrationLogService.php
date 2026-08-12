<?php

namespace App\Http\Services\TenantMigrationLog;

use App\Http\Services\TenantMigrationLog\TenantMigrationLogRepositoryInterface;
use App\Http\Services\BaseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class TenantMigrationLogService extends BaseService implements TenantMigrationLogServiceInterface
{
    protected TenantMigrationLogRepositoryInterface $migrationLogRepository;

    public function __construct(TenantMigrationLogRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->migrationLogRepository = $repository;
    }

    public function createMigrationLog(array $data): array
    {
        try {
            // Add user context and request metadata
            $data['performed_by'] = Auth::id();
            $data['ip_address'] = Request::ip();
            $data['user_agent'] = Request::header('User-Agent');

            $log = $this->migrationLogRepository->createLog($data);

            return $this->sendResponse(true, 'Migration log created successfully', $log->toArray());
        } catch (\Exception $e) {
            return $this->sendResponse(false, 'Failed to create migration log: ' . $e->getMessage(), [], 500, $e->getMessage());
        }
    }

    public function updateMigrationLog(int $logId, array $data): array
    {
        try {
            $updated = $this->migrationLogRepository->updateLog($logId, $data);

            if (!$updated) {
                return $this->sendResponse(false, 'Migration log not found');
            }

            $log = $this->migrationLogRepository->getLogById($logId);

            return $this->sendResponse(true, 'Migration log updated successfully', $log->toArray());
        } catch (\Exception $e) {
            return $this->sendResponse(false, 'Failed to update migration log: ' . $e->getMessage(), [], 500, $e->getMessage());
        }
    }

    public function getTenantMigrationLogs(int $tenantId): array
    {
        try {
            $logs = $this->migrationLogRepository->getByTenantId($tenantId);

            return $this->sendResponse(true, 'Tenant migration logs retrieved successfully', $logs);
        } catch (\Exception $e) {
            return $this->sendResponse(false, 'Failed to retrieve migration logs: ' . $e->getMessage(), [], 500, $e->getMessage());
        }
    }

    public function getRecentMigrationLogs(int $limit = 10): array
    {
        try {
            $logs = $this->migrationLogRepository->getRecentLogs($limit);

            return $this->sendResponse(true, 'Recent migration logs retrieved successfully', $logs);
        } catch (\Exception $e) {
            return $this->sendResponse(false, 'Failed to retrieve recent migration logs: ' . $e->getMessage(), [], 500, $e->getMessage());
        }
    }

    public function getMigrationLogById(int $logId): array
    {
        try {
            $log = $this->migrationLogRepository->getLogById($logId);

            if (!$log) {
                return $this->sendResponse(false, 'Migration log not found');
            }

            return $this->sendResponse(true, 'Migration log retrieved successfully', $log->toArray());
        } catch (\Exception $e) {
            return $this->sendResponse(false, 'Failed to retrieve migration log: ' . $e->getMessage(), [], 500, $e->getMessage());
        }
    }

    public function logMigrationStart(int $tenantId, string $migrationType, string $reason = null): int
    {
        $data = [
            'tenant_id' => $tenantId,
            'migration_type' => $migrationType,
            'status' => 'running',
            'command' => $migrationType === 'fresh' ? 'tenant:migrate:fresh' : 'tenant:migrate',
            'reason' => $reason,
            'started_at' => now(),
            'performed_by' => Auth::id(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::header('User-Agent'),
        ];

        $log = $this->migrationLogRepository->createLog($data);

        return $log->id;
    }

    public function logMigrationComplete(int $logId, string $output, int $migrationsRun = 0): bool
    {
        $log = $this->migrationLogRepository->getLogById($logId);

        if (!$log) {
            return false;
        }

        $executionTime = now()->diffInSeconds($log->started_at);

        $data = [
            'status' => 'completed',
            'output' => $output,
            'migrations_run' => $migrationsRun,
            'completed_at' => now(),
            'execution_time' => $executionTime,
        ];

        return $this->migrationLogRepository->updateLog($logId, $data);
    }

    public function logMigrationFailure(int $logId, string $errorMessage, string $output = null): bool
    {
        $log = $this->migrationLogRepository->getLogById($logId);

        if (!$log) {
            return false;
        }

        $executionTime = $log->started_at ? now()->diffInSeconds($log->started_at) : null;

        $data = [
            'status' => 'failed',
            'error_message' => $errorMessage,
            'output' => $output,
            'completed_at' => now(),
            'execution_time' => $executionTime,
        ];

        return $this->migrationLogRepository->updateLog($logId, $data);
    }
}
