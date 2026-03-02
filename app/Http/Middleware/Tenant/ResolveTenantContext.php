<?php

namespace App\Http\Middleware\Tenant;

use App\Enums\UserRole;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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

        $request->attributes->set('tenant', $tenant);

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
}

