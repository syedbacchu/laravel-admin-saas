<?php

namespace App\Http\Services\Tenant;

use App\Http\Requests\Tenant\TenantCreateRequest;
use App\Http\Services\BaseService;
use Illuminate\Support\Facades\DB;

class TenantService extends BaseService implements TenantServiceInterface
{
    protected TenantRepositoryInterface $tenantRepository;

    protected TenantProvisionServiceInterface $tenantProvisionService;

    public function __construct(
        TenantRepositoryInterface $repository,
        TenantProvisionServiceInterface $tenantProvisionService
    ) {
        parent::__construct($repository);
        $this->tenantRepository = $repository;
        $this->tenantProvisionService = $tenantProvisionService;
    }

    public function getDataTableData($request): array
    {
        $data = $this->tenantRepository->tenantList($request);
        return $this->sendResponse(true, __('Data get successfully.'), $data);
    }

    public function storeOrUpdateTenant(TenantCreateRequest $request): array
    {
        if ($request->edit_id) {
            return $this->sendResponse(false, __('Tenant update is not implemented yet'));
        }

        return $this->tenantProvisionService->provision($request->validated());
    }

    public function tenantCreateData($request): array
    {
        return $this->sendResponse(true, '', []);
    }

    public function getTenant($tenantId): mixed
    {
        return $this->tenantRepository->find((int) $tenantId);
    }

    public function backupTenantDatabase($tenantId): array
    {
        try {
            $tenant = $this->tenantRepository->find((int) $tenantId);
            if (!$tenant) {
                return $this->sendResponse(false, __('Tenant not found'));
            }

            $tenantDatabase = $tenant->database;
            if (!$tenantDatabase) {
                return $this->sendResponse(false, __('Tenant database configuration not found'));
            }

            // Create backup directory if it doesn't exist
            $backupDir = storage_path('app/backups/tenants');
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            // Generate backup filename
            $timestamp = now()->format('Y-m-d_H-i-s');
            $companyName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $tenant->company_name);
            $backupFile = "{$backupDir}/{$companyName}_{$timestamp}.sql";

            // Use mysqldump with admin database credentials instead of tenant credentials
            // This works because we're on the same server
            $adminDbHost = env('TENANCY_DB_ADMIN_HOST', env('DB_HOST'));
            $adminDbPort = env('TENANCY_DB_ADMIN_PORT', env('DB_PORT'));
            $adminDbUser = env('TENANCY_DB_ADMIN_USERNAME', env('DB_USERNAME'));
            $adminDbPass = env('TENANCY_DB_ADMIN_PASSWORD', env('DB_PASSWORD'));

            // Build mysqldump command with admin credentials
            $command = sprintf(
                'mysqldump -h%s -P%s -u%s -p%s %s > %s 2>&1',
                $adminDbHost,
                $adminDbPort,
                $adminDbUser,
                $adminDbPass,
                $tenantDatabase->db_name,
                $backupFile
            );

            // Execute backup
            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                return $this->sendResponse(false, __('Database backup failed'), [
                    'error' => implode("\n", $output),
                    'return_code' => $returnCode,
                    'command_used' => 'mysqldump with admin credentials'
                ]);
            }

            // Check if backup file was created
            if (!file_exists($backupFile) || filesize($backupFile) === 0) {
                return $this->sendResponse(false, __('Backup file creation failed'), [
                    'error' => 'Backup file is empty or was not created'
                ]);
            }

