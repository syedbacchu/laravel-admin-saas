<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\TenantDatabase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Throwable;

class FreshMigrateTenantsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:migrate:fresh
                            {--tenant_id= : Run for a specific tenant ID}
                            {--company_username= : Run for a specific company username}
                            {--drop-views : Drop all views as part of the operation}
                            {--drop-types : Drop all tables and types (Postgres only)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drop all tenant tables and re-run tenant database migrations for one or all tenant databases';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $query = Tenant::query()->with('database')->orderBy('id');

        if ($this->option('tenant_id')) {
            $query->where('id', (int) $this->option('tenant_id'));
        }

        if ($this->option('company_username')) {
            $query->where('company_username', (string) $this->option('company_username'));
        }

        $tenants = $query->get();
        if ($tenants->isEmpty()) {
            $this->warn('No tenants found for migration.');
            return self::SUCCESS;
        }

        $success = 0;
        $failed = 0;

        foreach ($tenants as $tenant) {
            if (!$tenant->database) {
                $failed++;
                $this->error("Skipped tenant {$tenant->id} ({$tenant->company_username}): database config missing");
                continue;
            }

            try {
                $this->configureTenantConnection($tenant->database);

                $exitCode = Artisan::call('migrate:fresh', [
                    '--database' => 'tenant',
                    '--path' => 'database/migrations/tenant',
                    '--force' => true,
                    '--drop-views' => (bool) $this->option('drop-views'),
                    '--drop-types' => (bool) $this->option('drop-types'),
                ]);

                if ($exitCode !== 0) {
                    $failed++;
                    $this->error("Failed tenant {$tenant->id} ({$tenant->company_username})");
                    $this->line(trim(Artisan::output()));
                    continue;
                }

                $success++;
                $this->info("Fresh migrated tenant {$tenant->id} ({$tenant->company_username})");
            } catch (Throwable $e) {
                $failed++;
                logStore('FreshMigrateTenantsCommand', $e->getMessage());
                $this->error("Failed tenant {$tenant->id} ({$tenant->company_username}): {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->table(['Result', 'Count'], [
            ['Success', $success],
            ['Failed', $failed],
        ]);

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    protected function configureTenantConnection(TenantDatabase $tenantDatabase): void
    {
        $password = Crypt::decryptString((string) $tenantDatabase->db_password_encrypted);

        config([
            'database.connections.tenant.host' => (string) $tenantDatabase->db_host,
            'database.connections.tenant.port' => (int) $tenantDatabase->db_port,
            'database.connections.tenant.database' => (string) $tenantDatabase->db_name,
            'database.connections.tenant.username' => (string) $tenantDatabase->db_username,
            'database.connections.tenant.password' => $password,
            'database.connections.tenant.charset' => (string) ($tenantDatabase->db_charset ?: config('tenancy.database_charset', 'utf8mb4')),
            'database.connections.tenant.collation' => (string) ($tenantDatabase->db_collation ?: config('tenancy.database_collation', 'utf8mb4_unicode_ci')),
        ]);

        DB::purge('tenant');
        DB::reconnect('tenant');
    }
}
