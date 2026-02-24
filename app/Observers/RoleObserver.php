<?php

namespace App\Observers;

use App\Models\Role;

class RoleObserver
{
    /**
     * Handle the Role "created" event.
     */
    public function created(Role $role): void
    {
        //
    }

    /**
     * Handle the Role "updated" event.
     */
    public function updated(Role $role): void
    {
        // Clear sidebar cache for this role
        cache()->forget('sidebar_menu_role_' . $role->id);
        logStore('role updated',$role->id);

        // Clear permissions cache for all users of this role
        $role->users->each(function ($user) {
            cache()->forget('user_permissions_' . $user->id);
            logStore('user cache updated',$user->id);
        });
    }

    /**
     * Handle the Role "deleted" event.
     */
    public function deleted(Role $role): void
    {
        cache()->forget('sidebar_menu_role_' . $role->id);

        $role->users->each(function ($user) {
            cache()->forget('user_permissions_' . $user->id);
        });
    }

    /**
     * Handle the Role "restored" event.
     */
    public function restored(Role $role): void
    {
        //
    }

    /**
     * Handle the Role "force deleted" event.
     */
    public function forceDeleted(Role $role): void
    {
        //
    }
}
