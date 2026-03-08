<?php

namespace App\Http\Middleware\Tenant;

use App\Enums\UserRole;
use App\Models\Tenant;
use App\Models\TenantDatabase;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ResolveTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $companyUsername = (string) ($request->route('company_username')
            ?? $request->header('X-Tenant-Username')
            ?? $request->input('company_username')
            ?? '');

        if ($companyUsername === '') {
            return response()->json([
                'success' => false,
                'message' => __('Company username is required'),
                'data' => [],
                'status' => 422,
                'error_message' => __('Company username is required'),
            ], 422);
        }

        $tenant = Tenant::query()
            ->with('database')
            ->where('company_username', $companyUsername)
            ->first();

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => __('Tenant not found'),
                'data' => [],
                'status' => 404,
                'error_message' => __('Tenant not found'),
            ], 404);
        }

        if (!$tenant->database) {
            return response()->json([
                'success' => false,
                'message' => __('Tenant database configuration not found'),
                'data' => [],
                'status' => 422,
                'error_message' => __('Tenant database configuration not found'),
            ], 422);
        }

        $bootstrapped = $this->bootstrapTenantConnection($tenant->database);
        if (($bootstrapped['success'] ?? false) !== true) {
            return response()->json($bootstrapped, (int) ($bootstrapped['status'] ?? 500));
        }

        $request->attributes->set('tenant', $tenant);
        $request->attributes->set('tenant_database', $tenant->database);

        $user = $request->user();
        if ($user) {
            $ownerUserId = (int) $tenant->owner_user_id;
            $isBelongsToTenant = (int) $user->id === $ownerUserId || (int) $user->parent_id === $ownerUserId;

            if (!$isBelongsToTenant || (int) $user->role_module !== enum(UserRole::USER_ROLE)) {
                return response()->json([
                    'success' => false,
                    'message' => __('Access denied for this tenant'),
                    'data' => [],
                    'status' => 403,
                    'error_message' => __('Access denied for this tenant'),
                ], 403);
            }
        }

        return $next($request);
    }

    protected function bootstrapTenantConnection(TenantDatabase $tenantDatabase): array
    {
        try {
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

            return [
                'success' => true,
            ];
        } catch (Throwable $e) {
            logStore('ResolveTenantContext bootstrapTenantConnection', $e->getMessage());

            return [
                'success' => false,
                'message' => __('Unable to connect tenant database'),
                'data' => [],
                'status' => 500,
                'error_message' => $e->getMessage(),
            ];
        }
    }
}
