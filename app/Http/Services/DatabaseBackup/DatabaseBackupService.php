<?php

namespace App\Http\Services\DatabaseBackup;

use App\Http\Requests\DatabaseBackup\DatabaseBackupCreateRequest;
use App\Http\Services\BaseService;
use App\Models\DatabaseBackup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;
use ZipArchive;

class DatabaseBackupService extends BaseService implements DatabaseBackupServiceInterface
{
    protected DatabaseBackupRepositoryInterface $databaseBackupRepository;

    public function __construct(DatabaseBackupRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->databaseBackupRepository = $repository;
    }

    public function getDataTableData(Request $request): array
    {
        $data = $this->databaseBackupRepository->backupList($request);
        return $this->sendResponse(true, __('Backup data retrieved successfully'), $data);
    }

    public function backupCreateData(Request $request): array
    {
        $data = [
            'database_name' => config('database.connections.mysql.database'),
            'database_host' => config('database.connections.mysql.host'),
        ];

        return $this->sendResponse(true, '', $data);
    }

    public function createDatabaseBackup(DatabaseBackupCreateRequest $request): array
    {
        try {
            return DB::transaction(function () use ($request) {
                $databaseName = config('database.connections.mysql.database');
                $databaseHost = config('database.connections.mysql.host');

                // Generate backup filename
                $timestamp = now()->format('Y-m-d-H-i-s');
                $fileName = "backup-{$databaseName}-{$timestamp}.sql.zip";
                $storagePath = storage_path('app/backups/');

                // Ensure backup directory exists
                if (!file_exists($storagePath)) {
                    mkdir($storagePath, 0755, true);
                }

                // Create temporary SQL dump
                $tempSqlFile = $storagePath . "temp-{$timestamp}.sql";

                // Get database credentials
                $dbUsername = config('database.connections.mysql.username');
                $dbPassword = config('database.connections.mysql.password');
                $dbHost = config('database.connections.mysql.host');

                // Create MySQL dump
                $command = sprintf(
                    'mysqldump -h%s -u%s -p%s %s > %s 2>&1',
                    escapeshellarg($dbHost),
                    escapeshellarg($dbUsername),
                    escapeshellarg($dbPassword),
                    escapeshellarg($databaseName),
                    escapeshellarg($tempSqlFile)
                );

                exec($command, $output, $returnCode);

                if ($returnCode !== 0 && !file_exists($tempSqlFile)) {
                    return $this->sendResponse(false, __('Failed to create database dump. Check MySQL credentials and permissions.'));
                }

                // Create password-protected ZIP file
                $zipFilePath = $storagePath . $fileName;
                $zipPassword = env('BACKUP_ZIP_PASSWORD', 'default_secure_password_' . Str::random(16));

                if (!$this->createPasswordProtectedZip($tempSqlFile, $zipFilePath, $zipPassword)) {
                    // Clean up temp file
                    if (file_exists($tempSqlFile)) {
                        unlink($tempSqlFile);
                    }
                    return $this->sendResponse(false, __('Failed to create password-protected backup file.'));
                }

                // Clean up temporary SQL file
                if (file_exists($tempSqlFile)) {
                    unlink($tempSqlFile);
                }

                // Get file size
                $fileSize = filesize($zipFilePath);

                // Create database record
                $backupData = [
                    'file_name' => $fileName,
                    'file_path' => $zipFilePath,
                    'file_size' => $fileSize,
                    'database_name' => $databaseName,
                    'database_host' => $databaseHost,
                    'backup_created_at' => now(),
                    'status' => 1,
                    'description' => $request->description ?? null,
                ];

                $backup = $this->databaseBackupRepository->createBackup($backupData);

                return $this->sendResponse(true, __('Database backup created successfully'), [
                    'backup' => $backup,
                    'file_size' => $this->formatFileSize($fileSize),
                    'password_protected' => true,
                ]);
            });
        } catch (Throwable $e) {
            logStore('DatabaseBackupService', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong during backup creation'), [], 500, $e->getMessage());
        }
    }

    protected function createPasswordProtectedZip(string $sourceFile, string $destinationZip, string $password): bool
    {
        try {
            $zip = new ZipArchive();

            if ($zip->open($destinationZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                return false;
            }

            // Add file to ZIP with password protection
            if (!$zip->addFile($sourceFile, basename($sourceFile))) {
                $zip->close();
                return false;
            }

            // Set password for the ZIP
            if (!$zip->setPassword($password)) {
                $zip->close();
                return false;
            }

            // Set compression method
            $zip->setCompressionName(basename($sourceFile), ZipArchive::CM_DEFLATE);

            if ($zip->close() !== true) {
                return false;
            }

            return file_exists($destinationZip) && filesize($destinationZip) > 0;
        } catch (Throwable $e) {
            logStore('PasswordProtectedZip', $e->getMessage());
            return false;
        }
    }

    public function deleteDatabaseBackup(int $id): array
    {
        try {
            $backup = $this->databaseBackupRepository->find($id);
            if (!$backup) {
                return $this->sendResponse(false, __('Backup not found'));
            }

            // Delete physical file if exists
            if ($backup->backup_exists && file_exists($backup->file_path)) {
                unlink($backup->file_path);
            }

            // Delete database record
            $this->databaseBackupRepository->delete($id);

            return $this->sendResponse(true, __('Backup deleted successfully'));
        } catch (Throwable $e) {
            logStore('DatabaseBackupService', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong during backup deletion'), [], 500, $e->getMessage());
        }
    }

    public function downloadBackup(int $id): array
    {
        try {
            $backup = $this->databaseBackupRepository->find($id);
            if (!$backup) {
                return $this->sendResponse(false, __('Backup not found'));
            }

            if (!$backup->backup_exists) {
                return $this->sendResponse(false, __('Backup file not found on server'));
            }

            return $this->sendResponse(true, __('Backup file ready for download'), [
                'file_path' => $backup->file_path,
                'file_name' => $backup->file_name,
                'file_size' => $backup->file_size,
                'password_protected' => true,
                'backup_created_at' => $backup->backup_created_at,
            ]);
        } catch (Throwable $e) {
            logStore('DatabaseBackupService', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong during backup preparation'), [], 500, $e->getMessage());
        }
    }

    public function getBackupStatistics(): array
    {
        try {
            $backups = $this->databaseBackupRepository->getRecentBackups(10);
            $totalSize = $backups->sum(function ($backup) {
                return (int) ($backup->file_size ?? 0);
            });

            return $this->sendResponse(true, __('Backup statistics retrieved successfully'), [
                'total_backups' => DatabaseBackup::count(),
                'total_size' => $this->formatFileSize($totalSize),
                'recent_backups' => $backups,
                'latest_backup' => $backups->first(),
            ]);
        } catch (Throwable $e) {
            logStore('DatabaseBackupService', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong'), [], 500, $e->getMessage());
        }
    }

    protected function formatFileSize(int $size): string
    {
        if ($size >= 1073741824) {
            return number_format($size / 1073741824, 2) . ' GB';
        } elseif ($size >= 1048576) {
            return number_format($size / 1048576, 2) . ' MB';
        } elseif ($size >= 1024) {
            return number_format($size / 1024, 2) . ' KB';
        }
        return $size . ' bytes';
    }
}
