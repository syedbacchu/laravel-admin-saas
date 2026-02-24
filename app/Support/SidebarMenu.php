<?php
namespace App\Support;

use App\Enums\UserRole;
use Illuminate\Support\Facades\Auth;

class SidebarMenu
{
    public static function get(): array
    {
        $user = Auth::user();

        if (!$user) {
            return [];
        }

        $roleId = $user->role_id ?? 'no_role';
        $sidebarConfigVersion = md5(json_encode(config('sidebar')));
        $cacheKey = 'sidebar_menu_role_' . $roleId . '_' . $sidebarConfigVersion;

        return cache()->remember($cacheKey, 3600, function () {
            $menus = config('sidebar');

            return collect($menus)
                ->map(function ($menu) {

                    if (!empty($menu['children'])) {
                        $menu['children'] = collect($menu['children'])
                            ->filter(fn ($child) => self::can($child))
                            ->values()
                            ->toArray();
                    }

                    return $menu;
                })
                ->filter(function ($menu) {

                    if (empty($menu['children'])) {
                        return isset($menu['route']) && self::can($menu);
                    }

                    return count($menu['children']) > 0;
                })
                ->values()
                ->toArray();
        });
    }

    protected static function can(array $item): bool
    {
        // No permission required
        if (empty($item['permission'])) {
            return true;
        }

        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // Super admin → allow all
        if ($user->role_module == enum(UserRole::SUPER_ADMIN_ROLE)) {
            return true;
        }

        // Your custom permission system
        return $user->hasPermission($item['permission']);
    }
}
