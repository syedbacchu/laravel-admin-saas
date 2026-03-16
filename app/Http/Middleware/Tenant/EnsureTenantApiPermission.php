<?php

namespace App\Http\Middleware\Tenant;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantApiPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => __('Unauthenticated'),
                'data' => [],
                'status' => 401,
                'error_message' => __('Unauthenticated'),
            ], 401);
        }

        $tenant = $request->attributes->get('tenant');
        $ownerUserId = (int) ($tenant?->owner_user_id ?? 0);

        if ($ownerUserId > 0 && (int) $user->id === $ownerUserId) {
            return $next($request);
        }

        if ($request->route()?->middleware() && in_array('tenant.api.permission.skip', $request->route()->middleware(), true)) {
            return $next($request);
        }

        $permission = (string) ($request->route()?->getName() ?? '');
        if ($permission === '') {
            return $next($request);
        }

        if (!$user->hasPermission($permission)) {
            return response()->json([
                'success' => false,
                'message' => __('Permission denied'),
                'data' => [],
                'status' => 403,
                'error_message' => __('Permission denied'),
            ], 403);
        }

        return $next($request);
    }
}

