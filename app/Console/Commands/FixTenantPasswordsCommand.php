<?php

namespace App\Console\Commands;

use App\Models\TenantDatabase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;

class FixTenantPasswordsCommand extends Command
{
    protected $signature = 'tenants:fix-passwords';
    protected $description = 'Re-encrypt tenant database passwords with current APP_KEY';

    public function handle()
    {
        $this->info('Starting tenant password re-encryption...');

        try {
            $tenantDatabases = TenantDatabase::all();

            if ($tenantDatabases->isEmpty()) {
                $this->warn('No tenant databases found.');
                return 0;
            }

            $this->info("Found {$tenantDatabases->count()} tenant database(s) to process.");

            $successCount = 0;
            $failedCount = 0;

            foreach ($tenantDatabases as $tenantDatabase) {
                try {
                    // Test if current password can be decrypted
                    $password = decrypt($tenantDatabase->db_password_encrypted);

                    $this->line("<fg=green>✓</fg=green> Tenant {$tenantDatabase->tenant_id}: Password already valid");
                    $successCount++;

                } catch (\Exception $decryptError) {
                    $this->warn("Tenant {$tenantDatabase->tenant_id}: Password needs manual update");

                    // Ask for the plain password
                    $plainPassword = $this->secret("Enter the database password for tenant {$tenantDatabase->tenant_id} ({$tenantDatabase->db_name})");

                    if (empty($plainPassword)) {
                        $this->error("Skipping tenant {$tenantDatabase->tenant_id} - no password provided");
                        $failedCount++;
                        continue;
                    }

                    try {
                        // Re-encrypt with current APP_KEY
                        $tenantDatabase->update([
                            'db_password_encrypted' => Crypt::encryptString($plainPassword)
                        ]);

                        $this->line("<fg=green>✓</fg=green> Tenant {$tenantDatabase->tenant_id}: Password re-encrypted successfully");
                        $successCount++;

                    } catch (\Exception $encryptError) {
                        $this->error("Tenant {$tenantDatabase->tenant_id}: Failed to encrypt password");
                        $failedCount++;
                    }
                }
            }

            $this->info("\n--- Summary ---");
            $this->info("Success: {$successCount}");
            $this->info("Failed: {$failedCount}");

            return 0;

        } catch (\Exception $e) {
            $this->error('Error during password re-encryption: ' . $e->getMessage());
            return 1;
        }
    }
}