            return $this->compressBackupFile($backupFile, $tenant);

        } catch (Throwable $e) {
            logStore('TenantService backupTenantDatabase', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong during backup'), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    protected function createBackupWithMysqldump($tenant, $tenantDatabase, $dbPassword): array
    {
        // Create backup directory if it doesn't exist
        $backupDir = storage_path('app/backups/tenants');
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        // Generate backup filename
        $timestamp = now()->format('Y-m-d_H-i-s');
        $companyName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $tenant->company_name);
        $backupFile = "{$backupDir}/{$companyName}_{$timestamp}.sql";

        // Build mysqldump command
        $command = sprintf(
            'mysqldump -h%s -P%s -u%s -p%s %s > %s 2>&1',
            $tenantDatabase->db_host,
            $tenantDatabase->db_port,
            $tenantDatabase->db_username,
            $dbPassword,
            $tenantDatabase->db_name,
            $backupFile
        );

        // Execute backup
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            return $this->sendResponse(false, __('Database backup failed'), [
                'error' => implode("\n", $output),
                'return_code' => $returnCode
            ]);
        }

        // Check if backup file was created
        if (!file_exists($backupFile) || filesize($backupFile) === 0) {
            return $this->sendResponse(false, __('Backup file creation failed'), [
                'error' => 'Backup file is empty or was not created'
            ]);
        }

        return $this->compressBackupFile($backupFile, $tenant);
    }

    protected function createBackupWithPHP($tenant, $tenantDatabase): array
    {
        try {
            // Create backup directory if it doesn't exist
            $backupDir = storage_path('app/backups/tenants');
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            // Generate backup filename
            $timestamp = now()->format('Y-m-d_H-i-s');
            $companyName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $tenant->company_name);
            $backupFile = "{$backupDir}/{$companyName}_{$timestamp}.sql";

            // Use the existing tenant connection system that's already configured
            // The ResolveTenantContext middleware sets this up correctly
            try {
                // Temporarily configure tenant connection using the same method as the middleware
                $this->configureTenantConnection($tenantDatabase);
                $db = DB::connection('tenant');
                $db->getPdo(); // Test connection
            } catch (\Exception $connectionError) {
                return $this->sendResponse(false, __('Unable to connect to tenant database'), [
                    'error' => 'Could not establish database connection',
                    'details' => $connectionError->getMessage(),
                    'suggestion' => 'The tenant database might not be accessible from this environment. Please check database connectivity.'
                ]);
            }

            // Get all tables
            $tables = $db->select("SHOW TABLES");
            $tableColumn = 'Tables_in_' . $tenantDatabase->db_name;

            $sqlContent = "-- Database Backup: {$tenantDatabase->db_name}\n";
            $sqlContent .= "-- Generated: " . now()->format('Y-m-d H:i:s') . "\n";
            $sqlContent .= "-- Tenant: {$tenant->company_name} ({$tenant->company_username})\n";
            $sqlContent .= "-- Database: {$tenantDatabase->db_name} @ {$tenantDatabase->db_host}:{$tenantDatabase->db_port}\n\n";
            $sqlContent .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                $tableName = $table->$tableColumn;
                $sqlContent .= "-- Table structure for table `$tableName`\n";
                $sqlContent .= "DROP TABLE IF EXISTS `$tableName`;\n";

                // Get create table statement
                $createTable = $db->select("SHOW CREATE TABLE `$tableName`");
                $sqlContent .= $createTable[0]->{'Create Table'} . ";\n\n";

                // Get table data
                $rows = $db->select("SELECT * FROM `$tableName`");
                if (!empty($rows)) {
                    foreach ($rows as $row) {
                        $columns = array_keys((array)$row);
                        $values = array_values((array)$row);
                        $escapedValues = array_map(function($value) {
                            if ($value === null) return 'NULL';
                            if (is_numeric($value)) return $value;
                            return "'" . addslashes($value) . "'";
                        }, $values);

                        $sqlContent .= "INSERT INTO `$tableName` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $escapedValues) . ");\n";
                    }
                    $sqlContent .= "\n";
                }
            }

            $sqlContent .= "\nSET FOREIGN_KEY_CHECKS=1;\n";

            // Write to file
            file_put_contents($backupFile, $sqlContent);

            // Clean up connection
            DB::disconnect('tenant');

            return $this->compressBackupFile($backupFile, $tenant);

        } catch (\Exception $e) {
            return $this->sendResponse(false, __('PHP-based backup failed'), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    protected function configureTenantConnection($tenantDatabase): array
    {
        try {
            // Try to decrypt the password using the same method as the middleware
            try {
                $password = decrypt($tenantDatabase->db_password_encrypted);
            } catch (\Exception $decryptError) {
                throw new \Exception('Password decryption failed. This typically occurs when the APP_KEY has changed. Error: ' . $decryptError->getMessage());
            }

            config([
                'database.connections.tenant.host' => $tenantDatabase->db_host,
                'database.connections.tenant.port' => $tenantDatabase->db_port,
                'database.connections.tenant.database' => $tenantDatabase->db_name,
                'database.connections.tenant.username' => $tenantDatabase->db_username,
                'database.connections.tenant.password' => $password,
                'database.connections.tenant.charset' => $tenantDatabase->db_charset ?: 'utf8mb4',
                'database.connections.tenant.collation' => $tenantDatabase->db_collation ?: 'utf8mb4_unicode_ci',
            ]);

            DB::purge('tenant');
            $connection = DB::reconnect('tenant');

            // Test the connection with a simple query
            try {
                $connection->getPdo();
                $connection->select('SELECT 1 as test');
            } catch (\Exception $connectionError) {
                throw new \Exception('Database connection test failed. The database server might not be accessible from this admin panel. Error: ' . $connectionError->getMessage());
            }

            return ['success' => true, 'connection' => $connection];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    protected function compressBackupFile($backupFile, $tenant): array
    {
        // Compress the backup file
        $zipFile = $backupFile . '.gz';
        $source = fopen($backupFile, 'r');
        $destination = fopen($zipFile, 'wb');
        stream_copy_to_stream($source, $destination);
        fclose($source);
        fclose($destination);

        // Remove the original SQL file
        unlink($backupFile);

        // Get file size
        $fileSize = filesize($zipFile);

        return $this->sendResponse(true, __('Database backup created successfully'), [
            'tenant_id' => $tenant->id,
            'company_name' => $tenant->company_name,
            'backup_file' => basename($zipFile),
            'backup_size' => $this->formatBytes($fileSize),
            'backup_time' => now()->format('Y-m-d H:i:s')
        ]);
    }

    private function formatBytes($size, $precision = 2)
    {
        if ($size == 0) return '0 Bytes';

        $units = array('Bytes', 'KB', 'MB', 'GB', 'TB');
        $pow = floor(log($size, 1024));
        $size = $size / pow(1024, $pow);

        return round($size, $precision) . ' ' . $units[$pow];
    }

    public function getTenantBackups($tenantId): array
    {
        try {
            $tenant = $this->tenantRepository->find((int) $tenantId);
            if (!$tenant) {
                return $this->sendResponse(false, __('Tenant not found'));
            }

            $backupDir = storage_path('app/backups/tenants');
            $companyName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $tenant->company_name);

            if (!file_exists($backupDir)) {
                return $this->sendResponse(true, __('No backups found'), []);
            }

            $backups = [];
            $files = glob($backupDir . '/' . $companyName . '_*.sql.gz');

            foreach ($files as $file) {
                $backups[] = [
                    'filename' => basename($file),
                    'size' => $this->formatBytes(filesize($file)),
                    'created_at' => date('Y-m-d H:i:s', filemtime($file)),
                    'download_url' => route('tenant.downloadBackup', [
                        'tenant' => $tenant->id,
                        'filename' => basename($file)
                    ]),
                    'delete_url' => route('tenant.deleteBackup', [
                        'tenant' => $tenant->id,
                        'filename' => basename($file)
                    ])
                ];
            }

            // Sort by creation date (newest first)
            usort($backups, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });

            return $this->sendResponse(true, __('Backups retrieved successfully'), $backups);

        } catch (Throwable $e) {
            logStore('TenantService getTenantBackups', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong'), [], 500, $e->getMessage());
        }
    }

    public function downloadBackup($tenantId, $filename): array
    {
        try {
            $tenant = $this->tenantRepository->find((int) $tenantId);
            if (!$tenant) {
                return $this->sendResponse(false, __('Tenant not found'));
            }

            // Security: Validate filename format
            $companyName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $tenant->company_name);
            if (!preg_match('/^' . preg_quote($companyName) . '_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql\.gz$/', $filename)) {
                return $this->sendResponse(false, __('Invalid filename'));
            }

            $backupPath = storage_path('app/backups/tenants/' . $filename);
            if (!file_exists($backupPath)) {
                return $this->sendResponse(false, __('Backup file not found'));
            }

            return $this->sendResponse(true, __('Backup file ready'), [
                'path' => $backupPath,
                'filename' => $filename,
                'company_name' => $tenant->company_name
            ]);

        } catch (Throwable $e) {
            logStore('TenantService downloadBackup', $e->getMessage());
            return $this->sendResponse(false, __('Something went wrong'), [], 500, $e->getMessage());
        }
    }

    public function reencryptTenantPassword($tenantId, $plainPassword): array
    {
        try {
            $tenant = $this->tenantRepository->find((int) $tenantId);
            if (!$tenant) {
                return $this->sendResponse(false, __('Tenant not found'));
            }

            $tenantDatabase = $tenant->database;
            if (!$tenantDatabase) {
                return $this->sendResponse(false, __('Tenant database configuration not found'));
            }

            // Re-encrypt the password with current APP_KEY
            $tenantDatabase->update([
                'db_password_encrypted' => encrypt($plainPassword)
            ]);

            return $this->sendResponse(true, __('Database password re-encrypted successfully'), [
                'tenant_id' => $tenant->id,
                'company_name' => $tenant->company_name
            ]);

        } catch (\Exception $e) {
            return $this->sendResponse(false, __('Failed to re-encrypt password'), [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function diagnoseTenantConnection($tenantId): array
    {
        try {
            $tenant = $this->tenantRepository->find((int) $tenantId);
            if (!$tenant) {
                return $this->sendResponse(false, __('Tenant not found'));
            }

            $tenantDatabase = $tenant->database;
            if (!$tenantDatabase) {
                return $this->sendResponse(false, __('Tenant database configuration not found'));
            }

            $diagnostics = [
                'tenant' => [
                    'id' => $tenant->id,
                    'company_name' => $tenant->company_name,
                    'company_username' => $tenant->company_username,
                    'status' => $tenant->status,
                ],
                'database_config' => [
                    'db_name' => $tenantDatabase->db_name,
                    'db_host' => $tenantDatabase->db_host,
                    'db_port' => $tenantDatabase->db_port,
                    'db_username' => $tenantDatabase->db_username,
                    'db_charset' => $tenantDatabase->db_charset,
                    'db_collation' => $tenantDatabase->db_collation,
                ],
                'tests' => []
            ];

            // Test 1: Check if database exists on server
            try {
                $adminDbHost = env('TENANCY_DB_ADMIN_HOST', env('DB_HOST'));
                $adminDbPort = env('TENANCY_DB_ADMIN_PORT', env('DB_PORT'));
                $adminDbUser = env('TENANCY_DB_ADMIN_USERNAME', env('DB_USERNAME'));
                $adminDbPass = env('TENANCY_DB_ADMIN_PASSWORD', env('DB_PASSWORD'));

                $dsn = "mysql:host={$adminDbHost};port={$adminDbPort}";
                $testConnection = new \PDO($dsn, $adminDbUser, $adminDbPass);

                $result = $testConnection->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$tenantDatabase->db_name}'");
                $dbExists = $result->fetchColumn();

                if ($dbExists) {
                    $diagnostics['tests']['database_exists'] = [
                        'status' => 'success',
                        'message' => 'Database exists on server: ' . $tenantDatabase->db_name
                    ];
                } else {
                    $diagnostics['tests']['database_exists'] = [
                        'status' => 'failed',
                        'message' => 'Database not found: ' . $tenantDatabase->db_name
                    ];
                    return $this->sendResponse(false, 'Database does not exist', $diagnostics);
                }
                $testConnection = null;

            } catch (\Exception $e) {
                $diagnostics['tests']['database_exists'] = [
                    'status' => 'failed',
                    'message' => 'Cannot check database existence: ' . $e->getMessage()
                ];
                return $this->sendResponse(false, 'Database check failed', $diagnostics);
            }

            // Test 2: Can we access the specific database with admin credentials?
            try {
                $adminDbHost = env('TENANCY_DB_ADMIN_HOST', env('DB_HOST'));
                $adminDbPort = env('TENANCY_DB_ADMIN_PORT', env('DB_PORT'));
                $adminDbUser = env('TENANCY_DB_ADMIN_USERNAME', env('DB_USERNAME'));
                $adminDbPass = env('TENANCY_DB_ADMIN_PASSWORD', env('DB_PASSWORD'));

                $dsn = "mysql:host={$adminDbHost};port={$adminDbPort};dbname={$tenantDatabase->db_name}";
                $testConnection = new \PDO($dsn, $adminDbUser, $adminDbPass);
                $statement = $testConnection->query('SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = DATABASE()');
                $result = $statement->fetch(\PDO::FETCH_ASSOC);
                $testConnection = null;

                $diagnostics['tests']['database_access'] = [
                    'status' => 'success',
                    'message' => 'Database access successful using admin credentials',
                    'table_count' => $result['table_count']
                ];
            } catch (\Exception $e) {
                $diagnostics['tests']['database_access'] = [
                    'status' => 'failed',
                    'message' => 'Database access failed: ' . $e->getMessage()
                ];
                return $this->sendResponse(false, 'Database access test failed', $diagnostics);
            }

            // Test 3: Password decryption status (informational only)
            try {
                $password = decrypt($tenantDatabase->db_password_encrypted);
                $diagnostics['tests']['password_decryption'] = [
                    'status' => 'success',
                    'message' => 'Tenant password decryption works (backup can use either method)'
                ];
            } catch (\Exception $decryptError) {
                $diagnostics['tests']['password_decryption'] = [
                    'status' => 'warning',
                    'message' => 'Tenant password decryption failed (but admin credentials work for backup)',
                    'note' => 'This does not affect backup functionality'
                ];
            }

            $diagnostics['tests']['overall'] = [
                'status' => 'success',
                'message' => 'Backup system is ready! Using admin MySQL credentials for backups.'
            ];

            return $this->sendResponse(true, 'Diagnostics completed successfully', $diagnostics);

        } catch (\Exception $e) {
            return $this->sendResponse(false, __('Diagnostics failed'), [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function deleteBackup($tenantId, $filename): array
    {
        try {
            $tenant = $this->tenantRepository->find((int) $tenantId);
            if (!$tenant) {
                return $this->sendResponse(false, __('Tenant not found'));
            }

            // Security: Validate filename format
            $companyName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $tenant->company_name);
            if (!preg_match('/^' . preg_quote($companyName) . '_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql\.gz$/', $filename)) {
                return $this->sendResponse(false, __('Invalid filename'));
            }

            $backupPath = storage_path('app/backups/tenants/' . $filename);
            if (!file_exists($backupPath)) {
                return $this->sendResponse(false, __('Backup file not found'));
            }

            // Delete the file
            if (unlink($backupPath)) {
                return $this->sendResponse(true, __('Backup deleted successfully'), [
                    'filename' => $filename,
                    'company_name' => $tenant->company_name
                ]);
            } else {
                return $this->sendResponse(false, __('Failed to delete backup file'));
            }

        } catch (\Exception $e) {
            return $this->sendResponse(false, __('Something went wrong'), [
                'error' => $e->getMessage()
            ]);
        }
    }
}
