<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }
        if ($user->role_module == enum(UserRole::SUPER_ADMIN_ROLE)) {
            return $next($request);
        }
        if ($request->route()?->middleware()
            && in_array('skip.permission', $request->route()->middleware())
        ) {
            return $next($request);
        }
        $permission = $request->route()?->getName();

        if (!$permission) {
            return $next($request);
        }

        if (!$user->hasPermission($permission)) {
            abort(403, 'Permission denied');
        }

        return $next($request);
    }
}
