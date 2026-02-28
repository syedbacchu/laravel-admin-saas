<?php

namespace App\Http\Services\Tenant;

use App\Enums\UserRole;
use App\Models\Tenant;
use App\Models\TenantDatabase;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class TenantProvisionService implements TenantProvisionServiceInterface
{
    public function provision(array $payload): array
    {
        $dbName = null;
        $dbUsername = null;
        $databaseCreated = false;
        $owner = null;

        try {
            $dbName = $this->generateDatabaseName($payload['company_username']);
            $dbUsername = $this->generateDatabaseUserName($payload['company_username']);
            $dbPassword = Str::password(24, true, true, false, false);
            $tenantToken = Str::random(64);

            DB::beginTransaction();

            $owner = $this->createOwnerUser($payload);

            $tenant = Tenant::create([
                'uuid' => (string) Str::uuid(),
                'company_name' => $payload['company_name'],
                'company_username' => $payload['company_username'],
                'owner_user_id' => $owner->id,
                'status' => 'provisioning',
            ]);

            $this->createPhysicalDatabaseAndUser($dbName, $dbUsername, $dbPassword);
            $databaseCreated = true;

            $this->configureTenantConnection($dbName, $dbUsername, $dbPassword);
            $this->runTenantMigrations();

            $configPath = $this->writeTenantConfig($tenant, [
                'db_name' => $dbName,
                'db_host' => (string) config('tenancy.database_host'),
                'db_port' => (int) config('tenancy.database_port', 3306),
                'db_username' => $dbUsername,
                'db_password_encrypted' => Crypt::encryptString($dbPassword),
                'tenant_token_encrypted' => Crypt::encryptString($tenantToken),
            ]);

            TenantDatabase::create([
                'tenant_id' => $tenant->id,
                'db_name' => $dbName,
                'db_host' => (string) config('tenancy.database_host'),
                'db_port' => (int) config('tenancy.database_port', 3306),
                'db_username' => $dbUsername,
                'db_password_encrypted' => Crypt::encryptString($dbPassword),
                'db_charset' => (string) config('tenancy.database_charset', 'utf8mb4'),
                'db_collation' => (string) config('tenancy.database_collation', 'utf8mb4_unicode_ci'),
                'tenant_token_encrypted' => Crypt::encryptString($tenantToken),
                'config_path' => $configPath,
            ]);

            $tenant->update([
                'status' => 'active',
                'activated_at' => now(),
            ]);

            DB::commit();

            return sendResponse(true, __('Tenant created successfully'), [
                'tenant_id' => $tenant->id,
                'tenant_uuid' => $tenant->uuid,
                'company_name' => $tenant->company_name,
                'company_username' => $tenant->company_username,
                'owner_user_id' => $owner->id,
                'owner_username' => $owner->username,
                'db_name' => $dbName,
                'config_path' => $configPath,
            ]);
        } catch (Throwable $e) {
            DB::rollBack();

            if ($databaseCreated && $dbName && $dbUsername) {
                $this->dropPhysicalDatabaseAndUser($dbName, $dbUsername);
            }

            logStore('Tenant Provisioning', $e->getMessage());

            return sendResponse(false, __('Tenant provisioning failed'), [], 500, $e->getMessage());
        }
    }

    protected function createOwnerUser(array $payload): User
    {
        return User::create([
            'name' => $payload['owner_name'],
            'username' => $payload['company_username'],
            'email' => Arr::get($payload, 'owner_email'),
            'phone' => Arr::get($payload, 'owner_phone'),
            'password' => Hash::make($payload['owner_password']),
            'status' => 1,
            'role_module' => enum(UserRole::USER_ROLE),
            'is_email_verified' => !empty($payload['owner_email']) ? 1 : 0,
            'is_phone_verified' => !empty($payload['owner_phone']) ? 1 : 0,
            'email_verified_at' => !empty($payload['owner_email']) ? now() : null,
        ]);
    }

    protected function createPhysicalDatabaseAndUser(string $dbName, string $dbUsername, string $dbPassword): void
    {
        $connection = $this->tenantAdminConnection();
        $charset = (string) config('tenancy.database_charset', 'utf8mb4');
        $collation = (string) config('tenancy.database_collation', 'utf8mb4_unicode_ci');
        $escapedPassword = $this->escapeSqlString($dbPassword);

        $connection->statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET {$charset} COLLATE {$collation}");
        $connection->statement("CREATE USER IF NOT EXISTS '{$dbUsername}'@'%' IDENTIFIED BY '{$escapedPassword}'");
        $connection->statement("ALTER USER '{$dbUsername}'@'%' IDENTIFIED BY '{$escapedPassword}'");
        $connection->statement("GRANT ALL PRIVILEGES ON `{$dbName}`.* TO '{$dbUsername}'@'%'");
        $connection->statement('FLUSH PRIVILEGES');
    }

    protected function dropPhysicalDatabaseAndUser(string $dbName, string $dbUsername): void
    {
        try {
            $connection = $this->tenantAdminConnection();
            $connection->statement("DROP DATABASE IF EXISTS `{$dbName}`");
            $connection->statement("DROP USER IF EXISTS '{$dbUsername}'@'%'");
            $connection->statement('FLUSH PRIVILEGES');
        } catch (Throwable $e) {
            logStore('Tenant Provisioning Rollback', $e->getMessage());
        }
    }

    protected function tenantAdminConnection()
    {
        config([
            'database.connections.tenant_admin.host' => config('tenancy.database_admin_host'),
            'database.connections.tenant_admin.port' => config('tenancy.database_admin_port'),
            'database.connections.tenant_admin.database' => config('tenancy.database_admin_database'),
            'database.connections.tenant_admin.username' => config('tenancy.database_admin_username'),
            'database.connections.tenant_admin.password' => config('tenancy.database_admin_password'),
        ]);

        DB::purge('tenant_admin');

        return DB::connection('tenant_admin');
    }

    protected function configureTenantConnection(string $dbName, string $dbUsername, string $dbPassword): void
    {
        config([
            'database.connections.tenant.host' => config('tenancy.database_host'),
            'database.connections.tenant.port' => config('tenancy.database_port'),
            'database.connections.tenant.database' => $dbName,
            'database.connections.tenant.username' => $dbUsername,
            'database.connections.tenant.password' => $dbPassword,
            'database.connections.tenant.charset' => config('tenancy.database_charset', 'utf8mb4'),
            'database.connections.tenant.collation' => config('tenancy.database_collation', 'utf8mb4_unicode_ci'),
        ]);

        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    protected function runTenantMigrations(): void
    {
        $tenantMigrationPath = database_path('migrations/tenant');
        if (!is_dir($tenantMigrationPath)) {
            return;
        }

        $exitCode = Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            throw new \RuntimeException('Tenant migration failed: ' . trim(Artisan::output()));
        }
    }

    protected function writeTenantConfig(Tenant $tenant, array $data): string
    {
        $directory = trim((string) config('tenancy.config_directory', 'tenants'), '/');
        $path = $directory . '/' . $tenant->company_username . '.json';

        $payload = [
            'tenant_id' => $tenant->id,
            'tenant_uuid' => $tenant->uuid,
            'company_name' => $tenant->company_name,
            'company_username' => $tenant->company_username,
            'database' => [
                'name' => $data['db_name'],
                'host' => $data['db_host'],
                'port' => $data['db_port'],
                'username' => $data['db_username'],
                'password_encrypted' => $data['db_password_encrypted'],
            ],
            'tenant_token_encrypted' => $data['tenant_token_encrypted'],
            'generated_at' => now()->toISOString(),
        ];

        Storage::disk((string) config('tenancy.config_disk', 'local'))->put(
            $path,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        return $path;
    }

    protected function generateDatabaseName(string $companyUsername): string
    {
        $prefix = (string) config('tenancy.database_prefix', 'sd_tenant_');
        $suffix = strtolower(Str::random(6));
        $normalized = $this->normalizeIdentifier($companyUsername);
        $maxLength = 63;
        $availableBaseLength = max(1, $maxLength - strlen($prefix) - 1 - strlen($suffix));
        $trimmed = substr($normalized, 0, $availableBaseLength);

        return $prefix . $trimmed . '_' . $suffix;
    }

    protected function generateDatabaseUserName(string $companyUsername): string
    {
        $prefix = 'tu_';
        $suffix = strtolower(Str::random(6));
        $normalized = $this->normalizeIdentifier($companyUsername);
        $maxLength = 32;
        $availableBaseLength = max(1, $maxLength - strlen($prefix) - 1 - strlen($suffix));
        $trimmed = substr($normalized, 0, $availableBaseLength);

        return $prefix . $trimmed . '_' . $suffix;
    }

    protected function normalizeIdentifier(string $value): string
    {
        $normalized = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $value) ?: 'tenant');
        $normalized = trim($normalized, '_');

        return $normalized === '' ? 'tenant' : $normalized;
    }

    protected function escapeSqlString(string $value): string
    {
        return str_replace(
            ["\\", "'"],
            ["\\\\", "\\'"],
            $value
        );
    }
}
