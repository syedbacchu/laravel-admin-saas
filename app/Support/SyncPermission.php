<?php


namespace App\Support;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SyncPermission
{

    public static function sync(Request $request): array
    {
        $routes = Route::getRoutes();

        foreach ($routes as $route) {

            $name = $route->getName();

            // Skip unnamed routes
            if (!$name || !str_contains($name, '.')) {
                continue;
            }

            // Optional: Only admin routes
            if (!str_starts_with($route->uri(), 'admin')) {
                continue;
            }

            if (collect($route->middleware())->contains('no.permission.sync')) {
                continue;
            }
            $guard = collect($route->middleware())->contains('api') ? 'api' : 'web';
            [$module] = explode('.', $name);

            if ($module == 'sanctum') {
                continue;
            }
            if ($module == 'ignition') {
                continue;
            }
            Permission::firstOrCreate(
                [
                    'slug'  => $name,
                    'guard' => $guard,
                ],
                [
                    'name'  => formatPermissionName($name),
                    'module' => $module,
                ]
            );
        }

        return sendResponse(true, __('Permissions synced successfully.'));
    }

    public static function roleCacheClear($role) {
        // Clear sidebar cache for this role
        cache()->forget('sidebar_menu_role_' . $role->id);
        logStore('role updated',$role->id);

        // Clear permissions cache for all users of this role
        $role->users->each(function ($user) {
            cache()->forget('user_permissions_' . $user->id);
            logStore('user cache updated',$user->id);
        });
    }
    public static function userCacheClear($user) {
        logStore('userCacheClear',$user->id);
        if ($user->wasChanged('role_id')) {
            $oldRoleId = $user->getOriginal('role_id') ?? 'no_role';
            $newRoleId = $user->role_id ?? 'no_role';

            cache()->forget('sidebar_menu_role_' . $oldRoleId);
            cache()->forget('sidebar_menu_role_' . $newRoleId);
            cache()->forget('user_permissions_' . $user->id);
        }

        // Always clear user's cached permissions
        cache()->forget('user_permissions_' . $user->id);
    }

}
